<?php

namespace AGTI\Cliente\Mapping;

interface MappingInterface
{
    /**
     * Retorna um array com todas as possibilidades de mapeamento
     */
    public function getAvailableOptions();

    /**
     * Recebe como argumento uma lista de valores/objetos e retorna um valor após a aplicação do mapeamento
     */
    public function getValue(...$objects);

    /**
     * Retorna uma string com um nome "human-friendly" para ser apresentado ao cliente na escolha do mapeamento. Exemplo: "Mapeamento de CPF".
     */
    public function getLabelForSelect();

    /**
     * Se for mapeamento múltiplo usa um input do tipo swap
     */
    public function isMultiple();
}