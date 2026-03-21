<?php

class agclienteUpgradeModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        set_time_limit(0);

        $module_name = Tools::getValue('module_name');

        try {
            $obj = Module::getInstanceByName($module_name);
            if ($obj === false) {
                throw new Exception("Módulo {$module_name} não encontrado.");
            }

            $sql_version = new DbQuery;
            $sql_version->select('version')->from('module')->where('name="' . pSQL($module_name) . '"');

            $obj->database_version = Db::getInstance()->getValue($sql_version);
            $obj->installed = Module::isInstalled($module_name);

            Module::initUpgradeModule($obj);
            $obj->runUpgradeModule();
            
            $errors = $obj->getErrors();
            if (count($errors)) {
                $obj->disable();
                Logger::addLog("Ocorreu um erro ao atualizar o módulo {$module_name}, e por esse motivo ele foi desativado. Se o problema persistir por favor abra uma solicitação de suporte em www.agti.eng.br/contato.", 4);
            } else {
                Module::upgradeModuleVersion($module_name, $obj->version);
            }

            echo json_encode([
                'success'=> true
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success'=> false,
                'error' => sprintf('%s - %s', get_class($e), $e->getMessage())
            ]);
        }

        exit();
    }
}
