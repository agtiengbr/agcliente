<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_14_5($module)
{
    $module->updateModuleTables($module);
    $module->RemakeWorkers($module);
    return true;
}
