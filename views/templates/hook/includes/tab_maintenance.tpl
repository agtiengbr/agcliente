<form class='form-horizontal'>
    <div class='panel' id="tab_maintenance">
        <div class='alert alert-warning'>Essa aba não deve ser utilizada se você não souber exatamente o que está
            fazendo, pois pode provocar perda de informações ou problemas de configuração no módulo.</div>

        <p>
            <button type="button" target='_blank' class='btn btn-default update_database'>Atualizar Banco de
                Dados</button><small> (vai
                criar
                as
                tabelas/colunas/índices faltantes no BD)</small>
        </p>
        <p>
            <button type="button" target='_blank' class='btn btn-default clean_module_db'>Limpar Banco de
                dados</button><small> (vai apagar
                todos os dados do módulo do BD)</small>
        </p>
        <p>
            <button type="button" target='_blank' class='btn btn-default delete_tables'>Excluir tabelas do Banco de
                Dados</button><small> (somente as tabelas vinculadas ao módulo serão excluídas)</small>
        </p>
        <p>
            <button type="button" target='_blank' class='btn btn-default reset_configs'>Resetar
                configurações</button><small> (vai restaurar
                todas as configurações do módulo)</small>
        </p>
        <p>
            <button type="button" target='_blank' class='btn btn-default remake_menus'>Recriar menus</button><small>
                (deleta e recria todas
                as abas do backoffice)</small>
        </p>
        <p>
            <button type="button" target='_blank' class='btn btn-default remake_workers'>Recriar
                Workers</button><small> (remove e recria
                todas as workers do módulo)</small>
        </p>

        <p>
            <button type="button" target='_blank' class='btn btn-default reset_hooks'>Redefinir Hooks</button><small> (desinsere e reinsere o módulo em todos os hooks padrão)</small>
        </p>
    </div>
</form>