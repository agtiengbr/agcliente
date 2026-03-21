<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_10_20()
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
    
    return true;
}