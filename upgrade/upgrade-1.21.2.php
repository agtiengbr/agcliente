<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_21_2($module)
{
    $module->updateModuleTables($module);
    return true;
}
