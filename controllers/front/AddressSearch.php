<?php

class AgClienteAddressSearchModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
        $postcode = Tools::getValue('postcode');
        $options = $this->module->getOptions();
        
        $address = AddressFinder::findByPostcode($postcode,$options['config']['cep_provider']);
		echo json_encode($address);
		
		exit();
	}
}