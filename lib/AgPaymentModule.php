<?php

require_once _PS_MODULE_DIR_ . 'agcliente/lib/AgClienteModuleTrait.php';
class AgPaymentModule extends PaymentModule
{
    use AgClienteModuleTrait;
}
