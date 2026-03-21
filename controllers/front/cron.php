<?php
class agclientecronModuleFrontController extends ModuleFrontController
{
    protected $models = array();

    private function doCurlRequestAsync($url, $post_data = array())
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

    public function initContent()
    {
        if (function_exists("set_time_limit")) {
            set_time_limit(0);
        }
        
        ignore_user_abort(true);

        $this->updateConfig('AGCLIENTE_CRON_WATCHDOG', time());

        if (file_exists(_PS_MODULE_DIR_ . 'agmelhorenvio/classes/AgMelhorCache.php')) {
            unlink(_PS_MODULE_DIR_ . 'agmelhorenvio/classes/AgMelhorCache.php');
        }

        if (Module::isInstalled('agcorreios')&& Module::isEnabled('agcorreios')) {
            $url = $this->context->link->getModuleLink('agcorreios', 'createintervals');
            $this->doCurlRequestAsync($url);
        }

        if (Module::isInstalled('agcorreios')&& Module::isEnabled('agcorreios')) {
            $url = $this->context->link->getModuleLink('agcorrios', 'CalcPrices');
            $this->doCurlRequestAsync($url);
        }

        if (Module::isInstalled('agmelhorenvio') && Module::isEnabled('agmelhorenvio')) {
            if (Configuration::get('agmelhorenvio_track_labels_next_time') < time() || Tools::getValue('agmelhorenvio_track_labels')) {
                $url = $this->context->link->getModuleLink('agmelhorenvio', 'TrackLabels');
                $this->doCurlRequestAsync($url);
                Configuration::updateValue('agmelhorenvio_track_labels_next_time', time() + 60 * 60);
            }

            $time_clear = (int) Configuration::get('AGMELHORENVIO_CONFIGURATION_TIMEOUT_CLEAR_REQUESTS') * 86400;
            $next_clear = (int) Configuration::get('agmelhorenvio_timeout_clear_requests_next_time');

            if ($next_clear <= time() && $time_clear > 0) {
                AgClienteCleanner::cleanRequests('agmelhorenvio', $next_clear);

                Configuration::updateValue('agmelhorenvio_timeout_clear_requests_next_time', time() + $time_clear);
            }
        }

        if (Module::isInstalled('agzipcodezones') && Module::isEnabled('agzipcodezones')) {
            if (Configuration::get('agzipcodezones_update_orders_zones') < time() || Tools::getValue('agzipcodezones_update_orders_zones')) {
                $url = $this->context->link->getModuleLink('agzipcodezones', 'UpdateZones');
                $this->doCurlRequestAsync($url);
                Configuration::updateValue('agzipcodezones_update_orders_zones', time() + 24*60*60);
            }
        }


        if (Module::isInstalled('agformmakeralerts') && Module::isEnabled('agformmakeralerts')) {
            if (Configuration::get('agformmakeralerts_next_alert') < time() || Tools::getValue('agformmakeralerts_force_alert')) {
                $url = $this->context->link->getModuleLink('agformmakeralerts', 'SendMails');
                $this->doCurlRequestAsync($url);
                Configuration::updateValue('agformmakeralerts_next_alert', time() + 15*60);
            }
        }


        if (Module::isInstalled('agyapay') && Module::isEnabled('agyapay')) {
            if (Configuration::get('agyapay_send_tracking_number_next') < time() || Tools::getValue('agyapay_force_tracking_number')) {
                $url = $this->context->link->getModuleLink('agyapay', 'sendTrackingNumber');
                $this->doCurlRequestAsync($url);
                Configuration::updateValue('agyapay_send_tracking_number_next', time() + 60*60);
            }
        }
        
        if (Module::isInstalled('agyapay') && Module::isEnabled('agyapay')) {
            $url = $this->context->link->getModuleLink('agyapay', 'return', ['process_next' => 1]);
            $this->doCurlRequestAsync($url);
        }

        //pagseguro
        if (Module::isInstalled('agpagseguro') && Module::isEnabled('agpagseguro')) {
            $url = $this->context->link->getModuleLink('agpagseguro', 'webhook', ['process_next' => 1]);
            $r = $this->doCurlRequestAsync($url);

            //  limpeza da tabela de requisições do pagseguro
            $time_clear = (int) Configuration::get('AGPAGSEGURO_CONFIGURATION_TIMEOUT_CLEAR_REQUESTS') * 86400;
            $next_clear = (int) Configuration::get('agpagseguro_timeout_clear_requests_next_time');

            if ($next_clear <= time() && $time_clear > 0) {
                AgClienteCleanner::cleanRequests('agpagseguro', $time_clear);

                Configuration::updateValue('agpagseguro_timeout_clear_requests_next_time', time() + $next_clear);
            }
        }


        //agmoipmarketplace
        if (Module::isInstalled('agmoipmarketplace') && Module::isEnabled('agmoipmarketplace')) {
            $url = $this->context->link->getModuleLink('agmoipmarketplace', 'webhook', ['proccess_next' => 1]);
            $this->doCurlRequestAsync($url);
        }

        echo json_encode([
            'success' => 1
        ]);
        die();
    }

    public function getConfig($name)
    {
        $sql = new DbQuery;
        $sql->from('configuration')
            ->select('value')
            ->where('name="' . pSQL($name) . '"')
            ->where('id_shop=' . (int)$this->context->shop->id)
            ->where('id_shop_group=' . (int)$this->context->shop->id_shop_group);

        return Db::getInstance()->getValue($sql);
    }

    public function updateConfig($name, $value)
    {
        if ($this->getConfig($name) !== false) {
            return Db::getInstance()->update(
                'configuration',
                [
                    'value' => pSQL($value),
                    'date_upd' => date('Y-m-d H:i:s')
                ],
                'name="' . pSQL($name) . '" AND id_shop=' . (int)$this->context->shop->id . ' AND id_shop_group=' . (int)$this->context->shop->id_shop_group
            );
        }

        return Db::getInstance()->insert('configuration', [
            'name' => $name,
            'value' => pSQL($value),
            'id_shop' => (int)$this->context->shop->id,
            'id_shop_group' => (int)$this->context->shop->id_shop_group,
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s')
        ]);
    }
}
