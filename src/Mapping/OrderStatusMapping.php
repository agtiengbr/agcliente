<?php

namespace AGTI\Cliente\Mapping;

class OrderStatusMapping implements MappingInterface
{
    protected $configName;
    protected $label;

    public function isMultiple()
    {
        return false;
    }
    
    function getAvailableOptions()
    {
        return \OrderState::getOrderStates(\Context::getContext()->language->id);
    }

    function getValue(...$key)
    {
        return $this->getMappedValue();
    }

    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function getLabelForSelect()
    {
        return $this->label;
    }
    
    public function getOptionsForSelect()
    {
        $items = $this->getAvailableOptions();

        $return = [['id' => -1, 'name' => 'Mapeamento Desativado']];
        foreach ($items as $value) {
            $return[] = [
                'id' => $value['id_order_state'],
                'name' => $value['name']
            ];
        }

        return $return;
    }

    public function mapsTo($value)
    {
        \Configuration::updateValue($this->getConfigName(), $value);
    }

    public function getMappedValue()
    {
        return \Configuration::get($this->getConfigName());
    }

    /**
     * Get the value of configName
     */ 
    public function getConfigName()
    {
        return $this->configName;
    }

    /**
     * Set the value of configName
     *
     * @return  self
     */ 
    public function setConfigName($configName)
    {
        $this->configName = $configName;

        return $this;
    }
}