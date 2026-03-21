<?php

namespace AGTI\Cliente\Entity\ServiceArgs;

class AddWorkingDaysLocal
{
    protected $qtyDays;

    /**
     * Get the value of qtyDays
     */ 
    public function getQtyDays()
    {
        return $this->qtyDays;
    }

    /**
     * Set the value of qtyDays
     *
     * @return  self
     */ 
    public function setQtyDays($qtyDays)
    {
        $this->qtyDays = $qtyDays;

        return $this;
    }
}