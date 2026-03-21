<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_20_2($module)
{
    $module->updateModuleTables($module);
    return true;
}
