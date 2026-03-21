<?php

trait AgClienteModuleTrait
{
    //aba pai que conterá todas as abas do módulo
    protected $main_tab = '';
    protected $tabs = array();
    protected $tabs_ps16 = array();
    protected $hooks = array();
    protected $workers = [];

    public $errors = [];
    public $warnings = [];
    public $confirmations = [];
    public $informations = [];

    public $ps17 = false;
    public $ps16 = false;
    public $ps8 = false;

    public function __construct()
    {
        $this->loadClasses();
        parent::__construct();

        $this->ps17 = version_compare(_PS_VERSION_, '1.7', '>=') && version_compare(_PS_VERSION_, '1.8', '<');
        $this->ps16 = version_compare(_PS_VERSION_, '1.6', '>=') && version_compare(_PS_VERSION_, '1.7', '<');
        $this->ps8 = version_compare(_PS_VERSION_, '8', '>=') && version_compare(_PS_VERSION_, '10', '<');
    }

    public function install()
    {
        // checa se o módulo já foi criado
        $flg_reinstall = false;
        $moduleExists = AgClienteConfig::getModuleTables($this->name);
        if (count($moduleExists) > 0) {
            $flg_reinstall = true;
        }

        foreach (\get_declared_classes() as $i => $class) {
            $myReflection = new ReflectionClass($class);
            if(!$myReflection->isSubclassOf('AgObjectModel')) {
                continue;
            }

            if (
                //verifica se trata-se de uma class na "namespace" do módulo atual
                strpos(strtolower($class), strtolower($this->name)) === 0

                //ignora a criação do banco se houver namespaces
                && strpos(strtolower($class), strtolower($this->name) . '\\') === false
            ) {
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
        }

        $return = parent::install();

        if (!$return) {
            $this->errors[] = Tools::displayError('Erro ao instalar o módulo');
            return false;
        }

        if ($return && isset($this->tabs) && is_array($this->tabs)) {
            $parent_tab = 0;

            if ($this->ps16 && !empty($this->main_tab_ps16)) {
                $parent_tab = Tab::getIdFromClassName($this->main_tab_ps16);
            } elseif (!empty($this->main_tab)) {
                $parent_tab = Tab::getIdFromClassName($this->main_tab);
            }

            if ($this->ps16 && count($this->tabs_ps16)) {
                $tabs = $this->tabs_ps16;
            } else {
                $tabs = $this->tabs;
            }

            $return = $this->addTab($tabs, $parent_tab);
        }

        if ($return && isset($this->hooks)) {
            $return = $this->registerHook($this->hooks);
        }

        if (method_exists($this, 'resetConfig') && !$flg_reinstall) {
            $this->resetConfig();
        }

        $this->RemakeWorkers();
        
        return (bool) $return;
    }

    public function uninstall()
    {
        $success = true;

        if (isset($this->tabs) && is_array($this->tabs)) {
            $success = $this->removeTab($this->tabs);
        }

        return $success && parent::uninstall();
    }

    protected function resetConfig()
    {
        $this->installWorkers();
    }

    public function loadClasses()
    {
        require_once _PS_MODULE_DIR_ . 'agcliente/lib/AgObjectModel.php';
        require_once _PS_MODULE_DIR_ . 'agcliente/lib/Loader.php';

        AgLoader::loadDir(_PS_MODULE_DIR_ . 'agcliente/lib');
        AgLoader::loadDir(_PS_MODULE_DIR_ . 'agcliente');

        AgLoader::loadDir(_PS_MODULE_DIR_ . $this->name . '/lib');
        AgLoader::loadDir(_PS_MODULE_DIR_ . $this->name . '/classes');
    }

    public static function authModule($module_name, $license_key = '', $try_remote = false)
    {
        return true;
    }

    public function auth($license_key = '')
    {
        $this->is_authenticated = true;
        return true;
    }

    public function generateDefaultHelperForm()
    {
        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module          = $this;
        // $helper->name_controller = $this->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex . '&configure=' . $this->name;

        // title and Toolbar
        $helper->title          = $this->displayName;
        $helper->show_toolbar   = true; // false -> remove toolbar
        $helper->toolbar_scroll = true; // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action  = 'submit' . $this->name;
        $helper->toolbar_btn    = array(
            'save' => array(
                'desc' => "Salvar",
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => "Voltar",
            ),
        );

        return $helper;
    }



    public function RemakeMenus()
    {
        $this->removeTab();
        $this->addTab();
    }

    protected function addTab(
        $tabs = [],
        $id_parent = 0
    ) {
        $return = true;

        if (count($tabs) === 0) {
            if ($this->ps16 && count($this->tabs_ps16)) {
                $tabs = $this->tabs_ps16;
            } else {
                $tabs = $this->tabs;
            }

            if ($this->ps16 && !empty($this->main_tab_ps16)) {
                $id_parent = \Tab::getIdFromClassName($this->main_tab_ps16);
            } elseif (!empty($this->main_tab)) {
                $id_parent = \Tab::getIdFromClassName($this->main_tab);
            }
        }

        foreach ($tabs as $tab) {
            $tab_id = \Tab::getIdFromClassName($tab["className"]);
            if ($tab_id) {
                $om = new \Tab($tab_id);
                $om->delete();
                continue;
            }

            $tabModel             = new \Tab();
            $tabModel->module     = isset($tab['module']) ? $tab['module'] : $this->name;
            $tabModel->active     = $tab["active"];
            $tabModel->class_name = $tab["className"];
            $tabModel->id_parent  = $id_parent;

            foreach (\Language::getLanguages(true) as $lang) {
                //multi-idiomas
                if (is_array($tab['name'])) {
                    if (isset($tab['name'][$lang['iso_code']])) {
                        $tabModel->name[$lang['id_lang']] = $tab['name'][$lang['iso_code']];
                    } else {
                        $tabModel->name[$lang['id_lang']] = $tab['name']['default'];
                    }
                } else {
                    if (isset($tab["name"])) {
                        $tabModel->name[$lang['id_lang']] = $tab["name"];
                    } else {
                        $tabModel->name[$lang['id_lang']] = '';
                    }
                }
            }

            $return &= $tabModel->add();

            if (isset($tab['childs']) && is_array($tab["childs"])) {
                $this->addTab($tab["childs"], \Tab::getIdFromClassName($tab["className"]));
            }
        }
        return true;
    }

    protected function removeTab($tabs = [])
    {
        if (count($tabs) === 0) {
            if ($this->ps16 && count($this->tabs_ps16)) {
                $tabs = $this->tabs_ps16;
            } else {
                $tabs = $this->tabs;
            }
        }

        try {
            foreach ($tabs as $tab) {
                $id_tab = (int) \Tab::getIdFromClassName($tab["className"]);
                if ($id_tab) {
                    $tabModel = new \Tab($id_tab);
                    $tabModel->delete();
                }

                if (isset($tab["childs"]) && is_array($tab["childs"])) {
                    $this->removeTab($tab["childs"]);
                }
            }
        } catch (\Exception $e) {
        }

        return true;
    }

    public function saveNotifications()
    {
        $notifications = json_encode(array(
            'error' => $this->errors,
            'warning' => $this->warnings,
            'success' => $this->confirmations,
            'info' => $this->informations,
        ));

        $this->context->cookie->agnotifications = $notifications;
    }

    public function prepareNotifications()
    {
        $controller = $this->context->controller;
        $notifications = array(
            'error' => $controller->errors ?? [],
            'warning' => $controller->warnings ?? [],
            'success' => $controller->confirmations ?? [],
            'info' => $controller->informations ?? [],
        );

        if (isset($this->context->cookie->agnotifications)) {
            $notifications = array_merge($notifications, json_decode($this->context->cookie->agnotifications, true));
            unset($this->context->cookie->agnotifications);
        }

        if (!empty($notifications['error'])){
            $controller->errors = $notifications['error'];
        }

        if (!empty($notifications['success'])){
            $controller->confirmations = $notifications['success'];
        }

        if (!empty($notifications['info'])){
            $controller->informations = $notifications['info'];
        }

        if (!empty($notifications['warning'])){
            $controller->warnings = $notifications['warning'];
        }
    }

    public static function redirectToConfigPage($module_name)
    {
        $context = Context::getContext();
        $link = $context->link->getAdminLink('AdminModules') . "&configure=$module_name";
        Tools::redirectAdmin($link);
    }

    public function installWorkers()
    {
        foreach ($this->workers as $worker) {
            $existent_worker_group = AgClienteWorkerGroup::findByName("{$this->name}_{$worker['name']}");
            if (!Validate::isLoadedObject($existent_worker_group)) {
                $existent_worker_group = new AgClienteWorkerGroup;
                $existent_worker_group->group_name = "{$this->name}_{$worker['name']}";
            }

            $existent_worker_group->key_for_workers = uniqid();

            if (!isset($worker['qty_wanted_workers'])) {
                $worker['qty_wanted_workers'] = 1;
            }
            $existent_worker_group->qty_wanted_workers = $worker['qty_wanted_workers'];

            if (!isset($worker['delay'])) {
                $worker['delay'] = 90;    
            }
            $existent_worker_group->delay = $worker['delay'];

            if (!isset($worker['module'])) {
                $worker['module'] = $this->name;
            }

            $existent_worker_group->module = $worker['module'];
            $existent_worker_group->controller = $worker['controller'];

            if (isset($worker['querystring'])) {
                $existent_worker_group->querystring = $worker['querystring'];
            }

            if (!isset($worker['active'])) {
                $worker['active']  = 1;
            }
            $existent_worker_group->active = $worker['active'];

            if (isset($worker['time_to'])) {
                $existent_worker_group->time_to = $worker['time_to'];
            }

            if (isset($worker['time_from'])) {
                $existent_worker_group->time_from = $worker['time_from'];
            }            

            $existent_worker_group->save();
        }
    }

    public function addLocalJs($file, $identifier, $options=[])
    {
        $controller = Context::getContext()->controller;
        if ($this->ps17 && $controller instanceof FrontController) {
            //verifica se existe arquivo .min.js
            $file_to_check = _PS_MODULE_DIR_ . substr($file, 0, -3) . '.min.js';
            if (file_exists($file_to_check)) {
                $controller->registerJavascript($identifier, "modules/" . substr($file, 0, -3) . ".min.js", $options);
            } else {
                $controller->registerJavascript($identifier, "modules/$file", $options);
            }
        } else {
            throw new Exception("Opção compatível apenas com PS 1.7");
        }
    }

    public function RemakeWorkers()
    {
        if (count($this->workers) > 0) {
            try {
                $this->installWorkers();

                // Busca todos os grupos de workers do módulo
                $all_workers_group = AgClienteWorkerGroup::findByModuleName($this->name);
                if (count($all_workers_group) > 0) {
                    foreach ($all_workers_group as $worker_group) {
                        if (Validate::isLoadedObject($worker_group)) {
                            // busca o worker e troca o id dele para forçar a recriação
                            $take_worker_working = AgClienteWorkerGroupShop::getFromWorkerGroup($worker_group);
                            if (Validate::isLoadedObject($take_worker_working)) {
                                $take_worker_working->killWorkers();
                            }
                        }
                    }
                }
            } catch (Exception $ex) {
                $this->errors[0] = 'Ocorreu um erro ao recriar os workers, verifique os registros da loja';
            }
        }
    }

    public function getHooks()
    {
        return $this->hooks;
    }
}
