<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_15_1($module)
{
    $module->updateModuleTables($module);
    $module->RemakeWorkers($module);

    $module->uninstallOverrides();
    $module->installOverrides();

    return true;
}
