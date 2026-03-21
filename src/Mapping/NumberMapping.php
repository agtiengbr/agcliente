<?php

namespace AGTI\Cliente\Mapping;

use AGTI\Cliente\Mapping\Adapter\DjtalBrazilianRegister;
use AGTI\Cliente\Mapping\Factory\RegistrationModuleAdapterFactory;
use Customer;
use InvalidArgumentException;

class NumberMapping extends DatabaseMapping
{
    public function __construct()
    {
        $this->setTableName('address');
    }

    public function getLabelForSelect()
    {
        return 'Número';
    }

    public function getAvailableOptions()
    {
        $options = parent::getAvailableOptions();
        return $options;
    }

    
    public function getValue(...$objects)
    {
        /** @var \Address */
        $address = $objects[0];
        if ($address instanceof \Address === false) {
            throw new InvalidArgumentException("Primeiro parâmetro deve ser do tipo Address.");
        }

        if (in_array($this->getMappedValue(), $this->getColumnsFromTable($this->getTableName()))) {
            return call_user_func('parent::getValue', ...$objects);;
        }

        $adapter = RegistrationModuleAdapterFactory::createAdapter($this->getMappedValue());
        return $adapter->getAddressNumber($address);
    }
}