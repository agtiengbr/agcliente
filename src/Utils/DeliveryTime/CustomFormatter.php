<?php

namespace AGTI\Cliente\Utils\DeliveryTime;

use AGTI\Cliente\Entity\ServiceArgs\AddWorkingDaysLocal;
use AGTI\Cliente\Interfaces\DeliveryTimeFormatter;
use AGTI\Cliente\Service\AddWorkingDaysLocal as ServiceAddWorkingDaysLocal;


class CustomFormatter implements DeliveryTimeFormatter
{
    public function format($time, $format='')
    {
        $args = new AddWorkingDaysLocal;
        $args->setQtyDays($time);

        $service = new ServiceAddWorkingDaysLocal;
        $r = $service->exec($args);

        $ret = $format;

        $ret = str_replace('{tempo_entrega}', $time, $ret);
        $ret = str_replace('{prazo_entrega}', $r->format('d/m'), $ret);

        return $ret;
    }
}