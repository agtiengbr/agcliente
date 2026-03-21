<?php
namespace AGTI\Cliente\Mapping;

abstract class DomainMapping extends AbstractMapping
{
    abstract function setValue($value, ...$objects);
    abstract function getValue(...$objects);
}