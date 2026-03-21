<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_6_34($module)
{
    $free_shipping_texts = [];
    foreach (Language::getLanguages() as $language) {
        if (!Configuration::get('AGTI_SIMULATION_FREE_SHIPPING_TEXT', $language['id_lang'])) {
            switch($language['language_code']) {
                case 'pt-br':
                case 'pt-pt':
                    $free_shipping_texts[$language['id_lang']] = 'Frete Grátis';
                    break;

                default:
                    $free_shipping_texts[$language['id_lang']] = 'Free Shipping';
                    break;
            }
        }
    }
    return true;
}
