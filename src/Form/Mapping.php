<?php

namespace AGTI\Cliente\Form;

use AGTI\Cliente\Mapping\AbstractMapping;

class Mapping extends Form
{
    protected $panels = [];
    protected $submitButton = 'mapping';

    public function addPanel($legend, ...$mappings)
    {
        $panel = [];

        foreach ($mappings as $mapping) {
            $panel[] = $mapping;
        }

        $this->panels[] = [
            'legend' => $legend,
            'mappings' => $panel
        ];
    }
        
    public function renderHtml()
    {
        $forms = [];

        foreach ($this->panels as $panel) {
            $inputs = [];

            /** @var AbstractMapping */
            foreach ($panel['mappings'] as $mapping) {
                $inputs[] = [
                    'label' => $mapping->getLabelForSelect(),
                    'name' => $mapping->getConfigName(),
                    'type' => $mapping->isMultiple() ? 'swap' : 'select',
                    'options' => [
                        'id' => 'id',
                        'name' => 'name',
                        'query' => $mapping->getOptionsForSelect()
                    ]
                ];
            }

            $forms[] = [
                'form' => [
                    'legend' => ['title' => $panel['legend']],
                    'input' => $inputs,
                    'submit' => ['title' => 'Salvar', 'name' => $this->submitButton]
                ]
            ];
        }

        $helperForm = $this->getHelperForm();
        $this->fillForm($helperForm);
        return $helperForm->generateForm($forms);
    }

    public function fillForm($form)
    {
        foreach ($this->panels as $panel) {
            /** @var AbstractMapping */
            foreach ($panel['mappings'] as $mapping) {
                $form->fields_value[$mapping->getConfigName()] = $mapping->getMappedValue();
            }
        }
    }

    public function postProcess()
    {
        if (\Tools::isSubmit($this->submitButton)) {
            $this->persistData();
        }
    }

    protected function persistData()
    {
        foreach ($this->panels as $panel) {
            /** @var AbstractMapping */
            foreach ($panel['mappings'] as $mapping) {
                if ($mapping->isMultiple()) {
                    $mapping->mapsTo(\Tools::getValue($mapping->getConfigName() . '_selected'));
                } else {
                    $mapping->mapsTo(\Tools::getValue($mapping->getConfigName()));
                }
            }
        }
    }
}