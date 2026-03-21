<?php

namespace AGTI\Cliente\Domain\Shipping\Service;

use AgClienteProductZipCode;
use AgMarketplaceSeller;
use Module;

class GetOriginZipcodeByProduct
{
    //nenhum CEP pôde ser determinado e cabe a cada situação particular (ex: config. do módulo de transportadoras) determinar o CEP de origem
    const UNKNOWN_ZIPCODE = -1;

    //nenhum CEP pôde ser determinado e é um estado de erro em que o produto não deve ser postado.
    const NO_ZIPCODE = 0;

    public function exec($idProduct)
    {
        $zipcode = AgClienteProductZipCode::getZipcodeByProduct($idProduct);
        if ($zipcode) {
            return $zipcode;
        }

        if (Module::isEnabled('agmarketplace')) {
            require_once _PS_MODULE_DIR_ . 'agmarketplace/classes/AgMarketplaceProduct.php';
            $seller = AgMarketplaceSeller::getSellerByProduct($idProduct);
            //produto do seller principal
            if (!$seller) {
                return self::UNKNOWN_ZIPCODE;
            }

            $seller = new AgMarketplaceSeller($seller);
            if (!$seller->postcode) {
                //ignora o produto se o seller não tiver o CEP de origem configurado
                return self::NO_ZIPCODE;
            }
            
            $zipcode = $seller->postcode;
            return $zipcode;
        }

        return self::UNKNOWN_ZIPCODE;
    }
}