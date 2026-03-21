<?php

namespace AGTI\Cliente\Mapping;

use AGTI\Cliente\Database\DbUtils;

class DatabaseMapping extends DomainMapping
{
    use DbUtils;

    protected $tableName;
    protected $label;
    
    public function getLabelForSelect()
    {
        return $this->getLabel();
    }
    
    public function getAvailableOptions()
    {
        $columns = $this->getColumnsFromTable($this->getTableName());

        $return = [];

        $return['mapping_disabled'] = 'Sem Mapeamento';
        foreach ($columns as $column) {
            $return[$column] = $column;
        }

        return $return;
    }

    function getValue(...$objects)
    {
        $definition = \ObjectModel::getDefinition($objects[0]);
        $identifier = $definition['primary'];

        $id_object = @(int)$objects[0]->{$identifier} ?: (int)$objects[0]->id;

        $dbPrefix = _DB_PREFIX_;
        $sql = "SELECT {$this->getMappedValue()} from {$dbPrefix}{$this->getTableName()} where {$identifier}={$id_object}";

        return \Db::getInstance()->getValue($sql);
    }

    function setValue($value, ...$objects)
    {
        $definition = \ObjectModel::getDefinition($objects[0]);
        $identifier = $definition['primary'];

        $id_object = @(int)$objects[0]->{$identifier} ?: (int)$objects[0]->id;

        $dbPrefix = _DB_PREFIX_;
        $sql = "UPDATE {$dbPrefix}{$this->getTableName()} SET {$this->getMappedValue()} = '" . pSQL($value) . "' where {$identifier}={$id_object}";

        \Db::getInstance()->execute($sql);
    }

    /**
     * Get the value of tableName
     */ 
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set the value of tableName
     *
     * @return  self
     */ 
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Get the value of label
     */ 
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the value of label
     *
     * @return  self
     */ 
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }
}