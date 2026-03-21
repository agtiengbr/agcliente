<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_2_2($module)
{
    //reinstala os overrides
    if (Module::isInstalled('agcliente')) {
        $module->registerHook('dashboardZoneTwo');
    }

    $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'agconfig CHANGE value value text';
    try {
    	Db::getInstance()->execute($sql);
    } catch (Exception $e) {}
    
    return true;
}
