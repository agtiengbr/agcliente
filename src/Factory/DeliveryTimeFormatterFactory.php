<?php
namespace AGTI\Cliente\Factory;

use AGTI\Cliente\Utils\DeliveryTime\BothFormatter;
use AGTI\Cliente\Utils\DeliveryTime\CustomFormatter;
use AGTI\Cliente\Utils\DeliveryTime\DateFormatter;
use AGTI\Cliente\Utils\DeliveryTime\TimeFormatter;
use InvalidArgumentException;

class DeliveryTimeFormatterFactory
{
    public static function createFormatter($mode)
    {
        switch ($mode) {
            case 'date':
                return new DateFormatter;
            case 'time':
                return new TimeFormatter;
            case 'both':
                return new BothFormatter;
            case 'custom':
                return new CustomFormatter;
            default:
                //para retrocompatibilidade
                return new TimeFormatter;
        }
    }
}