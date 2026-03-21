<?php

class AgClienteWorker extends AgObjectModel
{
    public static $definition = array(
        'table' => 'agworker',
        'primary' => 'id_agworker',
        'multilang' => false,
        'fields' => array(
            'id_agworker' => array('type' => self::TYPE_INT),
            'group_name'       => array('type' => self::TYPE_STRING,  'db_type' => 'varchar(64)'),
            'id_shop' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned'),
            'workers_hash'         => array('type' => self::TYPE_STRING,  'db_type' => 'varchar(64)'),
            'killed'         => array('type' => self::TYPE_BOOL,  'db_type' => 'boolean'),
            'idx'       => array('type' => self::TYPE_INT,  'db_type' => 'int'),
            'date_upd'    => array('type' => self::TYPE_DATE, 'db_type' => 'datetime'),           
        ),
        'indexes' => [
            [
                'fields' => ['date_upd'],
                'name' => 'for_removal',
            ],
            [
                'fields' => ['group_name', 'workers_hash', 'id_shop', 'date_upd'],
                'name' => 'search',
            ]
        ]
    );

    public $id_agworker;
    
    //identificador único de cada rotina. ex: agcorreios_calc_prices
    public $group_name;

    public $id_shop;

    //se a chave do worker pai for diferente de $key então essa worker deve ser finalizada
    public $workers_hash;

    //se já foi enviado um comando para matar a worker ou não
    public $killed;

    //0: primero worker (deve processar o primeiro bloco de objetos)
    //1: segundo worker (deve processar o segundo bloco de objetos)
    public $idx;
    public $date_upd;

    /**
     * @return AgClienteWorkerGroup
     */
    public function getWorkerGroup()
    {
        return AgClienteWorkerGroup::findByName($this->group_name);
    }

    public static function findByGroup(AgClienteWorkerGroup $group, $check_hash=false)
    {
        $asso = AgClienteWorkerGroupShop::getFromWorkerGroup($group);

        $delay = '90';
        if ($group->delay) {
            $delay = $group->delay;
        }

        $sql = new DbQuery;

        $sql->from('agworker') 
            ->where('group_name="' . pSQL($group->group_name) . '"')
            ->where('id_shop=' . (int)Context::getContext()->shop->id)
            ->where('date_upd > "' . date('Y-m-d H:i:s', strtotime("-{$delay} seconds")) . '"')
            ->where('killed=0 OR killed IS NULL');
        
        if ($check_hash) {
            $sql->where('workers_hash="' . pSQL($asso->key_for_workers) . '"');
        }

        $db_data = Db::getInstance()->executeS($sql);
        if (!is_array($db_data)) {
            $db_data = [];
        }
        
        return ObjectModel::hydrateCollection('AgClienteWorker', $db_data);
    }

    /**
     * Obtém os indices de início e fim do loop baseado na quantidae
     * de workers processando, e também no índice do elemento atual
     */
    public function getInitAndEndIndexes($total_elem, $total_workers = null, $idx_worker = null)
    {
        if (is_null($idx_worker)) {
             $idx_worker = $this->idx;
        }

        if (is_null($total_workers)) {
            $group = $this->getWorkerGroup();
            $total_workers = $group->qty_wanted_workers;
        }

        $elems_per_worker = (int)$total_elem / $total_workers;
        $begin = $elems_per_worker * ($idx_worker - 1);
        $end = $begin + $elems_per_worker;

        return [
            'begin' => $begin,
            'end' => $end
        ];
    }

    
    public function add($auto_date = true, $null_values = false)
    {
        if (!Tools::getValue('debug')) {
            $this->checkShouldDie();
            return parent::add($auto_date, $null_values);
        } else {
            return parent::add($auto_date, $null_values);
        }
    }

    public function update($null_values = false)
    {
        if (!Tools::getValue('debug')) {
            $this->checkShouldDie();
            return parent::update($null_values);
        } else {
            return parent::update($null_values);
        }
    }

    public function checkShouldRun()
    {
        if ($this->group_name == 'agcorreios_calc_prices') {
            if (file_exists(_PS_MODULE_DIR_ . 'agcorreios/agcorreios.php')) {
                require_once _PS_MODULE_DIR_ . 'agcorreios/agcorreios.php';

                $module = new agcorreios;
                if (!method_exists($module, 'getOptions')) {
                    return false;
                }
                
                $options = $module->getOptions();
                    

                if (!$options['agcorreios_precalculate'] || !$options['agcorreios_zipcode_origin']) {
                    return false;
                }
            } else {
                return false;
            }
        } elseif ($this->group_name === 'aginstallments_calc_fees') {
            return Configuration::get('aginstallments_calc_fees_next_time') < time();
        }
        
        return true;
    }
    
    protected function checkShouldDie()
    {
        $group = $this->getWorkerGroup();
        $shopAsso = AgClienteWorkerGroupShop::getFromWorkerGroup($group);

        if ($shopAsso->key_for_workers != $this->workers_hash) {
            AgClienteLogger::addLog('finalizando worker '. $this->id);
            exit();
        }

        if (!$this->checkShouldRun()) {
            exit();
        }

        return false;
    }
}