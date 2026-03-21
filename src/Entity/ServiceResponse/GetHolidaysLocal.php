<?php

namespace AGTI\Cliente\Entity\ServiceResponse;

use AGTI\Cliente\Entity\Holiday;

class GetHolidaysLocal
{
    /** @var Holiday[] */
    protected $holidays = [];

    /**
     * Get the value of holidays
     */ 
    public function getHolidays()
    {
        return $this->holidays;
    }

    /**
     * Set the value of holidays
     *
     * @return  self
     */ 
    public function setHoliday($holidays)
    {
        $this->holidays[] = $holidays;

        return $this;
    }
}