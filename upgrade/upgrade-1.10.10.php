<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_10_10()
{
    foreach (Shop::getShops() as $shop) {
        Configuration::updateValue('AGCLIENTE_ENABLE_CRONJOB', 1, false, $shop->id_shop_group, $shop->id);
    }
    
    return true;
}