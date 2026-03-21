<?php

class AgObjectModel extends ObjectModel
{
    public static function startTransaction()
    {
        Db::getinstance(_PS_USE_SQL_SLAVE_)->execute('START TRANSACTION');
    }

    public static function commit()
    {
        Db::getinstance(_PS_USE_SQL_SLAVE_)->execute('COMMIT');
    }

    public static function rollback()
    {
        Db::getinstance(_PS_USE_SQL_SLAVE_)->execute('ROLLBACK');
    }
    /**
     * Return informations of the columns that exists in the
     * table relative to the ObjectModel. If the Model has multilang enabled,
     * this method also returns information about the multilang table.
     */
    public function getDatabaseColumns()
    {
        $definition = ObjectModel::getDefinition($this);

        $sql = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA="' . pSQL(_DB_NAME_) .
            '" AND TABLE_NAME="' . pSQL(_DB_PREFIX_ . $definition['table']) . '"';

        $columns['self'] = Db::getInstance()->executeS($sql, true, false);

        $sql = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA="' . pSQL(_DB_NAME_) .
            '" AND TABLE_NAME="' . pSQL(_DB_PREFIX_ . $definition['table']) . '_lang"';

        $columns['lang'] = Db::getInstance()->executeS($sql, true, false);


        return $columns;
    }

    /**
     * Add a column in the table relative to the ObjectModel.
     * This method uses the $definition property of the ObjectModel,
     * with some extra properties.
     *
     * Example:
     * 'table'        => 'tablename',
     * 'primary'      => 'id',
     * 'fields'       => [
     *     'id'     => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
     *     'number' => [
     *         'type'     => self::TYPE_STRING,
     *         'db_type'  => 'varchar(20)',
     *         'required' => true,
     *         'default'  => '25'
     *     ],
     * ],
     *
     * The primary column is created automatically as INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT. The other columns
     * require an extra parameter, with the type of the column in the database.
     *
     *
     */
    public function createColumn(
        $field_name,
        $column_definition
    ) {
        $definition = ObjectModel::getDefinition($this);

        //object model has a multilang table
        $multilang = isset($definition['multilang']) && $definition['multilang'];

        if ($multilang && $column_definition['lang']) {
            $sql = 'ALTER TABLE ' . pSQL(_DB_PREFIX_ . $definition['table']) . '_lang';
        } else {
            $sql = 'ALTER TABLE ' . pSQL(_DB_PREFIX_ . $definition['table']);
        }

        $sql .= ' ADD COLUMN `' . pSQL($field_name) . '` ' . pSQL($column_definition['db_type']);

        if ($field_name === $definition['primary'] && !$column_definition['lang']) {
            $sql .= ' serial';
        } else {
            if (isset($field['required']) && $field['required']) {
                $sql .= ' NOT NULL';
            }

            if (isset($field['default'])) {
                $sql .= ' DEFAULT "' . pSQL($field['default']) . '"';
            }
        }
        
        Db::getInstance()->execute($sql);
    }

    public function createIndex(
        array $fields,
        $index_name,
        $index_prefix = '', //UNIQUE, FULLTEXT or SPATIAL
        $index_type = '' //BTREE or HASH
    ) {
        $definition = ObjectModel::getDefinition($this);

        $object_is_multilang = isset($definition['multilang']) && $definition['multilang'];
        $index_is_multilang = null;

        //check for multilang consistency. If one of the fields in the index is multilang,
        //all of them must to be multilang.
        if ($object_is_multilang) {
            foreach ($fields as $field) {
                $column_definition = $definition['fields'][$field];

                $field_is_multilang = isset($definition['lang']) && $definition['lang'];

                if (!is_null($index_is_multilang) && $index_is_multilang != $field_is_multilang) {
                    throw new Exception(sprintf(
                        'Error creating index %s for table %s. Multilang inconsistency.',
                        $index_name,
                        $definition['table']
                    ));
                }
                $index_is_multilang = $field_is_multilang;
            }
        }

        //check if the index alread exists
        $table_name = _DB_PREFIX_ . $definition['table'];
        if ($index_is_multilang) {
            $table_name .= '_lang';
        }

        $sql = 'SELECT COUNT(1) IndexIsThere FROM INFORMATION_SCHEMA.STATISTICS '.
                        'WHERE table_schema="' . pSQL(_DB_NAME_) . '" AND table_name="' . psQL($table_name) .
                        '" AND index_name="' . pSQL($index_name) . '" AND seq_in_index=1';

        $exists = Db::getInstance()->getValue($sql);

        if ($exists) {            
            return false;
        }    

        $sql = 'CREATE ';

        if ($index_prefix != '') {
            if (!in_array(strtoupper($index_prefix), ['UNIQUE', 'FULLTEXT', 'SPATIAL'])) {
                throw new Exception(sprintf(
                    'Error creating index %s for table %s. Invalid prefix %s',
                    $index_name,
                    $definition['table'],
                    $index_prefix
                ));
            }

            $sql .=  $index_prefix;
        }

        $sql .= ' INDEX ' . pSQL($index_name) . ' ';

        if ($index_type != '') {
            if (!in_array(strtoupper($index_type), ['BTREE', 'HASH'])) {
                throw new Exception(sprintf(
                    'Error creating index %s for table %s. Invalid index type %s',
                    $index_name,
                    $definition['table'],
                    $index_type
                ));
            }

            $sql .=  ' USING ' . pSQL($index_type);
        }

        $sql .= ' ON ' . pSQL($table_name);
        $sql .= ' (' . pSQL(implode(',', $fields)) . ')';

        Db::getInstance()->execute($sql);
    }

