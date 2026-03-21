<?php

namespace AGTI\Cliente\Utils\DeliveryTime;

use AGTI\Cliente\Entity\ServiceArgs\AddWorkingDaysLocal;
use AGTI\Cliente\Interfaces\DeliveryTimeFormatter;
use AGTI\Cliente\Service\AddWorkingDaysLocal as ServiceAddWorkingDaysLocal;

class DateFormatter implements DeliveryTimeFormatter
{
    public function format($time)
    {
        $args = new AddWorkingDaysLocal;
        $args->setQtyDays($time);

        $service = new ServiceAddWorkingDaysLocal;
        $r = $service->exec($args);

        return "Até " . $r->format('d/m');
    }
}