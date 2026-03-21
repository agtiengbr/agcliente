<?php

class AgClienteWorkerGroupShop extends AgObjectModel
{
    public static $definition = array(
        'table' => 'agworker_group_shop',
        'primary' => 'id_agworker_group_shop',
        'multilang' => false,
        'multishop' => true,
        'fields' => array(
            'id_agworker_group_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'key_for_workers' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),
            'id_agworker_group' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned'),
            'id_shop' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned'),
            'date_upd' => array('type' => self::TYPE_DATE, 'db_type' => 'datetime')
        ), 
        'indexes' => [
            [
                'fields' => ['id_agworker_group', 'id_shop'],
                'prefix' => 'unique',
                'name' => 'unique_group'
            ]
        ]
    );

    public $id_agworker_group_shop;
    public $id_agworker_group;
    public $key_for_workers;
    public $date_upd;
    public $id_shop;
    
    /**
     * @return AgClienteWorkerGroupShop
     */
    public static function getFromWorkerGroup(AgClienteWorkerGroup $group)
    {
        $sql = new DbQuery;
        $sql->from('agworker_group_shop')
            ->where('id_shop =' . (int)Context::getContext()->shop->id)
            ->where('id_agworker_group=' . (int)$group->id);

        $db_data = Db::getInstance()->getRow($sql);
        if (!is_array($db_data)) {
           $obj = new AgClienteWorkerGroupShop;
           $obj->id_agworker_group = $group->id;
           $obj->id_shop = (int)Context::getContext()->shop->id;
           $obj->save();

           $obj->id_agworker_group_shop = $obj->id;
           return $obj;
        }

        $return = new AgClienteWorkerGroupShop;
        $return->hydrate($db_data);
        $return->id = $return->id_agworker_group_shop;

        return $return;
    }

    public function killWorkers()
    {
        $this->key_for_workers = uniqid();
        $this->update();
    }
}