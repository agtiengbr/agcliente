<?php

namespace AGTI\Cliente\Mapping;

use AGTI\Cliente\Mapping\Factory\RegistrationModuleAdapterFactory;
use Customer;
use InvalidArgumentException;

class CPFMapping extends DatabaseMapping
{
    public function __construct()
    {
        $this->setTableName('customer');
    }

    public function getLabelForSelect()
    {
        return 'CPF';
    }

    public function getAvailableOptions()
    {
        $options = parent::getAvailableOptions();

        $options['djtalbrazilianregister'] = 'Módulo de Cadastro Brasileiro';
        $options['fkcustomers'] = 'FK Customers';
        $options['ldcustomers'] = 'LD Customers';

        return $options;
    }

    public function getValue(...$objects)
    {
        /** @var Customer */
        $customer = $objects[0];
        if ($customer instanceof Customer === false) {
            throw new InvalidArgumentException("Primeiro parâmetro deve ser do tipo Customer.");
        }


        if (in_array($this->getMappedValue(), $this->getColumnsFromTable($this->getTableName()))) {
            return call_user_func('parent::getValue', ...$objects);;
        }
        
        /** @var \Address */
        $address = $objects[1];
        if ($address instanceof \Address === false) {
            $address = new \Address;
        }

        $adapter = RegistrationModuleAdapterFactory::createAdapter($this->getMappedValue());
        return $adapter->getCpf($customer, $address);
    }
}