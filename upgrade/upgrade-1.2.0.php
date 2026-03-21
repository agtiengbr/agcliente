<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_2_0($module)
{
    //reinstala os overrides
    if (Module::isInstalled('agcliente')) {
        $module->registerHook('dashboardZoneTwo');
    }
    
    return true;
}
