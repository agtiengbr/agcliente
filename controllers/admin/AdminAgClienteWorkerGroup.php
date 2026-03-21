<?php

class AdminAgClienteWorkerGroupController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap  = true;
        $this->table      = 'agworker_group';
        $this->identifier = 'id_agworker_group';
        $this->className  = 'AgClienteWorkerGroup';
        $this->list_no_link = true;

        parent::__construct();

        $this->fields_list = [
            'id_agworker_group' => [
                'title' => 'ID',
                'class' => 'fixed-width-xs'
            ]
        ];
    }
}