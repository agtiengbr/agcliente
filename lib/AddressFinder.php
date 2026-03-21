<?php

use AGTI\Cliente\Service\Cache\AddressCache;
use AGTI\DNE\Exception\NotFoundException;
use AGTI\DNE\Service\AddressFinder as ServiceAddressFinder;

class AddressFinder
{
    private static function doCurlRequest($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        $bom = pack("CCC", 0xef, 0xbb, 0xbf);
        if (0 === strncmp($result, $bom, 3)) {
            $result = substr($result, 3);
        }

        return $result;
    }

    private static function isJson($string)
    {
        $decoded = json_decode($string);
        return is_array($decoded) || is_object($decoded);
    }

    public static function findByZone($zone)
    {
        $url = "https://enderecos.agti.eng.br/intervals/{$zone}";
        $resp = self::doCurlRequest($url);

        if (!self::isJson($resp)) {
            return false;
        }

        $resp = json_decode($resp);
        return $resp;
    }

    public static function findByUf($uf)
    {
        $url = "https://enderecos.agti.eng.br/intervals/{$uf}";
        $resp = self::doCurlRequest($url);

        if (!self::isJson($resp)) {
            return false;
        }

        $resp = json_decode($resp);
        return $resp;
    }

    public static function findByUfAndCity($uf, $city)
    {
        $city = self::removeAccents($city);
        $city = rawurlencode($city);


        $url = "https://enderecos.agti.eng.br/intervals/{$uf}/$city";
        $resp = self::doCurlRequest($url);

        if (!self::isJson($resp)) {
            return false;
        }

        $resp = json_decode($resp);
        return $resp;
    }

    public static function findCitiesByName($name, $uf='')
    {
        $name = self::removeAccents($name);
        $name = rawurlencode($name);
        
        $url = "https://enderecos.agti.eng.br/search/city/{$name}";
        if ($uf){
            $url .= "?uf={$uf}";
        }
        
        $resp = self::doCurlRequest($url);

        if (!self::isJson($resp)) {
            return false;
        }

        $resp = json_decode($resp);
        return $resp;
    }

    public static function findNeighborhoodByName($uf, $city, $neighborhood)
    {
        $city = self::removeAccents($city);
        $neighborhood = self::removeAccents($neighborhood);

        $city = rawurlencode($city);
        $neighborhood = rawurlencode($neighborhood);

        $url = "https://enderecos.agti.eng.br/search/neighborhood/$uf/$city/{$neighborhood}";
        
        $resp = self::doCurlRequest($url);

        if (!self::isJson($resp)) {
            return false;
        }

        $resp = json_decode($resp);
        return $resp;
    }

    public static function findIntervalByNeighborhood($uf, $city, $neighborhood)
    {
        $city = self::removeAccents($city);
        $neighborhood = self::removeAccents($neighborhood);

        $city = rawurlencode($city);
        $neighborhood = rawurlencode($neighborhood);
        
        $url = "https://enderecos.agti.eng.br/intervals/$uf/$city/{$neighborhood}";
        $resp = self::doCurlRequest($url);

        if (!self::isJson($resp)) {
            return false;
        }

        $resp = json_decode($resp);
        return $resp;
    }

    public static function findByPostcode($postcode, $provider = null)
    {
        if (Module::isEnabled('agdne')) {
            $ret = self::findDne($postcode);
        }

        if (!@$ret || !@$ret->city) {
            $cache_addres = new AddressCache($postcode);

            if (!empty((array) $cache_addres)) {
                return $cache_addres;
            }

            $address = self::findAgti($postcode);

            if (!is_null($address)) {
                $cache_addres->Save($postcode, $address);
            }

            return $address;
        }
        return $ret;
    }



    public static function findAgti($postcode)
    {
        $url = "https://enderecos.agti.eng.br/postcode/{$postcode}";
        $resp = self::doCurlRequest($url);

        if (!self::isJson($resp)) {
            return false;
        }

        $resp = json_decode($resp);
        $return = new StdClass;

        $return->street = $resp->type . ' ' . $resp->address;
        $return->district = $resp->neighborhood;
        $return->city = $resp->city;
        $return->state = $resp->state;

        return $return;
    }

    public static function findDne($postcode)
    {
        //carrega dependências
        require_once _PS_MODULE_DIR_ . 'agdne/agdne.php';
        $mod = new agdne;

        $service = new ServiceAddressFinder;
        try {
            $r = $service->exec($postcode);
            $return = new StdClass;

            $return->street = $r->getStreet();
            $return->district = $r->getNeighborhood();
            $return->city = $r->getCity();
            $return->state = $r->getState();

            return $return;
        } catch (NotFoundException $e) {
            return false;
        }
    }

    
    public static function findRepublicaVirtual($postcode)
    {
        $url = "http://cep.republicavirtual.com.br/web_cep.php?cep=".$postcode."&formato=json";
        $resp = self::doCurlRequest($url);

        if (!self::isJson($resp)) {
            return false;
        }

        $resp = json_decode($resp);
        $return = new StdClass;
        
        if ($resp->resultado_txt === 'sucesso - cep completo') {
                $return->address = $resp->tipo_logradouro . ' ' . $resp->logradouro;
        } elseif ($resp->resultado_txt === 'sucesso - cep único') {
                $return->address = '';
        }

        $return->district = $resp->bairro;
        $return->city = $resp->cidade;
        $return->state = $resp->uf;        

        return $return;
    }


    public static function findViaCep($postcode)
    {
        $url = "https://viacep.com.br/ws/{$postcode}/json/";
        $resp = self::doCurlRequest($url);
        if (!self::isJson($resp)) {
            return false;
        }

        $resp = json_decode($resp);
        $return = new StdClass;
        
        $return->address = $resp->logradouro;
        $return->district = $resp->bairro;
        $return->city = $resp->localidade;
        $return->state = $resp->uf;        

        return $return;
    }


    private static function removeAccents($string) {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;
    
        $chars = array(
        // Decompositions for Latin-1 Supplement
        chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
        chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
        chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
        chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
        chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
        chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
        chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
        chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
        chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
        chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
        chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
        chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
        chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
        chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
        chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
        chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
        chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
        chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
        chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
        chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
        chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
        chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
        chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
        chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
        chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
        chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
        chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
        chr(195).chr(191) => 'y',
        // Decompositions for Latin Extended-A
        chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
        chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
        chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
        chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
        chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
        chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
        chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
        chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
        chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
        chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
        chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
        chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
        chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
        chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
        chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
        chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
        chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
        chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
        chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
        chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
        chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
        chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
        chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
        chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
        chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
        chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
        chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
        chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
        chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
        chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
        chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
        chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
        chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
        chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
        chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
        chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
        chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
        chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
        chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
        chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
        chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
        chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
        chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
        chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
        chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
        chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
        chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
        chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
        chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
        chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
        chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
        chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
        chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
        chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
        chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
        chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
        chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
        chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
        chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
        chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
        chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
        chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
        chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
        chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
        );
    
        $string = strtr($string, $chars);
    
        return $string;
    }
}
