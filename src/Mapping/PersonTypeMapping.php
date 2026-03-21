<?php

namespace AGTI\Cliente\Mapping;

use AGTI\Cliente\Mapping\Factory\RegistrationModuleAdapterFactory;
use Customer;
use InvalidArgumentException;

class PersonTypeMapping  extends DatabaseMapping
{
    const PERSON_TYPE_BRAZILIAN = 'pf';
    const PERSON_TYPE_ENTERPRISE = 'pj';
    const PERSON_TYPE_FOREIGNER = 'nbr';
    const PERSON_TYPE_OTHER = 'o';

    public function __construct()
    {
        $this->setTableName('customer');
    }

    public function getLabelForSelect()
    {
        return 'Tipo de Pessoa';
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
        if ($customer instanceof \Customer === false) {
            throw new InvalidArgumentException("Primeiro parâmetro deve ser do tipo Customer.");
        }

        if (in_array($this->getMappedValue(), $this->getColumnsFromTable($this->getTableName()))) {
            return call_user_func('parent::getValue', ...$objects);;
        }

        $adapter = RegistrationModuleAdapterFactory::createAdapter($this->getMappedValue());
        return $adapter->getPersonType($customer);
    }

    public function setValue($value, ...$objects)
    {
        /** @var Customer */
        $customer = $objects[0];
        if ($customer instanceof \Customer === false) {
            throw new InvalidArgumentException("Primeiro parâmetro deve ser do tipo Customer.");
        }

        if (in_array($this->getMappedValue(), $this->getColumnsFromTable($this->getTableName()))) {
            return call_user_func('parent::setValue', $value, ...$objects);;
        }

        $adapter = RegistrationModuleAdapterFactory::createAdapter($this->getMappedValue());
        return $adapter->setPersonType($value, $customer);
    }
}