<?php

namespace AGTI\Cliente\Service;

use AGTI\Cliente\Entity\Holiday;
use AGTI\Cliente\Entity\ServiceArgs\GetHolidaysLocal as ServiceArgsGetHolidaysLocal;
use AGTI\Cliente\Entity\ServiceResponse\GetHolidaysLocal as ServiceResponseGetHolidaysLocal;

class GetHolidaysLocal extends LocalService
{
    public function exec(ServiceArgsGetHolidaysLocal $args)
    {
        $year = $args->getYear();

        if ($year === null) {
            $year = intval(date('Y'));
        }
        
        $holidays = array(
            // Tatas Fixas dos feriados Nacionail Basileiras
            (new Holiday)->setDate(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, 1,  1,   $year))))->setName('Confraternização Universal - Ano Novo'), // Confraternização Universal - Lei nº 662, de 06/04/49
            (new Holiday)->setDate(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, 4,  21,  $year))))->setName('Tiradentes'), // Tiradentes - Lei nº 662, de 06/04/49
            (new Holiday)->setDate(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, 5,  1,   $year))))->setName('Dia do Trabalhador'), // Dia do Trabalhador - Lei nº 662, de 06/04/49
            (new Holiday)->setDate(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, 9,  7,   $year))))->setName('Dia da Independência'), // Dia da Independência - Lei nº 662, de 06/04/49
            (new Holiday)->setDate(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, 10,  12, $year))))->setName('Nossa Senhora Aparecida'), // N. S. Aparecida - Lei nº 6802, de 30/06/80
            (new Holiday)->setDate(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, 11,  2,  $year))))->setName('Todos os Santos'), // Todos os santos - Lei nº 662, de 06/04/49
            (new Holiday)->setDate(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, 11, 15,  $year))))->setName('Proclamação da República'), // Proclamação da republica - Lei nº 662, de 06/04/49
            (new Holiday)->setDate(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, 12, 25,  $year))))->setName('Natal'), // Natal - Lei nº 662, de 06/04/49
        );

        if (function_exists('easter_date')) {
            $easter = easter_date($year); // Limite de 1970 ou após 2037 da easter_date PHP consulta http://www.php.net/manual/pt_BR/function.easter-date.php
            $easter_day   = date('j', $easter);
            $easter_month = date('n', $easter);
            $easter_year  = date('Y', $easter);
            // These days have a date depending on easter
            $holidays[] = (new Holiday)->setDate(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, $easter_month, $easter_day - 48,  $easter_year))))->setName('Segunda-feira de Carnaval'); //2ºferia Carnaval
            $holidays[] = (new Holiday)->setDate(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, $easter_month, $easter_day - 47,  $easter_year))))->setName('Terça-feira de Carnaval');//3ºferia Carnaval 
            $holidays[] = (new Holiday)->setDate(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, $easter_month, $easter_day - 2 ,  $easter_year))))->setName('Sexta-feira Santa');//6ºfeira Santa  
            $holidays[] = (new Holiday)->setDate(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, $easter_month, $easter_day     ,  $easter_year))))->setName('Páscoa');//Pascoa
            $holidays[] = (new Holiday)->setDate(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, $easter_month, $easter_day + 60,  $easter_year))))->setName('Corpus Christ');//Corpus Cirist
        }

        sort($holidays);

        $return = new ServiceResponseGetHolidaysLocal;
        foreach ($holidays as $holiday) {
            $return->setHoliday($holiday);
        }

        return $return;
    }
}