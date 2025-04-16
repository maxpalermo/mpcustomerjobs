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
        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName('mpcustomerjobs');
        $this->translator = Context::getContext()->getTranslator();

        $this->bootstrap = true;
        $this->table = 'customer';
        $this->identifier = 'id_customer';
        $this->className = 'ModelMpCustomerJob';
        $this->lang = false;

        $this->fields_list = [
            'id_customer' => [
                'title' => 'ID',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'firstname' => [
                'title' => 'Nome',
                'align' => 'left',
            ],
            'lastname' => [
                'title' => 'Cognome',
                'align' => 'left',
            ],
            'email' => [
                'title' => 'email',
                'align' => 'left',
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
            ],
            'date_upd' => [
                'title' => 'Ultima modifica',
                'type' => 'datetime',
            ],
        ];

        $this->_select = 'c.firstname, c.lastname, c.email, cj.id_customer_job_area, cj.id_customer_job_name';
        $this->_join = ' LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON c.id_customer=a.id_customer';
        $this->_join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'customer_job cj ON cj.id_customer=a.id_customer';

        $this->jobAreaList = $this->getJobAreas();
        $this->jobNameList = $this->getJobNames();

        parent::__construct();
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['link'] = [
            'href' => $this->context->link->getAdminLink('AdminMpCustomerJobs', true, [], ['action' => 'linkJobs']),
            'desc' => $this->module->l('Associa professioni'),
            'icon' => 'icon-link',
            'class' => 'link-jobs',
        ];
        $this->page_header_toolbar_btn['sector'] = [
            'href' => $this->context->link->getAdminLink('AdminMpCustomerJobs', true, [], ['action' => 'newSector']),
            'desc' => $this->module->l('Nuovo settore'),
            'icon' => 'icon-plus-circle',
            'class' => 'new-sector',
        ];
        $this->page_header_toolbar_btn['job'] = [
            'href' => $this->context->link->getAdminLink('AdminMpCustomerJobs', true, [], ['action' => 'newJob']),
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
}
