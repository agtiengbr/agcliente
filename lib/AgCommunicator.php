<?php

class AgCommunicator
{
    public static $ip_agti = [];
    
    public static function getShopFullUrl()
    {
        $url_instance = \ShopUrl::getShopUrls(Configuration::get('PS_SHOP_DEFAULT'))->where('main', '=', 1)->getFirst();
        return $url_instance->domain . $url_instance->physical_uri . $url_instance->virtual_uri;
    }

    public static function isJson($string)
    {
        $decoded = json_decode($string);
        return is_array($decoded) || is_object($decoded);
    }

    public static function sendShopData($params = [])
    {
        // Comunicação externa removida
    }

    public static function authRemote($license, $module_name, $module_version)
    {
        return true;
    }

    /**
     * Requisição cURL genérica (mantida para uso pelo AddressFinder).
     */
    public static function doCurlRequest($url, $post_data = array(), $params = array(), $parse_json=true, $headers=[])
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);

        if (!empty($post_data)) {
            curl_setopt($ch, CURLOPT_POST, count($post_data));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        }

        $result = curl_exec($ch);
        curl_close($ch);

        //REMOVE CARACTERES BOM
        $bom = pack("CCC", 0xef, 0xbb, 0xbf);
        if (0 === strncmp($result, $bom, 3)) {
            $result = substr($result, 3);
        }

        if (!$parse_json) {
            return $result;
        }

        if (!self::isJson($result)) {
            throw new AgCommunicatorInvalidResponseException("Response {$result} from URL {$url} is not a valid JSON");
        }

        $result = json_decode($result);

        if (!isset($result->success) || !$result->success) {
            if (isset($result->error_msg) && $result->error_msg) {
                throw new AgCommunicatorResponseWithErrorException($result->error_msg);
            } else {
                throw new AgCommunicatorResponseWithErrorException('The server returned an error');
            }
        }

        if (isset($result->response)) {
            return $result->response;
        }
    }

    public static function doCurlRequestAsync($url, $post_data = array())
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 500);

        if (!empty($post_data)) {
            curl_setopt($ch, CURLOPT_POST, count($post_data));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        }
        curl_exec($ch);
        curl_close($ch);
    }

    public static function getRecentVersions($modules_list)
    {
        return '{}';
    }

    public static function downloadModule($module_name, $version_number)
    {
        throw new Exception('Download de módulos remotos desabilitado.');
    }

    public static function getRemoteModules()
    {
        return [];
    }

    public static function expiresRemoteModulesCache()
    {
        Cache::clean(get_called_class() . '*');
    }

    public static function sendReview($data)
    {
        // Comunicação externa removida
    }
}
