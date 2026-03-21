<?php


namespace AGTI\Cliente\Mapping\Factory;

use AGTI\Cliente\Mapping\RegistrationModuleAdapter\DjtalBrazilianRegister;
use AGTI\Cliente\Mapping\RegistrationModuleAdapter\FkCustomers;
use AGTI\Cliente\Mapping\RegistrationModuleAdapter\LdCustomers;
use AGTI\Cliente\Mapping\RegistrationModuleAdapter\RegistrationModuleAdapterInterface;

class RegistrationModuleAdapterFactory
{
    public static function createAdapter($moduleName)
    {
        switch ($moduleName) {
            case 'djtalbrazilianregister':
                return new DjtalBrazilianRegister;
            default:
                throw new \Exception("Módulo {$moduleName} não encontrado.");
        }
    }
}