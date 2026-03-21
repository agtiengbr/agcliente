<?php

class AgClienteProductZipCode extends AgObjectModel
{
    public static $definition = [
        'table' => 'agcliente_product_zipcode',
        'primary' => 'id_agcliente_product_zipcode',
        'fields' => [
            'id_agcliente_product_zipcode' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_product' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned'),
            'zipcode' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(16)'),
        ],
        'indexes' => [
            [
                'fields' => ['id_product'],
                'prefix' => 'unique',
                'name' => 'uniqueness'
            ]
        ]
    ];

    public $id_agcliente_product_zipcode;
    public $id_product;
    public $zipcode;

    public static function getZipcodeByProduct($id_product)
    {
        $sql = new DbQuery;
        $sql->from('agcliente_product_zipcode')
            ->select('zipcode')
            ->where('id_product=' . (int)$id_product);

        return Db::getInstance()->getValue($sql);
    }

    public static function setZipcodeToProduct($id_product, $zipcode)
    {
        try {
            Db::getInstance()->insert('agcliente_product_zipcode', ['id_product' => (int)$id_product, 'zipcode' => pSQL($zipcode)]);
            Db::getInstance()->update('agcliente_product_zipcode', ['zipcode' => pSQL($zipcode)], 'id_product=' . (int)$id_product);
        } catch (Exception $e) {
            Db::getInstance()->update('agcliente_product_zipcode', ['zipcode' => pSQL($zipcode)], 'id_product=' . (int)$id_product);
        }
    }
}