<?php

require_once _PS_MODULE_DIR_ . 'agcliente/lib/AgClienteModuleTrait.php';
class AgCarrierModule extends CarrierModule
{
    use AgClienteModuleTrait;

    public function getOrderShippingCost($params,$shipping_cost){}
    public function getOrderShippingCostExternal($params){}
}
