<?php
namespace AGTI\Cliente\Entity\ServiceResponse;

use AGTI\Cliente\Entity\Address;

class AddressFinder
{
    /** @var Address */
    protected $address;

    /**
     * Get the value of address
     */ 
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set the value of address
     *
     * @return  self
     */ 
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }
}