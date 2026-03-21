<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_10_0($module)
{
    /***********************  cria as tabelas no banco **********************/
    $object_models = [
        'AgClienteWorker',
        'AgClienteWorkerGroup'
    ];
    
    foreach ($object_models as $class) {
        $modelInstance = new $class;

        if (method_exists($class, 'createDatabase')) {
            $modelInstance->createDatabase();
        }

        if (method_exists($class, 'createMissingColumns')) {
            $modelInstance->createMissingColumns();
        }

        if (method_exists($class, 'createIndexes')) {
            $modelInstance->createIndexes();
        }

        if (method_exists($class, 'createDefaultData')) {
            $class::createDefaultData();
        }
    }

    /***********************  cria as abas do BO **********************/
    $id_parent = Tab::getIdFromClassName("AdminParentModulesSf");

    $tabModel             = new \Tab();
    $tabModel->module     ="agcliente";
    $tabModel->active     = 1;
    $tabModel->class_name = 'AdminAgClienteWorkerGroup';
    $tabModel->id_parent  = $id_parent;

    foreach (\Language::getLanguages(true) as $lang) {
        $tabModel->name[$lang['id_lang']] = "AdminAgClienteWorkerGroup";
    }


    $id_parent = Tab::getIdFromClassName("AdminAgClienteWorker");

    $tabModel             = new \Tab();
    $tabModel->module     ="agcliente";
    $tabModel->active     = 1;
    $tabModel->class_name = 'AdminAgClienteWorker';
    $tabModel->id_parent  = $id_parent;

    foreach (\Language::getLanguages(true) as $lang) {
        $tabModel->name[$lang['id_lang']] = "AdminAgClienteWorker";
    }


    /************  cria a worker para cálculo da tabela offline dos correios  ********************/
    $workerGroup = new AgClienteWorkerGroup;
    $workerGroup->group_name = 'agcorreios_calc_prices';
    $workerGroup->qty_wanted_workers = 2;
    $workerGroup->module = 'agcorreios';
    $workerGroup->controller = 'CalcPrices';
    $workerGroup->active = 1;
    $workerGroup->save();


    return true;
}