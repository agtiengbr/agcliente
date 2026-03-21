<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_20_7($module)
{
    //remove diretório com nome incorreto
    if (is_dir(_PS_MODULE_DIR_ . 'agcliente/src/Entity/ServiceRespose')) {
        unlink(_PS_MODULE_DIR_ . 'agcliente/src/Entity/ServiceRespose/AddressFinder.php');
        unlink(_PS_MODULE_DIR_ . 'agcliente/src/Entity/ServiceRespose/AddWorkingDaysLocal.php');
        unlink(_PS_MODULE_DIR_ . 'agcliente/src/Entity/ServiceRespose/GetHolidaysLocal.php');

        rmdir(_PS_MODULE_DIR_ . 'agcliente/src/Entity/ServiceRespose/');
    }
    
    rename(_PS_MODULE_DIR_ . 'agcliente/src/Factory/DeliveyTimeFormatterFactory.php', _PS_MODULE_DIR_ . 'agcliente/src/Factory/DeliveryTimeFormatterFactory.php');



    if (is_dir(_PS_MODULE_DIR_ . 'agcliente/src/Form/Presenter')) {
        unlink(_PS_MODULE_DIR_ . 'agcliente/src/Form/Presenter/Tab.php');
        unlink(_PS_MODULE_DIR_ . 'agcliente/src/Form/Presenter/Tabs.php');

        rmdir(_PS_MODULE_DIR_ . 'agcliente/src/Form/Presenter/');
    }

    return true;
}
