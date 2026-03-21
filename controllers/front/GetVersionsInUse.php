<?php

class AgClienteGetVersionsInUseModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        echo json_encode(['success' => false, 'error' => 'Endpoint desabilitado.']);
        exit();
    }
}
