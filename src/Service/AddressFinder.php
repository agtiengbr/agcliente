<?php

namespace AGTI\Cliente\Service;

use AgClienteAddressCache;
use AGTI\Cliente\Entity\Address;
use AGTI\Cliente\Entity\ServiceArgs\AddressFinder as ServiceArgsAddressFinder;
use AGTI\Cliente\Entity\ServiceResponse\AddressFinder as ServiceResponseAddressFinder;
use AGTI\Cliente\Exception\AddressNotFoundException;
use AGTI\Cliente\Exception\CurlNotFoundException;
use AGTI\Cliente\Entity\ServiceResponse\AddressFinder as EntityServiceResponseAddressFinder;
use AGTI\Cliente\Service\Cache\AddressCache;

class AddressFinder extends Service
{
    public function buildUrl()
    {
        return "https://enderecos.agti.eng.br/" . $this->getEndpoint();
    }

    public function getEndpoint()
    {
        return 'postcode/';
    }

    /** @return EntityServiceResponseAddressFinder */
    public function exec(ServiceArgsAddressFinder $args)
    {
        $response = new ServiceResponseAddressFinder;
        $cache = new AddressCache();

        if ($args->getUseCache()) {
            $cache_address = $cache->Get($args->getPostcode());
            if ($cache_address != null) {
                $response->setAddress($cache_address);
                return $response;
            }
        }

        $url = $this->buildUrl() . $args->getPostcode();

        try {
            $r = $this->doRequest('GET', $url);
        } catch (CurlNotFoundException $e) {
            throw new AddressNotFoundException("O CEP {$args->getPostcode()} não foi encontrado.");
        }

        $decoded = json_decode($r);

        $address = new Address();
        $address
            ->setStreet($decoded->type . ' ' . $decoded->address)
            ->setPostcode($decoded->postcode)
            ->setNeighborhood($decoded->neighborhood)
            ->setCity($decoded->city)
            ->setState($decoded->state);

        if ($args->getUseCache()) {
            if (!\Validate::isLoadedObject($cache->Save($args->getPostcode(), $address))) {
                return 'Ocorreu um problema ao salvar a consulta';
            }
        }

        $response->setAddress($address);

        return $response;
    }
}
