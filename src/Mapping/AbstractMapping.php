<?php

namespace AGTI\Cliente\Mapping;

abstract class AbstractMapping implements MappingInterface
{
    protected $configName;
    
    /**
     * Retorna todas as opções do mapeamento em um formato para ser usado em um input do tipo select da classe FormHelper
     */
    public function getOptionsForSelect()
    {
        $items = $this->getAvailableOptions();

        $return = [];
        foreach ($items as $name=>$value) {
            $return[] = [
                'id' => $name,
                'name' => $value
            ];
        }

        return $return;
    }

    /*
     * Salva a configuração do mapeamento
     */
    public function mapsTo($value)
    {
        \Configuration::updateValue($this->getConfigName(), $value);
    }

    /**
     * Retorna a opção de mapeamento escolhida pelo cliente
     */
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

    public function isMultiple()
    {
        return false;
    }
}