<?php

class AgClienteWorkerGroup extends AgObjectModel
{
    public static $definition = array(
        'table' => 'agworker_group',
        'primary' => 'id_agworker_group',
        'multilang' => false,
        'fields' => array(
            'id_agworker_group' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'group_name' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),
            'qty_wanted_workers' => array('type' => self::TYPE_INT, 'db_type' => 'int'),
            'delay' => array('type' => self::TYPE_INT, 'db_type' => 'int'),
            'module' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),
            'controller' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),
            'querystring' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),
            'time_to' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(8)'),
            'time_from' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(8)'),
            'querystring' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),
            'active' => array('type' => self::TYPE_BOOL, 'db_type' => 'boolean'),
            'date_upd' => array('type' => self::TYPE_DATE, 'db_type' => 'datetime')
        ), 
        'indexes' => [
            [
                'fields' => ['group_name'],
                'prefix' => 'unique',
                'name' => 'unique_group'
            ]
        ]
    );

    public $id_agworker_group;
    public $group_name;
    public $qty_wanted_workers;
    public $delay;
    public $module;
    public $controller;
    public $querystring;
    public $date_upd;
    public $time_to;
    public $time_from;
    public $active;

    public static function getAll()
    {
        $sql = new DbQuery;
        $sql->from('agworker_group', 'ag')
            ->where('ag.active=1');

        $db_data = Db::getInstance()->executeS($sql);
        if (!$db_data) {
            $db_data = [];
        }

        return ObjectModel::hydrateCollection('AgClienteWorkerGroup', $db_data);
    }

    public static function findByName($name)
    {
        $sql = new DbQuery;
        $sql->from('agworker_group');
        $sql->where('group_name="' . pSQL($name) . '"');

        $db_data = Db::getInstance()->getRow($sql);
        if (!$db_data) {
            $db_data = [];
        }

        $obj = new AgClienteWorkerGroup;
        $obj->hydrate($db_data);
        $obj->id = $obj->id_agworker_group;

        return $obj;
    }

    public static function findByModuleName($name)
    {
        $sql = new DbQuery;
        $sql->from('agworker_group');
        $sql->where('module="' . pSQL($name) . '"');

        $db_data = Db::getInstance()->ExecuteS($sql);
        if (!$db_data) {
            $db_data = [];
        }

        return ObjectModel::hydrateCollection('AgClienteWorkerGroup', $db_data);
    }

    public function killWorkers()
    {
        $shopAsso = AgClienteWorkerGroupShop::getFromWorkerGroup($this);

        if (Validate::isLoadedObject($shopAsso)) {
            $shopAsso->killWorkers();
        }
    }

    public function createWorkers()
    {
        $asso = AgClienteWorkerGroupShop::getFromWorkerGroup($this);

        for ($i=1; $i <= $this->qty_wanted_workers; $i++) {
            $worker = new AgClienteWorker;
            $worker->group_name = $this->group_name;
            $worker->workers_hash = $asso->key_for_workers;
            $worker->idx = $i;
            $worker->id_shop = Context::getContext()->shop->id;

            if (!$worker->checkShouldRun()) {
                continue;
            }

            if (!$worker->validateFields()) {
                \Logger::addLog('AgClienteWorkerGroup::createWorkers() - Worker não é válida', 3, null, 'AgClienteWorkerGroup', $this->id_agworker_group, true);
                continue;
            }

            $worker->save();
            if (Db::getInstance()->getMsgError()) {
                \Logger::addLog('AgClienteWorkerGroup::createWorkers() - Erro ao salvar worker: ' . Db::getInstance()->getMsgError(), 3, null, 'AgClienteWorkerGroup', $this->id_agworker_group, true);
                continue;
            }

            $url = Context::getContext()->shop->getBaseURL(true) . 'index.php?fc=module';
            $url .= '&module=' . $this->module;
            $url .= '&controller=' . $this->controller;
            $url .= '&id_agworker=' . $worker->id;

            if ($this->querystring) {
                $url .= "&{$this->querystring}";
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 500);
            curl_exec($ch);
            curl_close($ch);
        }
    }
}