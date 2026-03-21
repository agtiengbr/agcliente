<?php

namespace AGTI\Cliente\Service;

use AGTI\Cliente\Entity\ServiceArgs\AddWorkingDaysLocal as ServiceArgsAddWorkingDaysLocal;
use AGTI\Cliente\Entity\ServiceArgs\GetHolidaysLocal;
use AGTI\Cliente\Entity\ServiceResponse\AddWorkingDaysLocal as ServiceResponseAddWorkingDaysLocal;
use AGTI\Cliente\Service\GetHolidaysLocal as ServiceGetHolidaysLocal;

class AddWorkingDaysLocal extends LocalService
{
    public function exec(ServiceArgsAddWorkingDaysLocal $args)
    {
        $args2 = new GetHolidaysLocal;
        $service = new ServiceGetHolidaysLocal;

        $r = $service->exec($args2);
        $holidays = $r->getHolidays();

        $begin = new \DateTime(date('Y-m-d 00:00:00'));
        $it    = new \DateInterval('P1D');

        $working_days = $args->getQtyDays();
        while($working_days) {
            $begin->add($it);
            $what_day = $begin->format('N');

            $is_weekend = $what_day > 5;
            if ($is_weekend) {
                continue;
            }

            $is_holiday = false;
            foreach ($holidays as $holiday) {
                if ($begin == $holiday->getDate()) {
                    $is_holiday = true;
                    break;
                }
            }

            if ($is_holiday) {
                continue;
            }
            
            $working_days--;
        }

        $r = new ServiceResponseAddWorkingDaysLocal;
        $r->setDate($begin);

        return $begin;
    }
}