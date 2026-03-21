<?php

namespace AGTI\Cliente\Mapping\RegistrationModuleAdapter;

interface RegistrationModuleAdapterInterface
{
    public static function getCpf(\Customer $customer, \Address $address):string;
    public static function getCnpj(\Customer $customer, \Address $address):string;
    public static function getCompanyName(\Customer $customer):string;
    public static function getRg(\Customer $customer):string;
    public static function getIe(\Customer $customer):string;
    public static function getAddressNumber(\Address $customer):string;
    public static function getPersonType(\Customer $customer):string;


    public static function setCpf($value, \Customer $customer, \Address $address);
    public static function setCnpj($value, \Customer $customer, \Address $address);
    public static function setCompanyName($value, \Customer $customer);
    public static function setRg($value, \Customer $customer);
    public static function setIe($value, \Customer $customer);
    public static function setAddressNumber($value, \Address $customer);
    public static function setPersonType($value, \Customer $customer);
}