<?php
class AgColumnMapping
{
    protected $table_name;
    protected $columns = [];
    protected $additional_columns = [];
    protected $configuration_name;

    public function getColumnsFromTable()
    {
        if (count($this->columns) === 0) {
            $this->columns = array();
            $this->columns['mapping_disabled'] = 'Sem Mapeamento';

            $sql = 'SHOW COLUMNS FROM ' . _DB_PREFIX_ . $this->table_name . ' IN `' . _DB_NAME_ . '`';
            $columns = DB::getInstance()->executeS($sql);

            foreach ($columns as $column) {
                $this->columns[$column['Field']] = $column['Field'];
            }
        }
    
        return array_merge($this->columns, $this->additional_columns);
    }

    public function getColumnsForSelect()
    {
        $columns = $this->getColumnsFromTable();

        $return = [];
        foreach ($columns as $key=>$name) {
            $return[] = ['id' => $key, 'name' => $name];
        }

        return $return;
    }

    public function mapsTo($column_name)
    {
        Configuration::updateValue($this->configuration_name, $column_name);
    }

    public function getMappedField()
    {
        return Configuration::get($this->configuration_name);
    }

    public function isMappingEnabled()
    {
        return $this->getMappedField() != 'mapping_disabled' && $this->getMappedField() != '';
    }

    public function setData($fields = array())
    {
        if (isset($fields['table_name'])) {
            $this->columns = [];
            $this->table_name = $fields['table_name'];
        }

        if (isset($fields['configuration_name'])) {
            $this->configuration_name = $fields['configuration_name'];
        }
    }

    public function addColumn($key, $value) {
        $this->additional_columns[$key] = $value;
    }


    public static function getCustomerDocument(
        AgColumnMapping $cpf_mapping,
        AgColumnMapping $cnpj_mapping,
        AgColumnMapping $social_name_mapping,
        Customer $customer
    ) {
        if ($cnpj_mapping->isMappingEnabled() && $social_name_mapping->isMappingEnabled()) {
            if ($cnpj_mapping->getMappedField() === 'djtalbrazilianregister') {
                $sql = new DbQuery;
                $sql->from('djtalbrazilianregister')
                    ->where('id_customer=' . (int)$customer->id);

                $data = Db::getInstance()->getRow($sql);

                $cnpj    = @$data['cnpj'];
            } elseif ($cnpj_mapping->getMappedField() === 'cpf_cnpj') {
                if (Module::isEnabled('psmodcpf')) {
                    include_once _PS_MODULE_DIR_ . 'psmodcpf/psmodcpf.php';
                    if (!class_exists('psmodcpf')) {
                        return;
                    }
    
                    $mod = new psmodcpf;
                    $version = $mod->version;
    
                    if (version_compare($version, '2.0.6', '>=')) {
                        $column = 'tipo';
                    } else {
                        $column = 'tp_documento';
                    }
                
                    $sql = new DbQuery;
                    $sql->from('modulo_cpf')
                        ->select('documento')
                        ->where('id_customer=' . (int)$customer->id)
                        ->where("{$column}=2");
                    $cnpj = Db::getInstance()->getValue($sql);
                } else {
                    //fkcustomers. Remover futuramente.
                    $sql = new DbQuery;
                    $sql->from('customer')
                        ->where('id_customer=' . (int)$customer->id);

                    $data = Db::getInstance()->getRow($sql);
                    if ($data['tipo'] === 'pj') {
                        $cnpj = @$data['cpf_cnpj'];
                    }
                }
            } elseif ($cnpj_mapping->getMappedField() === 'ldbrazilianregister') {
                $sql = new DbQuery;
                $sql->from('ldbrazilianregister')
                    ->where('id_customer=' . (int)$customer->id);

                $data = Db::getInstance()->getRow($sql);
                $cnpj    = @$data['cnpj'];
            } elseif ($cnpj_mapping->getMappedfield() === 'psmodcpf') {
                $sql = new DbQuery;
                $sql->from('modulo_cpf')
                    ->select('documento')
                    ->where('id_customer=' . (int)$customer->id)
                    ->where('tp_documento="2"');
                $cnpj = Db::getInstance()->getValue($sql);
            }else {
                $cnpj = @$customer->{$cnpj_mapping->getMappedField()};
            }

            $company = @$customer->{$social_name_mapping->getMappedField()};
        }

        if ($cpf_mapping->getMappedField() === 'djtalbrazilianregister') {
            $sql = new DbQuery;
            $sql->from('djtalbrazilianregister')
                ->where('id_customer=' . (int)$customer->id);

            $data = Db::getInstance()->getRow($sql);
            
            $cpf = @$data['cpf'];
            $name = $customer->firstname . ' ' . $customer->lastname;
        } elseif ($cpf_mapping->getMappedField() === 'cpf_cnpj') {
            $sql = new DbQuery;
            $sql->from('customer')
                ->where('id_customer=' . (int)$customer->id);

            $data = Db::getInstance()->getRow($sql);
            if ($data['tipo'] === 'pf') {
                $cpf = @$data['cpf_cnpj'];
            } else {
                $cnpj = @$data['cpf_cnpj'];
            }
        } elseif ($cpf_mapping->getMappedField() === 'ldbrazilianregister') {
            $sql = new DbQuery;
            $sql->from('ldbrazilianregister')
                ->where('id_customer=' . (int)$customer->id);

            $data = Db::getInstance()->getRow($sql);
            
            $cpf = @$data['cpf'];
            $name = $customer->firstname . ' ' . $customer->lastname;
        } elseif ($cpf_mapping->getMappedfield() === 'psmodcpf') {
            if (Module::isEnabled('psmodcpf')) {
                include_once _PS_MODULE_DIR_ . 'psmodcpf/psmodcpf.php';
                if (!class_exists('psmodcpf')) {
                    return;
                }

                $mod = new psmodcpf;
                $version = $mod->version;

                if (version_compare($version, '2.0.6', '>=')) {
                    $column = 'tipo';
                } else {
                    $column = 'tp_documento';
                }
            
                $sql = new DbQuery;
                $sql->from('modulo_cpf')
                    ->select('documento')
                    ->where('id_customer=' . (int)$customer->id)
                    ->where("{$column}=1");
                $cpf = Db::getInstance()->getValue($sql);
            }
        } else {
            $cpf = @$customer->{$cpf_mapping->getMappedField()};
        }

        $name = $customer->firstname . ' ' . $customer->lastname;


        return [
            'cpf'     => $cpf,
            'name'    => $name,
            'cnpj'    => @$cnpj,
            'company' => @$company
        ];
    }
}
