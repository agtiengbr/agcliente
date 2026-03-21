<?php

namespace AGTI\Cliente\Database;

trait DbUtils
{
    private $columns = [];

    function getColumnsFromTable($tableName)
    {
        if (count($this->columns) === 0) {
            $this->columns = array();

            $sql = 'SHOW COLUMNS FROM ' . _DB_PREFIX_ . $tableName . ' IN `' . _DB_NAME_ . '`';
            $columns = \Db::getInstance()->executeS($sql);

            foreach ($columns as $column) {
                $this->columns[] = $column['Field'];
            }
        }

        return $this->columns;
    }
}