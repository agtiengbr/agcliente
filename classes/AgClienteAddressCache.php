<?php

use PrestaShop\PrestaShop\Core\Exception\DatabaseException;

class AgClienteAddressCache extends AgObjectModel
{
    public static $definition = [
        'table' => 'agcliente_address_cache',
        'primary' => 'id_agcliente_address_cache',
        'fields' => [
            'id_agcliente_address_cache' => ['type' => self::TYPE_INT],
            'cache_key'          => ['type' => self::TYPE_INT,    'db_type' => 'int'],
            'address_data'       => ['type' => self::TYPE_STRING, 'db_type' => 'text'],
            'date_add'           => ['type' => self::TYPE_DATE,   'db_type' => 'datetime'],
            'expire_date'        => ['type' => self::TYPE_DATE,   'db_type' => 'datetime'],
        ],
        'indexes' => [
            [
                'fields' => ['cache_key'],
                'name' => 'uniqueness'
            ]
        ]
    ];

    public $id_agcliente_address_cache;
    public $cache_key;
    public $address_data;
    public $date_add;
    public $expire_date;

    /**
     * @return AgMelhorEnvioCache
     */
    public static function get($cache_key)
    {
        $sql = new DbQuery;
        $sql->from('agcliente_address_cache')
            ->where('cache_key=' . (int) $cache_key)
            ->where('expire_date >= "' . date('Y-m-d H:i:s') . '"');
        $db_data = Db::getInstance()->getRow($sql, false);

        $error = Db::getInstance()->getMsgError();

        if ($error) {
            throw new PrestaShopDatabaseException($error);
        }

        if (!is_array($db_data)) {
            $db_data = [];
        }

        $return = new AgClienteAddressCache();
        $return->hydrate($db_data);

        return $return;
    }

    /**
     * Salva o cache no banco de dados.
     * 
     * @throws Exception Erro de validação do Object Model
     * @throws DatabaseException Erro gravando os dados no BD.
     */
    public static function store($cache_key, $data)
    {
        // checa se o cache já foi inserido
        $exists_address_cache = AgClienteAddressCache::get($cache_key);
        if (!\Validate::isLoadedObject($exists_address_cache)) {

            $obj = new AgClienteAddressCache;

            $obj->cache_key = $cache_key;
            $obj->address_data = json_encode($data['address_data'], JSON_UNESCAPED_UNICODE);
            $obj->expire_date = $data['expire_date'];

            $valid = $obj->validateFields(false, true);

            if ($valid !== true) {
                throw new Exception($valid);
            }

            $obj->add();

            $error = Db::getInstance()->getMsgError();
            if ($error) {
                throw new DatabaseException($error);
            }
        } else {
            $obj = $exists_address_cache;
        }

        return $obj;
    }
}
