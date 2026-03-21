<?php

class AgClienteConfig extends AgObjectModel
{
    public static $definition = array(
        'table' => 'agconfig',
        'primary' => 'id_agconfig',
        'multilang' => false,
        'fields' => array(
            'id_agconfig' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'db_type' => 'int unsigned'),
            'id_agti_shop' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(8)'),
            'name' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(10000)'),
            'value' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            
        )        
    );

    public $id_agconfig;
    public $id_shop;
    public $id_agti_shop;
    public $name;
    public $value;

    public static function getFromName($name, $id_agti_shop)
    {
        $sql = new DbQuery();
        $sql->from('agconfig')
            ->where('id_shop=' . (int)Context::getContext()->shop->id)
            ->where('name="'. pSQL($name) . '"')
            ->where('id_agti_shop="' . pSQL($id_agti_shop) . '"');

        $db_data = Db::getInstance()->getRow($sql);
        if (!is_array($db_data)) {
            $db_data = array();
        }

        $return = new AgClienteConfig();
        $return->hydrate($db_data);
        
        return $return;
    }

    public static function getDefaultShop()
    {
        $shop = new Shop(Configuration::get('PS_SHOP_DEFAULT'));
        return $shop;
    }
    
    static function getModuleTables($module_name)
    {
        $sql = "SELECT 
                    table_name
                FROM information_schema.tables
                WHERE table_type = 'BASE TABLE'
                    AND table_name LIKE '" . _DB_PREFIX_ . "{$module_name}_%'
                ";

        $resp = Db::getInstance()->executeS($sql);

        return $resp;
    }

    static function CleanModuleTables($module)
    {
        $return = [];
        
        $module_tables =  AgClienteConfig::getModuleTables($module->name);

        if (count($module_tables) > 0) {
            foreach ($module_tables as $table) {
                try {
                    // Limpa as tabelas do módulo
                    $length = strlen(_DB_PREFIX_);
                    $table = substr($table['table_name'], $length);

                    Db::getInstance()->delete(
                        $table
                    );

                    $requests_deleted = Db::getInstance()->Affected_Rows();
                    Logger::addLog($module->name . " - Limpeza da tabela - {$table} concluida - {$requests_deleted} linhas deletadas", '1', '', '', '', true, Context::getContext()->employee->id);
                } catch (Exception $ex) {
                    Logger::addLog($module->name . ' - Ocorreu um erro ao limpar a tabela - ' . $table . ' - ' . $ex->getMessage(), 3, $ex->getCode(), '', '', true, Context::getContext()->employee->id);
                    $this->errors[$table['table_name']] = $ex->getMessage();
                }
            }
        } else {
            Logger::addLog($module->name . ' - Ocorreu um erro ao limpar a tabela - não foram encontradas as tabelas para a exclusão', 3, 404, '', '', true, Context::getContext()->employee->id);
            $return[$module->name] = 'Não foram encontradas as tabelas para a exclusão';
        }
    }

    static function CleanModuleConfiguration($module)
    {
        $return = [];

        // Busca na tabela de configurações variaveis que iniciem com o nome do módulo 
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'configuration WHERE name like "' . $module->name . '%"';
        $result = Db::getInstance()->ExecuteS($query);

        if (count($result) > 0) {
            try {
                foreach ($result as $item) {
                    Db::getInstance()->delete('configuration', 'id_configuration=' . (int) $item['id_configuration']);
                }

                $requests_deleted = count($result);
                Logger::addLog($module->name . " - Limpeza das configurações da {$module->name} concluida - {$requests_deleted} linhas deletadas", '1', '', '', '', true, Context::getContext()->employee->id);
            } catch (Exception $ex) {
                Logger::addLog($module->name . ' - Ocorreu um erro ao remover as configurações salvas - ' . $ex->getMessage(), 3, $ex->getCode(), '', '', true, Context::getContext()->employee->id);
                $return[$module->name] = $ex->getMessage();
            }
        }
    }

    static function CleanModuleWorkers($module) {
        $return = [];
        
        // Busca todos os grupos de workers do módulo
        $all_workers_group = AgClienteWorkerGroup::findByModuleName($module->name);
        if (count($all_workers_group) > 0) {
            try {
                foreach ($all_workers_group as $worker_group) {
                    // Busca todos workers do grupo
                    $workers = AgClienteWorker::findByGroup($worker_group);
                    if (count($workers) > 0) {
                        foreach ($workers as $worker) {
                            $worker->delete();
                        }
                    }

                    $worker_shop = AgClienteWorkerGroupShop::getFromWorkerGroup($worker_group);
                    if (Validate::isLoadedObject($worker_shop)) {
                        $worker_shop->delete();
                    }

                    $worker_group->delete();
                }
            } catch (Exception $ex) {
                Logger::addLog($module->name . ' - Ocorreu um erro ao remover os workers - ' . $ex->getMessage(), 3, $ex->getCode(), '', '', true, Context::getContext()->employee->id);
                $return[$module->name] = $ex->getMessage();
            }
        }
    }
}
