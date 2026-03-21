<?php

namespace AGTI\Cliente\Presenter;

class Tabs
{
    /** @var Tab */
    protected $tabs = [];

    

    /**
     * Get the value of tabs
     */ 
    public function getTabs()
    {
        return $this->tabs;
    }

    /**
     * Set the value of tabs
     *
     * @return  self
     */ 
    public function addTab(Tab $tab)
    {
        $this->tabs[] = $tab;

        return $this;
    }

    public function render()
    {
        $module = new \agcliente;
        \Context::getContext()->smarty->assign(['tabs' => $this->getTabs()]);
        $html = $module->display(_PS_MODULE_DIR_ . $module->name, 'includes/tabs.tpl');

        return $html;
    }
}