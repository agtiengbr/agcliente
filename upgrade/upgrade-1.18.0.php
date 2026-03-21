<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_18_0($module)
{
    $sql = "ALTER TABLE "._DB_PREFIX_." agworker_group ADD COLUMN time_from TIME,ADD COLUMN time_to TIME;";
    try {
    	Db::getInstance()->execute($sql);
    } catch (Exception $e) {}
    
    return true;
}
