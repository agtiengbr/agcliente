<?php

namespace AGTI\Cliente\Entity\ServiceArgs;

class AddressFinder
{
    protected $postcode;
    protected $useCache;

    /**
     * Get the value of postcode
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Set the value of postcode
     *
     * @return  self
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get the value of useCache
     */ 
    public function getUseCache()
    {
        return $this->useCache;
    }

    /**
     * Set the value of useCache
     *
     * @return  self
     */ 
    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;

        return $this;
    }
}
