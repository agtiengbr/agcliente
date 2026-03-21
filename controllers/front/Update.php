<?php

class agclienteUpdateModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        echo json_encode(['success' => false, 'error' => 'Atualização remota desabilitada.']);
        exit();
    }
}
