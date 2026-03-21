<?php

namespace AGTI\Cliente\Interfaces;

interface DeliveryTimeFormatter
{
    public function format($delay);
}