<?php

namespace AGTI\Cliente\Mapping;

use AGTI\Cliente\Mapping\Adapter\DjtalBrazilianRegister;
use AGTI\Cliente\Mapping\Factory\RegistrationModuleAdapterFactory;
use Customer;
use InvalidArgumentException;

class IEMapping extends DatabaseMapping
{
    public function __construct()
    {
        $this->setTableName('customer');
    }

    public function getLabelForSelect()
    {
        return 'Inscrição Estadual';
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

        $adapter = RegistrationModuleAdapterFactory::createAdapter($this->getMappedValue());
        return $adapter->getIe($customer);
    }
}