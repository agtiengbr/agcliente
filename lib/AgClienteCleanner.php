<?php

class AgClienteCleanner
{
    public static function cleanRequests($module, $next_clear)
    {
        $table = self::existsTable($module);
        $next_clear = $next_clear > 0 ? date('Y-m-d H:i:s', $next_clear) : date('Y-m-d H:i:s');
        AgClienteLogger::addLog("agcliente - Iniciando a limpeza das requisições da tabela {$table}", '1', '', '', '', true);

        if (empty($table)) {
            AgClienteLogger::addLog("agcliente - A tabela de requisições do módulo {$module} não foi localizada", '3', 404, '', '', true);

            return false;
        }

        sleep(1);

        $length = strlen(_DB_PREFIX_);
        $table = substr($table, $length);

        try {
            Db::getInstance()->delete(
                $table,
                '`date_add` <= "' . $next_clear . '"'
            );

            $requests_deleted = Db::getInstance()->Affected_Rows();

            AgClienteLogger::addLog("agcliente - Limpeza das requisições da tabela {$table} concluida - {$requests_deleted} linhas deletadas", '1', '', '', '', true);
        } catch (Exception $ex) {
            AgClienteLogger::addLog('agcliente - Ocorreu um erro ao tentar limpar a tabela ' . $table . ' - ' . $ex->getMessage(), '3', $ex->getCode(), '', '', true);

            return false;
        }

        return true;
    }

    private static function existsTable($module)
    {
        $sql = "SELECT 
                    table_name
                FROM information_schema.tables
                WHERE table_type = 'BASE TABLE'
                    AND table_name = '" . _DB_PREFIX_ . "{$module}_request'
                ";

        $resp = Db::getInstance()->getValue($sql);

        return $resp;
    }
}
