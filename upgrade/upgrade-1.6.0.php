<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_6_0($module)
{
	$has_menvios  = file_exists(_PS_MODULE_DIR_ . 'agmelhorenvio/agmelhorenvio.php');
	$has_correios = file_exists(_PS_MODULE_DIR_ . 'agcorreios/agcorreios.php');

	if ($has_menvios) {
		$display_images   = Configuration::get('AGMELHORENVIO_DISPLAY_IMAGES');
		$simulate_product = 1;
		$simulate_cart = 1;
	} elseif ($has_correios) {
		$simulate_product = Configuration::get('AGCORREIOS_SIMULATE_PRODUCT');
		$simulate_cart    = Configuration::get('AGCORREIOS_SIMULATE_CART');
		$display_images = 0;
	} else {
		$simulate_product = 0;
		$simulate_cart    = 0;
		$display_images = 0;
	}

	Configuration::updateValue('AGTI_SIMULATION_CART', $simulate_cart);
	Configuration::updateValue('AGTI_SIMULATION_PRODUCT', $simulate_product);
	Configuration::updateValue('AGTI_SIMULATION_DISPLAY_IMAGES', $display_images);

	$module->installOverrides();

	$module->registerHook(array(
        'displayHeader',
        'displayBackOfficeHeader',
        'displayProductButtons',
        'displayLeftColumnProduct',
        'displayProductAdditionalInfo',
        'displayShoppingCartFooter',
        'dashboardZoneTwo'
    ));

    //reinstala os overrides
    if (Module::isInstalled('agcliente')) {
        $module->registerHook('dashboardZoneTwo');
    }
    
    return true;
}
