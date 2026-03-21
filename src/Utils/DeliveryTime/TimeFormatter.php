<?php

namespace AGTI\Cliente\Utils\DeliveryTime;

use AGTI\Cliente\Interfaces\DeliveryTimeFormatter;

class TimeFormatter implements DeliveryTimeFormatter
{
    public function format($time)
    {
        return "Até $time dias úteis.";
    }
}