    public function createIndexes()
    {
        $definition = ObjectModel::getDefinition($this);
        if (isset($definition['indexes']) && is_array($definition['indexes'])) {
            foreach ($definition['indexes'] as $index) {
                if (isset($index['fields']) && is_array($index['fields'])) {
                    $this->createIndex(
                        $index['fields'],
                        $index['name'],
                        @$index['prefix'],
                        isset($index['type'])? $index['type'] : NULL
                    );
                }
            }
        }
    }

    // public function dropIndex($index_name, $table_name)
    // {
    //     $definition = ObjectModel::getDefinition($this);
    //     $sql = 'ALTER TABLE ' . $table_name . ' DROP INDEX ' . $index_name;

    //     Db::getInstance()->executeS($sql);
    // }

    /**
     *  Create in the database every column detailed in the $definition property that are
     *  missing in the database.
     */
    public function createMissingColumns()
    {
        $columns    = $this->getDatabaseColumns();
        $definition = ObjectModel::getDefinition($this);

        $multilang = isset($definition['multilang']) && $definition['multilang'];


        foreach ($definition['fields'] as $column_name => $column_definition) {
            //column exists in database
            $exists = false;


            if ($multilang && @$column_definition['lang']) {
                //column exists in database
                foreach ($columns['lang'] as $column) {
                    if ($column['COLUMN_NAME'] === $column_name) {
                        $exists = true;
                        break;
                    }
                }
            } else {
                foreach ($columns['self'] as $column) {
                    if ($column['COLUMN_NAME'] === $column_name) {
                        $exists = true;
                        break;
                    }
                }
            }

            if (!$exists) {
                $this->createColumn($column_name, $column_definition);
            }
        }

        //verify the foreign keys in the multilang table
        if ($multilang) {
            //id_lang column
            $column_name = 'id_lang';
            $exists = false;
            foreach ($columns['lang'] as $column) {
                if ($column['COLUMN_NAME'] === $column_name) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $column_definition = ['lang' => true, 'db_type' => 'int unsigned'];
                $this->createColumn($column_name, $column_definition);
            }

            //foreign key column
            $column_name = $definition['primary'];
            $exists = false;
            foreach ($columns['lang'] as $column) {
                if ($column['COLUMN_NAME'] === $column_name) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $column_definition = ['lang' => true, 'db_type' => 'int unsigned'];
                $this->createColumn($column_name, $column_definition);
            }
        }
    }

    /**
     *  Create the database table with its columns. Similar to the createColumn() method.
     */
    public function createDatabase()
    {
        $definition = ObjectModel::getDefinition($this);

        $multilang = isset($definition['multilang']) && $definition['multilang'];


        $sql = 'CREATE TABLE IF NOT EXISTS ' . pSQL(_DB_PREFIX_ . $definition['table']) . ' (';
        $sql .= '`' . pSQL($definition['primary']) . '` INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,';

        foreach ($definition['fields'] as $field_name => $field) {
            if ($field_name === $definition['primary']) {
                continue;
            }

            if ($multilang && @$field['lang']) {
                continue;
            }

            $sql .= '`' . pSQL($field_name) . '` ' . pSQL($field['db_type']);

            if (isset($field['required']) && $field['required']) {
                $sql .= ' NOT NULL';
            }

            if (isset($field['default'])) {
                $sql .= ' DEFAULT "' . $field['default'] . '"';
            }

            $sql .= ',';
        }

        $sql = trim($sql, ',');
        $sql .= ')';

        $sql .= ' COLLATE utf8_general_ci ';
        $sql .= ' ENGINE=InnoDB ';

        if (!Db::getInstance()->execute($sql)) {
            $msg_error = Db::getInstance()->getMsgError();
            Logger::addLog(sprintf('Error creating table %s - %s', $definition['table'], $msg_error), 3, null, null, null, true);
        }

        //create multilang tables
        if ($multilang) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . pSQL(_DB_PREFIX_ . $definition['table']) . '_lang (';
            $sql .= pSQL($definition['primary']) . ' INTEGER UNSIGNED NOT NULL,';
            $sql .= 'id_lang INTEGER UNSIGNED NOT NULL,';

            if (@$definition['multilang_shop']) {
                $sql .= 'id_shop INTEGER UNSIGNED NOT NULL,';
            }

            foreach ($definition['fields'] as $field_name => $field) {
                if ($field_name === $definition['primary']) {
                    continue;
                }

                if (@!$field['lang']) {
                    continue;
                }

                $sql .= pSQL($field_name) . ' ' . pSQL($field['db_type']);

                if (isset($field['required']) && $field['required']) {
                    $sql .= ' NOT NULL';
                }

                if (isset($field['default'])) {
                    $sql .= ' DEFAULT "' . pSQL($field['default']) . '"';
                }

                $sql .= ',';
            }

            $sql = trim($sql, ',');
            $sql .= ')';

            $sql .= ' COLLATE utf8_general_ci ';
            $sql .= ' ENGINE=InnoDB ';

            if (!Db::getInstance()->execute($sql)) {
                $msg_error = Db::getInstance()->getMsgError();
                Logger::addLog(sprintf('Error creating table %s - %s', $definition['table'] . '_lang', $msg_error), 3, null, null, null, true);
            }
        }

    }
    
    public function dropDatabase()
    {
        $definition = ObjectModel::getDefinition($this);
        $multilang = isset($definition['multilang']) && $definition['multilang'];

        $sql = 'DROP TABLE IF EXISTS' .pSQL(_DB_PREFIX_ . $definition['table']);
        Db::getInstance()->execute($sql);

        if ($multilang) {
            $sql = 'DROP TABLE IF EXISTS' . pSQL(_DB_PREFIX_ . $definition['table']) . '_lang';
            Db::getInstance()->execute($sql);
        }
    }
}
