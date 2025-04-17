<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
class AdminMpCustomerJobsController extends ModuleAdminController
{
    protected $jobAreaList = [];
    protected $jobNameList = [];

    public function __construct()
    {
        $pageType = Tools::getValue('pagetype', 'main');

        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName('mpcustomerjobs');
        $this->translator = Context::getContext()->getTranslator();
        $this->explicitSelect = true;

        $this->bootstrap = true;

        $this->jobAreaList = $this->getJobAreas();
        $this->jobNameList = $this->getJobNames();

        switch ($pageType) {
            case 'link':
                $this->getListLink();

                break;
            case 'area':
                $this->getListArea();

                break;
            case 'jobs':
                $this->getListJobs();

                break;
            case 'main':
            default:
                $this->getListMain();
        }

        parent::__construct();
    }

    public function setMedia($isNewTheme = false)
    {
        $jsPath = $this->module->getLocalPath() . 'views/js/';
        $this->context->controller->addJS("{$jsPath}Admin/manageURL.js");

        parent::setMedia($isNewTheme);
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['link'] = [
            'href' => $this->context->link->getAdminLink('AdminMpCustomerJobs', true, [], ['pagetype' => 'link']),
            'desc' => $this->module->l('Associa professioni'),
            'icon' => 'icon-link',
            'class' => 'link-jobs',
        ];
        $this->page_header_toolbar_btn['sector'] = [
            'href' => $this->context->link->getAdminLink('AdminMpCustomerJobs', true, [], ['pagetype' => 'area']),
            'desc' => $this->module->l('Nuovo settore'),
            'icon' => 'icon-plus-circle',
            'class' => 'new-sector',
        ];
        $this->page_header_toolbar_btn['job'] = [
            'href' => $this->context->link->getAdminLink('AdminMpCustomerJobs', true, [], ['pagetype' => 'jobs']),
            'desc' => $this->module->l('Nuova professione'),
            'icon' => 'icon-plus-circle',
            'class' => 'new-job',
        ];

        parent::initPageHeaderToolbar();
    }

    protected function getJobAreas()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_customer_job_area, name')
            ->from(ModelMpCustomerJobArea::$definition['table'] . '_lang')
            ->where('id_lang = ' . (int) $this->context->language->id)
            ->orderBy('name');

        $result = $db->executeS($sql);
        if ($result) {
            $list = [];
            foreach ($result as $row) {
                $list[$row['id_customer_job_area']] = Tools::strtoupper($row['name']);
            }

            return $list;
        }

        return [];
    }

    protected function getJobNames()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_customer_job_name, name')
            ->from(ModelMpCustomerJobName::$definition['table'] . '_lang')
            ->where('id_lang = ' . (int) $this->context->language->id)
            ->orderBy('name');

        $result = $db->executeS($sql);
        if ($result) {
            $list = [];
            foreach ($result as $row) {
                $list[$row['id_customer_job_name']] = Tools::strtoupper($row['name']);
            }

            return $list;
        }

        return [];
    }

    public function getJobAreaLabel($value)
    {
        if (!$value) {
            return '--';
        }

        return $this->jobAreaList[$value] ?? $value;
    }

    public function getJobNameLabel($value)
    {
        if (!$value) {
            return '--';
        }

        return $this->jobNameList[$value] ?? $value;
    }

    protected function getListMain()
    {
        $this->fields_list = [
            'id_customer' => [
                'title' => 'ID',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'firstname' => [
                'title' => 'Nome',
                'align' => 'left',
                'filter_key' => 'c!firstname',
            ],
            'lastname' => [
                'title' => 'Cognome',
                'align' => 'left',
                'filter_key' => 'c!lastname',
            ],
            'email' => [
                'title' => 'email',
                'align' => 'left',
                'filter_key' => 'c!email',
            ],
            'id_customer_job_area' => [
                'type' => 'select',
                'title' => 'Settore',
                'list' => $this->getJobAreas(),
                'filter_key' => 'cj!id_customer_job_area',
                'callback' => 'getJobAreaLabel',
            ],
            'id_customer_job_name' => [
                'type' => 'select',
                'title' => 'Professione',
                'list' => $this->getJobNames(),
                'filter_key' => 'cj!id_customer_job_name',
                'callback' => 'getJobNameLabel',
            ],
            'date_add' => [
                'title' => 'Data creazione',
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ],
            'date_upd' => [
                'title' => 'Ultima modifica',
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ],
        ];

        $this->table = 'customer';
        $this->identifier = 'id_customer';
        $this->className = 'ModelMpCustomerJob';
        $this->lang = false;
        $this->_select = 'c.firstname, c.lastname, c.email, cj.id_customer_job_area, cj.id_customer_job_name';
        $this->_join = ' LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON c.id_customer=a.id_customer';
        $this->_join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'customer_job cj ON cj.id_customer=a.id_customer';
    }

    protected function getListLink()
    {
    }

    protected function getListArea()
    {
        $this->fields_list = [
            'id_customer_job_area' => [
                'title' => 'ID',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'name' => [
                'title' => 'Nome',
                'align' => 'left',
                'filter_key' => 'b!name',
            ],
            'date_add' => [
                'title' => 'Data creazione',
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ],
            'date_upd' => [
                'title' => 'Ultima modifica',
                'type' => 'datetime',
                'filter_key' => 'a!date_upd',
            ],
        ];

        $this->className = 'ModelMpCustomerJobArea';
        $this->identifier = 'id_customer_job_area';
        $this->lang = true;
        $this->table = 'customer_job_area';
    }

    protected function getListJobs()
    {
        $this->fields_list = [
            'id_customer_job_name' => [
                'title' => 'ID',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'name' => [
                'title' => 'Nome',
                'align' => 'left',
                'filter_key' => 'b!name',
            ],
            'date_add' => [
                'title' => 'Data creazione',
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ],
            'date_upd' => [
                'title' => 'Ultima modifica',
                'type' => 'datetime',
                'filter_key' => 'a!date_upd',
            ],
        ];

        $this->className = 'ModelMpCustomerJobName';
        $this->identifier = 'id_customer_job_name';
        $this->lang = true;
        $this->table = 'customer_job_name';
    }
}
