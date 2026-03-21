<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_14_3($module)
{
    $module->installWorkers();
    return true;
}
