<?php

use AGTI\Cliente\Entity\ServiceArgs\AddressFinder as ServiceArgsAddressFinder;
use AGTI\Cliente\Service\AddressFinder as ServiceAddressFinder;

require_once 'lib/AgModule.php';
require_once _PS_MODULE_DIR_ . 'agcliente/vendor/autoload.php';

class BaseAgCliente extends AgModule
{
    protected $hooks = array(
        'displayHeader',
        'displayBackOfficeHeader',
        'dashboardZoneTwo'
    );
    protected $tabs = array(
        array(
            "name"      => "AdminAgClienteWorkerGroup",
            "className" => "AdminAgClienteWorkerGroup",
            "active"    => 0
        ),
        array(
            "name"      => "AdminAgClienteWorker",
            "className" => "AdminAgClienteWorker",
            "active"    => 0
        ),
        array(
            'name' => 'AdminAgClienteZipcode',
            'className' => 'AdminAgClienteZipcode',
            'active' => 0
        )
    );

    protected $workers = [
        [
            'name' => 'main'
        ],
        [
            'name' => 'removeOld',
            'controller' => 'worker',
            'querystring' => 'action=removeOld',
            'delay' => 60*60*24
        ],
        [
            'name' => 'cron',
            'controller' => 'cron',
            'delay' => 60*15
        ]
    ];

    public function __construct()
    {
        $this->name                   = 'agcliente';
        $this->version                = '1.21.12';
        $this->bootstrap              = true;
        $this->author                 = 'AGTI';
        $this->need_instance          = 1;
        $this->ps_versions_compliancy = array('min' => '1.7.6', 'max' => '9.999');
        parent::__construct();

        $this->displayName = $this->l('AgCliente');
        $this->description = $this->l('This module is required to use the modules from AGTI.', 'base');
    }

    public function install()
    {
        Configuration::updateValue('AGCLIENTE_ENABLE_CRONJOB', 1, false, null, $this->context->shop->id_shop_group, $this->context->shop->id);

        $free_shipping_texts = [];
        foreach (Language::getLanguages() as $language) {
            if (!Configuration::get('AGTI_SIMULATION_FREE_SHIPPING_TEXT', $language['id_lang'])) {
                switch($language['language_code']) {
                    case 'pt-br':
                    case 'pt-pt':
                        $free_shipping_texts[$language['id_lang']] = 'Frete Grátis';
                        break;

                    default:
                        $free_shipping_texts[$language['id_lang']] = 'Free Shipping';
                        break;
                }
            }
        }

        if (count($free_shipping_texts)) {
            Configuration::updateValue('AGTI_SIMULATION_FREE_SHIPPING_TEXT', $free_shipping_texts);
        }

        return parent::install();
    }

    public function resetConfig()
    {
        parent::resetConfig();
        Configuration::updateValue('AGTI_SIMULATE_SHIPPING_AJAX_ONLY', 1);
    }

    public function hookDisplayHeader()
    {
        if ($this->ps17 || $this->ps8) {
            $this->context->controller->addJs(array(
                _PS_MODULE_DIR_ . $this->name . '/views/js/jquery.mask.min.js'
            ));
        } else {
            //incompatibilidade com o fkcustomers no PS 16
            if (Module::isInstalled('fkcustomers') && Module::isEnabled('fkcustomers')) {
                Media::addJsDef([
                    'agcliente_mask' => false,
                ]);
                
                $this->context->controller->addJs(_PS_MODULE_DIR_ . $this->name . '/views/js/shipping_simulation.ps16.js');
            } else {
                $this->context->controller->addJs(array(
                    _PS_MODULE_DIR_ . $this->name . '/views/js/jquery.mask.min.js',
                    _PS_MODULE_DIR_ . $this->name . '/views/js/shipping_simulation.ps16.js'
                ));
            }
        }


        if (
            (($this->ps17 || $this->ps8) && $this->context->shop->theme->getName() === 'warehouse')
            || ($this->ps16 && $this->context->shop->theme_name === 'warehouse')
        ) {
            $this->context->controller->addCss($this->_path . 'views/css/warehouse.css');
        }

    }

    

