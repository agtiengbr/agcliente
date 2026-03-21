<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_2_7($module)
{
    $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'agconfig CHANGE value value text';
    try {
    	Db::getInstance()->execute($sql);
    } catch (Exception $e) {}
    
    return true;
}
