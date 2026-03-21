<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_19_0($module)
{
    unlink(_PS_MODULE_DIR_ . 'agcliente/override/classes/Cart.php');
    unlink(_PS_MODULE_DIR_ . 'agcliente/controllers/front/SimulateCarriers.php');

    $module->uninstallOverrides();
    $module->installOverrides();
    
    return true;
}