    public function getContent()
    {
        $this->context->controller->addJs([
            "https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js",
            'https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js',
            "https://cdn.jsdelivr.net/npm/maska@1.5.1/dist/maska.js",
            $this->_path . 'views/js/compare_versions.js',
            $this->_path . 'views/js/component/tabs/tabs.js',
            $this->_path . 'views/js/component/panel/panel.js',
            $this->_path . 'views/js/component/modal.js',
            $this->_path . 'views/js/component/dropdown/dropdown.js',
            $this->_path . 'views/js/configuration/config/component.vue.js',
            $this->_path . 'views/js/component/loading/loading.vue.js'
        ]);

        Media::addJsDef([
            'agcliente' => [
                'urls' => [
                    'worker' => $this->context->link->getModuleLink('agcliente', 'worker'),
                ],
                'worker_running' => time() - Configuration::get('AGCLIENTE_WORKER_WATCHDOG') < 60*15,
                'php_version' => phpversion()
            ]
        ]);
                

        // $translationService = $this->get('prestashop.service.translation');

        switch (Tools::getValue('action')) {
            default:
                break;
        }

        if (Tools::getIsSet('request_type')) {

            try {
                 /** Comandos da aba Manutenção */
                 $request_type = Tools::getValue('request_type');
                 $extra = Tools::getValue('extra');
 
                 $module_name = isset($extra['module']) && is_string($extra['module']) ? $extra['module'] : '';
 
                 if (empty($request_type)) {
                     Logger::addLog($module_name . ' - Erro nas ações das configurações - não foi informado nenhuma ação', 3, '', '', '', true, $this->context->employee->id);
 
                     exit();
                 }
 
                 $file_module = _PS_MODULE_DIR_ . "{$module_name}/{$module_name}.php";
 
                 if (!file_exists($file_module)) {
                     throw new Exception("Módulo {$module_name} não encontrado na loja.");
                 }
 
                 require_once $file_module;
 
                 /** @var AgModule */
                 $module = new $module_name;

                switch ($request_type) {
                    case 'updateModuleTables':
                        $this->updateModuleTables($module);
                        break;
                    case 'JustCleanModuleTables':
                        $this->JustCleanModuleThings($module);
                        break;
                    case 'RemoveModuleTables':
                        $this->RemoveModuleTables($module);
                        break;
                    case 'ResetConfigs':
                        $this->ResetConfigs($module);
                        break;
                    case 'RemakeMenus':
                        $module->RemakeMenus();
                        break;
                    case 'RemakeWorkers':
                        $module->RemakeWorkers();
                        break;
                    case 'ResetHooks':
                        $module->registerHook($module->getHooks());
                        break;
                    default:
                        
                        break;
                }
            } catch (Exception $ex) {
                Logger::addLog($module_name . ' - Ocorreu um erro ao executar a ação - ' . $ex->getMessage(), 3, $ex->getCode(), '', '', true, $this->context->employee->id);

                $this->errors[]['module'] = $module_name;
                $this->errors[]['erro'] = $ex->getMessage();
            }

            echo json_encode(['errors' => $this->errors]);

            exit();
        }

        $this->prepareNotifications();

        if (Tools::isSubmit('agcliente_submit')) {
            Configuration::updateValue('AGCLIENTE_ENABLE_CRONJOB', Tools::getValue('AGCLIENTE_ENABLE_CRONJOB'), false, $this->context->shop->id_shop_group, $this->context->shop->id);
        }

        if (version_compare(_PS_VERSION_, '9', '<')) {
            $this->context->controller->addJquery();
        }
        
        $this->context->controller->addJs(array(
            $this->_path . 'views/js/configuration.js',
        ));

        $this->context->controller->addCss(array(
            $this->_path . 'views/css/configuration.css'
        ));

        $html = $this->display(_PS_MODULE_DIR_ . $this->name, 'configuration.tpl');
        return $html;
    }

    public function displayFriendlyUrlWarning()
    {
        return $this->display(_PS_MODULE_DIR_ . $this->name, 'views/templates/hook/friendly_url_warning.tpl');
    }




