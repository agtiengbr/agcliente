<?php

namespace AGTI\Cliente\Service\Cache;

use AGTI\Cliente\Entity\Address;

class AddressCache
{
    function __construct($postcode = null)
    {
        if (!empty($postcode)) {
            // cache-key = CEP; data = salvos no formato JSON no banco
            // checa se já existe o cache no banco
            $cache_address = $this->Get($postcode);
            if (!$cache_address) {
                return false;
            }

            return $cache_address;
        }
    }

    /** Cache */
    public function Get($postcode)
    {
        $cache_address = \AgClienteAddressCache::get($postcode);
        if (!\Validate::isLoadedObject($cache_address)) {
            return;
        }

        $decoded = json_decode($cache_address->address_data);
        $address = new Address();
        $address
            ->setStreet($decoded->street)
            ->setPostcode($postcode)
            ->setNeighborhood($decoded->district)
            ->setCity($decoded->city)
            ->setState(
                isset($decoded->uf) ? $decoded->uf : (isset($decoded->state) ? $decoded->state : '')
            );
        return $address;
    }

    public function Save($postcode, $data)
    {
        $arr_cache = [];
        $expire_date = new \Datetime();
        $expire_date->modify("+15 day");
        $expire_date = $expire_date->format('Y-m-d H:i:s');

        $arr_cache['address_data'] = $data && method_exists($data, 'toJson') ? $data->toJson() : $data;
        $arr_cache['expire_date'] = $expire_date;

        // percorre todos os arrays atrás de variaveis do tipo string e limpa os espaços laterais
        array_walk_recursive($arr_cache, function (&$v) {
            if (is_string($v)) {
                $v = trim($v);
            }
        });

        $cache_address = \AgClienteAddressCache::store($postcode, $arr_cache);

        return $cache_address;
    }
}
