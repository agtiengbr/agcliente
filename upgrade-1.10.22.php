<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_10_22(AgCliente $module)
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
    
    $existent_worker_group = AgClienteWorkerGroup::findByName('main');
    if (!Validate::isLoadedObject($existent_worker_group)) {
        $workerGroup = new AgClienteWorkerGroup;
        $workerGroup->group_name = 'main';
        $workerGroup->qty_wanted_workers = 1;
        $workerGroup->module = 'agcliente';
        $workerGroup->controller = '';
        $workerGroup->active = 1;
        $workerGroup->save();
    }

    $module->installWorkers();
    
    return true;
}