    public function addOverride($classname)
    {
        try {
            $reflectionClass = new ReflectionClass('Cart');
            if (
                $reflectionClass->getMethod('getDeliveryOptionList')->class !== 'agcliente'
                && $reflectionClass->getMethod('getPackageShippingCost')->class !== 'agcliente'
                && $reflectionClass->getMethod('groupProductsByOrigin')->class !== 'agcliente'
            ) {
                Configuration::updateValue('AGCLIENTE_OVERRIDE_CART', 1);
                return parent::addOverride($classname);
            } else {
                Configuration::updateValue('AGCLIENTE_OVERRIDE_CART', 0);
                return true;
            }
        } catch (Exception $e) {
            //método não existe
            Configuration::updateValue('AGCLIENTE_OVERRIDE_CART', 1);
            return parent::addOverride($classname);
        }
    }

    public function renderShippingForm($module_instance=null)
    {
        return $this->display($this->_path, 'shipping_form.tpl');
    }

    public function checkSupportForm()
    {
    }


    /*********************** hooks *******************************/

    public function hookDisplayBackOfficeHeader()
    {
        $this->handleAjax();
        $this->generateReviewWarnings();

        if (version_compare(_PS_VERSION_, '9', '<')) {
            $this->context->controller->addJquery();
        }

        $this->context->controller->addJs(array(
            '//cdn.jsdelivr.net/bluebird/3.5.0/bluebird.min.js',
            $this->_path . 'views/js/agmodal.js',
            $this->_path . 'views/js/backoffice.js',
        ));

        $this->context->controller->addCss(array(
            $this->_path . 'views/css/agmodal.css',
            $this->_path . 'views/css/backoffice.css'
        ));

        $controllerName = $this->context->controller->controller_name ?? '';
        switch ($controllerName) {
                //a ausência do break é intencional
            case 'AdminDashboard':
            case 'AdminModules':
            case 'AdminModulesManage':
                if (!Configuration::get('PS_REWRITING_SETTINGS')) {
                    $this->context->controller->warnings[] = $this->displayFriendlyUrlWarning();
                }

                $this->context->controller->addJs($this->_path . 'views/js/tab_maintenance.js');
                break;
            case 'AdminMaintenance':
                break;
         }

        // Provide i18n strings used by agcliente Vue grid header
        Media::addJsDef([
            'agcliente_i18n' => [
                'filter' => $this->trans('Filter', [], 'Modules.Agcliente.Admin'),
                'clear' => $this->trans('Clear', [], 'Modules.Agcliente.Admin'),
                'allResults' => $this->trans('All results', [], 'Modules.Agcliente.Admin'),
                'yes' => $this->trans('Yes', [], 'Modules.Agcliente.Admin'),
                'no' => $this->trans('No', [], 'Modules.Agcliente.Admin'),
            ]
        ]);
    }

    public function hookDashboardZoneTwo()
    {
        return;
    }

    public static function prepareConfigHelpTab($module_name)
    {
        Context::getContext()->smarty->assign([
            'license' => '',
            'update_permission_end' => '',
            'has_support'        => true,
            'wiki_url'           => '',
            'issues_url'         => '',
            'shop_email'         => '',
            'new_license_link'   => '',
            'renew_license_link' => ''
        ]);
    }

    public static function prepareConfigMaintenanceTab($module_name)
    {
        Context::getContext()->smarty->assign([
            'ag_maintenance_module_name' => $module_name,
            'ag_maintenance_token' => Tools::getAdminTokenLite('AdminModules'),
        ]);
    }

    public function generateReviewWarnings()
    {
    }

    public function handleAjax()
    {
    }

    public function getAddressForSimulation($postcode)
    {
        if (!AgClienteShippingSimulation::shopMaySimulate()) {
            return null;
        }
        $postcode = preg_replace("/[^0-9]/", "", $postcode);
        return AddressFinder::findByPostcode($postcode);
    }
    

    public static function renderHelpTab(Module $module)
    {
        agcliente::prepareConfigHelpTab($module->name);
        $instance = new agcliente;
        return $instance->display(_PS_MODULE_DIR_ . 'agcliente', "includes/tab_help.tpl");
    }
    
