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
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'mpcustomerjobs/vendor/autoload.php';
require_once _PS_MODULE_DIR_ . 'mpcustomerjobs/models/autoload.php';

use Doctrine\ORM\QueryBuilder;
use MpSoft\MpCustomerJobs\Install\InstallMenu;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Search\Filters\CustomerFilters;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MpCustomerJobs extends Module implements WidgetInterface
{
    protected $jobAreaList = [];
    protected $jobNameList = [];
    protected $jobLinkList = [];

    public function __construct()
    {
        $this->name = 'mpcustomerjobs';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Massimiliano Palermo';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        $this->jobAreaList = ModelMpCustomerJobArea::getList();
        $this->jobNameList = ModelMpCustomerJobName::getList();
        $this->jobLinkList = ModelMpCustomerJobLink::getList();

        parent::__construct();

        $this->displayName = $this->l('Professioni Clienti');
        $this->description = $this->l('Gestisce le professioni cliente');
    }

    /**
     * Renderizza il widget per il modulo
     *
     * @param string $hookName
     * @param array $configuration
     *
     * @return string
     */
    public function renderWidget($hookName, array $configuration = [])
    {
        if (!isset($this->smarty)) {
            $this->smarty = \Context::getContext()->smarty;
        }
        $variables = $this->getWidgetVariables($hookName, $configuration);

        // Pass the AJAX URL to JS
        $ajaxUrl = $this->context->link->getModuleLink($this->name, 'AjaxJobNames', [], Configuration::get('PS_SSL_ENABLED') ?? 0);
        $script = <<<JS
            <script type="text/javascript">
                const mpCustomerJobAjaxUrl = '{$ajaxUrl}';
            </script>
        JS;

        // visualizzo lo script solo se siamo nella pagina di registrazione cliente
        if ($hookName != 'displayCustomerAccountForm') {
            $script = '';
        }

        return $script;
    }

    /**
     * Restituisce le variabili da passare al template del widget
     *
     * @param string $hookName
     * @param array $configuration
     *
     * @return array
     */
    public function getWidgetVariables($hookName, array $configuration = [])
    {
        $context = \Context::getContext();
        $id_customer = isset($context->customer->id) ? (int) $context->customer->id : null;
        $job = false;
        if ($id_customer) {
            $sql = new \DbQuery();
            $sql->select('*')->from('customer_job')->where('id_customer = ' . $id_customer);
            $job = \Db::getInstance()->getRow($sql);
        }

        return [
            'customer_job' => $job,
            'hookName' => $hookName,
        ];
    }

    public function install()
    {
        // Registra i controller Symfony
        if (!$this->registerSymfonyRoutes()) {
            return false;
        }

        $installMenu = new InstallMenu($this);

        return parent::install()
            && $this->registerHook([
                'displayAdminCustomer',
                'actionAdminControllerSetMedia',
                'actionFrontControllerSetMedia',
                'actionCustomerFormBuilderModifier',
                'actionCustomerFormDataProviderData',
                'actionCustomerFormDataProviderDefaultData',
                'actionCustomerAccountAdd',
                'actionCustomerAccountUpdate',
                'actionCustomerGridDefinitionModifier',
                'actionCustomerGridQueryBuilderModifier',
                'additionalCustomerFormFields',
                'validateCustomerFormFields',
                'displayCustomerAccount',
                'displayCustomerAccountForm',
                'displayCustomerAccountFormTop',
            ])
            && (new ModelMpCustomerJobArea())->install()
            && (new ModelMpCustomerJobName())->install()
            && (new ModelMpCustomerJobLink())->install()
            && (new ModelMpCustomerJob())->install()
            && $installMenu->installMenu(
                'AdminMpCustomerJobs',
                'Gestione Professioni clienti',
                'AdminParentCustomer'
            );
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Restituisce le rotte del modulo per PrestaShop
     * Questo metodo è chiamato automaticamente da PrestaShop per caricare le rotte del modulo
     *
     * @return array
     */
    public function getRoutes()
    {
        return [
            'admin_module_routes' => [
                [
                    'route' => 'admin_mporderflag_index',
                    'path' => '/mporderflag',
                    'methods' => ['GET'],
                    'controller' => 'MpOrderFlagController::indexAction',
                ],
                /*
                [
                    'route' => 'admin_mporderflag_view',
                    'path' => '/mporderflag/{id_product}',
                    'methods' => ['GET'],
                    'controller' => 'MpOrderFlagController::viewAction',
                    'defaults' => [
                        '_legacy_controller' => 'AdminMpOrderFlag',
                    ],
                    'requirements' => [
                        'id_product' => '\d+',
                    ],
                ],
                [
                    'route' => 'admin_mpwacart_requests_send_message',
                    'path' => '/mpwacart/requests/send-message',
                    'methods' => ['POST'],
                    'controller' => 'WaCartRequestsController::sendMessageAction',
                    'defaults' => [
                        '_legacy_controller' => 'AdminWaCartRequests',
                    ],
                ],
                [
                    'route' => 'admin_mpwacart_requests_update_status',
                    'path' => '/mpwacart/requests/update-status',
                    'methods' => ['POST'],
                    'controller' => 'WaCartRequestsController::updateStatusAction',
                    'defaults' => [
                        '_legacy_controller' => 'AdminWaCartRequests',
                    ],
                ],
                [
                    'route' => 'admin_mpwacart_requests_delete',
                    'path' => '/mpwacart/requests/{requestId}/delete',
                    'methods' => ['POST'],
                    'controller' => 'WaCartRequestsController::deleteAction',
                    'defaults' => [
                        '_legacy_controller' => 'AdminWaCartRequests',
                    ],
                    'requirements' => [
                        'requestId' => '\d+',
                    ],
                ],
                [
                    'route' => 'admin_mpwacart_requests_bulk_delete',
                    'path' => '/mpwacart/requests/bulk-delete',
                    'methods' => ['POST'],
                    'controller' => 'WaCartRequestsController::bulkDeleteAction',
                    'defaults' => [
                        '_legacy_controller' => 'AdminWaCartRequests',
                    ],
                ],
                */
            ],
        ];
    }

    /**
     * Registra le rotte Symfony per il modulo
     *
     * @return bool
     */
    private function registerSymfonyRoutes()
    {
        try {
            // In PrestaShop 8.x, i moduli possono registrare le rotte tramite il file routes.yml
            // che viene caricato automaticamente dal sistema
            // Non è necessario registrare manualmente le rotte

            // Verifica che il file routes.yml esista
            $routesPath = $this->getLocalPath() . 'config/routes.yml';
            if (!file_exists($routesPath)) {
                throw new \Exception('File routes.yml non trovato: ' . $routesPath);
            }

            return true;
        } catch (\Exception $e) {
            if (isset($this->context->controller)) {
                $this->context->controller->errors[] = $this->l('Errore durante la registrazione delle rotte Symfony: ') . $e->getMessage();
            }

            return false;
        }
    }

    /**
     * Rimuove le rotte Symfony per il modulo
     *
     * @return bool
     */
    private function unregisterSymfonyRoutes()
    {
        // In PrestaShop, le rotte vengono rimosse automaticamente quando il modulo viene disinstallato
        return true;
    }

    /**
     * Hook: actionAdminControllerSetMedia
     * Imposta gli stili e gli script nel BO
     */
    public function hookActionAdminControllerSetMedia($params)
    {
        // TODO: Implementare visualizzazione sopra il form account cliente
        return '';
    }

    public function hookAdditionalCustomerFormFields($params)
    {
        $fields = ModelMpCustomerJob::$definition['fields'];

        $formFields = [
            (new FormField)
                ->setName('id_customer_job_area')
                ->setType('select')
                ->setLabel($this->trans('Settore'))
                ->setRequired(false)
                ->addConstraint('isUnsignedId')
                ->setAvailableValues($this->jobAreaList)
                ->setValue(0),

            (new FormField)
                ->setName('id_customer_job_name')
                ->setType('select')
                ->setLabel($this->trans('Professione'))
                ->setRequired(false)
                ->addConstraint('isUnsignedId')
                ->setAvailableValues($this->jobNameList)
                ->setValue(0),
        ];

        $paramsFormFields = $params['fields'];
        // divido paramsFormFields in due array: optin e formfields
        $optinFields = [];
        $customerFields = [];
        foreach ($paramsFormFields as $key => $field) {
            if ($key === 'optin') {
                $optinFields[$key] = $field;
            } else {
                $customerFields[$key] = $field;
            }
        }
        $outFields = array_merge($customerFields, $formFields, $optinFields);
        $params['fields'] = $outFields;

        return false;
    }

    public function hookValidateCustomerFormFields($params)
    {
        $id_customer_job_area = Tools::getValue('id_customer_job_area', 0);
        $id_customer_job_name = Tools::getValue('id_customer_job_name', 0);

        if ($id_customer_job_area == 0 && $id_customer_job_name == 0) {
            return;
        }

        return true;
    }

    protected function getFrontCustomerFields()
    {
        $fields = [
            'id_customer_job_area' => Tools::getValue('id_customer_job_area', ''),
            'id_customer_job_name' => Tools::getValue('id_customer_job_name', ''),
        ];

        return $fields;
    }

    /**
     * Hook: actionAdminControllerSetMedia
     * Imposta gli stili e gli script nel BO
     */
    public function hookActionFrontControllerSetMedia($params)
    {
        // Inject JS for dynamic job select only on registration/account pages
        $controller = $this->context->controller;
        if ($controller && in_array($controller->php_self, ['authentication', 'my-account', 'registration'])) {
            $jsPath = 'modules/' . $this->name . '/views/js/customer_job_form.js';
            $controller->registerJavascript('module-customer-job-form', $jsPath, ['position' => 'bottom', 'priority' => 150]);
        }

        return '';
    }

    /**
     * Hook: displayAdminCustomer
     * Visualizza informazioni aggiuntive nella scheda cliente in back office
     */
    public function hookDisplayAdminCustomer($params)
    {
        // TODO: Implementare visualizzazione dati professioni cliente nel back office
        return '';
    }

    /**
     * Hook: actionCustomerFormBuilderModifier
     * Modifica il form del cliente (front office)
     */
    public function hookActionCustomerFormBuilderModifier($params)
    {
        $formBuilder = $params['form_builder'];
        $context = \Context::getContext();
        $id_lang = (int) $context->language->id;

        // Recupera tutte le aree
        $jobAreas = [];
        $sql = new \DbQuery();
        $sql->select('id_customer_job_area, name')
            ->from('customer_job_area_lang')
            ->where('id_lang = ' . $id_lang)
            ->orderBy('name');
        $result = \Db::getInstance()->executeS($sql);
        if ($result) {
            foreach ($result as $row) {
                $jobAreas[$row['id_customer_job_area']] = \Tools::strtoupper($row['name']);
            }
        }
        $jobAreas = [null => ''] + $jobAreas;

        // Recupera tutte le professioni
        $jobNames = [];
        $sql = new \DbQuery();
        $sql->select('id_customer_job_name, name')
            ->from('customer_job_name_lang')
            ->where('id_lang = ' . $id_lang)
            ->orderBy('name');
        $result = \Db::getInstance()->executeS($sql);
        if ($result) {
            foreach ($result as $row) {
                $jobNames[$row['id_customer_job_name']] = \Tools::strtoupper($row['name']);
            }
        }
        $jobNames = [null => ''] + $jobNames;

        // Aggiungi i campi select al form
        $formBuilder->add('id_customer_job_area', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
            'label' => $this->l('Settore'),
            'required' => false,
            'choices' => $jobAreas,
            'attr' => [
                'class' => 'mp-job-area-select',
            ],
        ]);
        $formBuilder->add('id_customer_job_name', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
            'label' => $this->l('Professione'),
            'required' => false,
            'choices' => $jobNames,
            'attr' => [
                'class' => 'mp-job-name-select',
            ],
        ]);
    }

    /**
     * Hook: actionCustomerFormDataProviderDefaultData
     * Fornisce dati di default al form del cliente
     */
    public function hookActionCustomerFormDataProviderDefaultData($params)
    {
        // TODO: Implementare fornitura dati di default per form cliente
    }

    /**
     * Hook: displayCustomerAccountForm
     * Modifica il form dell'account cliente (front office)
     */
    public function hookDisplayCustomerAccountForm($params)
    {
        return $this->renderWidget('displayCustomerAccountForm', $params);
    }

    /**
     * Hook: displayCustomerAccount
     * Visualizza informazioni aggiuntive nell'account cliente (front office)
     */
    public function hookDisplayCustomerAccountFormTop($params)
    {
        // TODO: Implementare visualizzazione sopra il form account cliente
        return '';
    }

    /**
     * Hook: actionCustomerGridDefinitionModifier
     * Modifica la definizione della griglia clienti in BO
     */
    public function hookActionCustomerGridDefinitionModifier($params)
    {
        $definition = $params['definition'];

        // Add columns
        $definition->getColumns()->addAfter(
            'email',
            (new DataColumn('customer_job_area'))
                ->setName($this->l('Settore'))
                ->setOptions([
                    'field' => 'customer_job_area',
                ])
        );
        $definition->getColumns()->addAfter(
            'customer_job_area',
            (new DataColumn('customer_job_name'))
                ->setName($this->l('Professione'))
                ->setOptions([
                    'field' => 'customer_job_name',
                ])
        );

        // Aggiungi il filtro per customer_job_area
        $definition->getFilters()->add(
            (new Filter('customer_job_area', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                    'attr' => [
                        'placeholder' => $this->trans('Settore', [], 'Modules.MpCustomerJobs.Admin'),
                    ],
                ])
                ->setAssociatedColumn('customer_job_area')
        );

        // Aggiungi il filtro per customer_job_name
        $definition->getFilters()->add(
            (new Filter('customer_job_name', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                    'attr' => [
                        'placeholder' => $this->trans('Professione', [], 'Modules.MpCustomerJobs.Admin'),
                    ],
                ])
                ->setAssociatedColumn('customer_job_name')
        );
    }

    /**
     * Hook: actionCustomerGridQueryBuilderModifier
     * Modifica la query builder della griglia clienti in BO
     */
    public function hookActionCustomerGridQueryBuilderModifier($params)
    {
        /** @var QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];
        /** @var CustomerFilters $searchCriteria */
        $searchCriteria = $params['search_criteria'];

        // Aggiungi id_customer_job_area e id_customer_job_name alla query
        $searchQueryBuilder
            ->addSelect('cj.id_customer_job_area, cj.id_customer_job_name')
            ->addSelect('COALESCE(cjal.name, "--") as customer_job_area')
            ->addSelect('COALESCE(cjnl.name, "--") as customer_job_name')
            ->leftJoin(
                'c',
                _DB_PREFIX_ . 'customer_job',
                'cj',
                'cj.id_customer = c.id_customer'
            )
            ->leftJoin(
                'cj',
                _DB_PREFIX_ . 'customer_job_area_lang',
                'cjal',
                'cjal.id_customer_job_area = cj.id_customer_job_area and cjal.id_lang = ' . (int) $this->context->language->id
            )
            ->leftJoin(
                'cj',
                _DB_PREFIX_ . 'customer_job_name_lang',
                'cjnl',
                'cjnl.id_customer_job_name = cj.id_customer_job_name and cjnl.id_lang = ' . (int) $this->context->language->id
            );

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ($filterName == 'customer_job_area') {
                $searchQueryBuilder->andWhere('cjal.name LIKE :customer_job_area');
                $searchQueryBuilder->setParameter('customer_job_area', '%' . $filterValue . '%');
            }
            if ($filterName == 'customer_job_name') {
                $searchQueryBuilder->andWhere('cjnl.name LIKE :customer_job_name');
                $searchQueryBuilder->setParameter('customer_job_name', '%' . $filterValue . '%');
            }
        }

        // Filtro per customer_job_area
        if (isset($params['filter']['customer_job_area'])) {
            $searchQueryBuilder->andWhere('cjal.name LIKE :customer_job_area');
            $searchQueryBuilder->setParameter('customer_job_area', '%' . $params['filter']['customer_job_area'] . '%');
        }

        // Filtro per customer_job_name
        if (isset($params['filter']['customer_job_name'])) {
            $searchQueryBuilder->andWhere('cjnl.name LIKE :customer_job_name');
            $searchQueryBuilder->setParameter('customer_job_name', '%' . $params['filter']['customer_job_name'] . '%');
        }

        $params['search_query_builder'] = $searchQueryBuilder;
        $params['search_criteria'] = $searchCriteria;
    }
}
