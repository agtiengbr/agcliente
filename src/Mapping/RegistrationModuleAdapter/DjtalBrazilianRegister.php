<?php

namespace AGTI\Cliente\Mapping\RegistrationModuleAdapter;

use Address;
use Customer;

class DjtalBrazilianRegister implements RegistrationModuleAdapterInterface
{
    public static function getCpf(\Customer $customer, \Address $address):string
    {
        $sql = new \DbQuery;
        $sql->from('djtalbrazilianregister')
            ->where('id_customer=' . (int)$customer->id);

        $data = \Db::getInstance()->getRow($sql);

        return @$data['cpf'];
    }

    public static function setCpf($value, \Customer $customer, \Address $address)
    {
        \Db::getInstance()->update(
            'djtalbrazilianregister',
            [
                'cpf' => pSQL($value)
            ],
            'id_customer=' . (int)$customer->id
        );
    }

    public static function getCnpj(\Customer $customer, \Address $address):string
    {
        $sql = new \DbQuery;
        $sql->from('djtalbrazilianregister')
            ->where('id_customer=' . (int)$customer->id);

        $data = \Db::getInstance()->getRow($sql);

        return @$data['cnpj'];
    }

    public static function setCnpj($value, \Customer $customer, \Address $address)
    {
        \Db::getInstance()->update(
            'djtalbrazilianregister',
            [
                'cnpj' => pSQL($value)
            ],
            'id_customer=' . (int)$customer->id
        );
    }

    public static function getCompanyName(Customer $customer): string
    {
        return $customer->company ?: "";
    }

    public static function setCompanyName($value, Customer $customer)
    {
        $customer->company = $value;
        $customer->save();
    }

    public static function getRg(Customer $customer): string
    {
        return '';
    }

    public static function setRg($value, Customer $customer)
    {
    }

    public static function getIe(Customer $customer): string
    {
        return '';
    }

    public static function setIe($value, Customer $customer)
    {
    }

    public static function getAddressNumber(Address $customer): string
    {
        return '';
    }

    public static function setAddressNumber($value, Address $customer)
    {
        
    }

    public static function getPersonType(Customer $customer): string
    {
        return '';
    }

    public static function setPersonType($value, Customer $customer)
    {
        
    }
}