    public static function renderMaintanceTab(Module $module)
    {
        Media::addJsDef(array(
            'module' => $module->name,
            'token' => Tools::getAdminTokenLite('AdminModules'),
        ));

        agcliente::prepareConfigMaintenanceTab($module->name);
        $instance = new agcliente;

        return $instance->display(_PS_MODULE_DIR_ . 'agcliente', "includes/tab_maintenance.tpl");
    }
    
    /** Funções da aba manutenção */
    public function updateModuleTables($module)
    {
        $class_path = _PS_MODULE_DIR_ . $module->name . '/classes/';
        $files = array_diff(scandir($class_path), array('.', '..'));

        foreach ($files as $class) {
            if (file_exists($class_path . $class)) {
                try {
                    include_once $class_path . $class;
                    $class = str_replace('.php', '', $class);
                    if (class_exists($class)) {
                        //instantiate the module
                        $modelInstance = new $class();
                        if (method_exists($class, 'createDatabase')) {
                            $modelInstance->createDatabase();
                        }

                        if (method_exists($class, 'createMissingColumns')) {
                            $modelInstance->createMissingColumns();
                        }

                        if (method_exists($class, 'createIndexes')) {
                            $modelInstance->createIndexes();
                        }

                        if (method_exists($class, 'createDefaultData')) {
                            $class::createDefaultData();
                        }
                    }
                } catch (Exception $ex) {
                    Logger::addLog($module->name . ' - Ocorreu um erro ao atualizar a tabela - ' . $class . ' - ' . $ex->getMessage(), 3, $ex->getCode(), '', '', true, $this->context->employee->id);
                    $this->errors[]['tabela'] = $class;
                    $this->errors[]['erro'] = $ex->getMessage();
                }
            }
        }
    }

    function JustCleanModuleThings($module)
    {
        AgClienteConfig::CleanModuleTables($module);
        AgClienteConfig::CleanModuleConfiguration($module);
        AgClienteConfig::CleanModuleWorkers($module);
    }

    function RemoveModuleTables($module)
    {
        $module_tables =  AgClienteConfig::getModuleTables($module->name);
        if (count($module_tables) > 0) {
            foreach ($module_tables as $table) {
                try {
                    $sql = "DROP TABLE IF EXISTS {$table['table_name']}";
                    $dropped = Db::getInstance()->execute($sql) ? 'ok' : 'error';

                    if ($dropped == 'ok') {
                        Logger::addLog($module->name . " - Tabela - {$table['table_name']} excluída", 1, '', '', '', true, $this->context->employee->id);
                    } else {
                        Logger::addLog($module->name . " - Não foi possível excluir a Tabela - {$table['table_name']}", 3, '', '', '', true, $this->context->employee->id);
                        $this->errors[$table['table_name']] = "Não foi possível excluir a Tabela - {$table['table_name']}";
                    }
                } catch (Exception $ex) {
                    Logger::addLog($module->name . ' - Ocorreu um erro ao excluir a tabela - ' . $table['table_name'] . ' - ' . $ex->getMessage(), 3, $ex->getCode(), '', '', true, $this->context->employee->id);
                    $this->errors[$table['table_name']] = $ex->getMessage();
                }
            }
        } else {
            Logger::addLog($module->name . ' - Ocorreu um erro ao excluir a tabela - não foram encontradas as tabelas para a exclusão', 3, 404, '', '', true, $this->context->employee->id);
            $this->errors[$module->name] = 'Não foram encontradas as tabelas para a exclusão';
        }
    }

    function ResetConfigs($module)
    {
        try {
            $module->resetConfig(true);
        } catch (Exception $ex) {
            Logger::addLog($module->name . ' - Ocorreu um erro ao restaurar todas as configurações do módulo - ' . $ex->getMessage(), 3, $ex->getCode(), '', '', true, $this->context->employee->id);
            $this->errors[] = 'Não foi possível restaurar as configurações do módulo, verifique os registros da loja';
        }
    
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }
}
