<?php

class AdminAgClienteZipcodeController extends ModuleAdminController
{
    public function initContent()
	{
        if (Tools::getIsSet('searchByZone')) {
			$zone = Tools::getValue('zone');
			$ret = AddressFinder::findByZone($zone);

			echo json_encode($ret);
			exit();
		} elseif (Tools::getIsSet('searchByUf')) {
			$uf = Tools::getValue('uf');
			$ret = AddressFinder::findByUf($uf);

			echo json_encode($ret);
			exit();
		} elseif (Tools::getIsSet('searchCityByName')) {
			$name = Tools::getValue('name');
			$uf = Tools::getValue('uf');

			$ret = AddressFinder::findCitiesByName($name, $uf);

			echo json_encode($ret);
			exit();
		} elseif (Tools::getIsSet('searchByCityAndUf')) {
			$uf = Tools::getValue('uf');
			$city = Tools::getValue('city');

			$ret = AddressFinder::findByUfAndCity($uf, $city);

			echo json_encode($ret);
			exit();
		} elseif (Tools::getIsSet('searchNeighborhoodByName')) {
			$uf = Tools::getValue('uf');
			$city = Tools::getValue('city');
			$neighborhood = Tools::getValue('neighborhood');

			$ret = AddressFinder::findNeighborhoodByName($uf, $city, $neighborhood);

			echo json_encode($ret);
			exit();
		} elseif (Tools::getIsSet('searchByNeighborhood')) {
			$uf = Tools::getValue('uf');
			$city = Tools::getValue('city');
			$neighborhood = Tools::getValue('neighborhood');

			$ret = AddressFinder::findIntervalByNeighborhood($uf, $city, $neighborhood);

			echo json_encode($ret);
			exit();
		}

        http_response_code(404);
        exit();
    }
}