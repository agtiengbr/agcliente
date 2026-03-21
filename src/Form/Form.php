<?php
namespace AGTI\Cliente\Form;

use AdminController;
use HelperForm;
use Module;
use Tools;

abstract class Form
{
    protected $submitButton;
    protected $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    protected function getHelperForm()
    {
        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module          = $this->module;
        // $helper->name_controller = $this->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex . '&configure=' . $this->module->name;

        // title and Toolbar
        $helper->title          = $this->module->displayName;
        $helper->show_toolbar   = true; // false -> remove toolbar
        $helper->toolbar_scroll = true; // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action  = $this->submitButton;
        $helper->toolbar_btn    = array(
            'save' => array(
                'desc' => "Salvar",
                'href' => AdminController::$currentIndex . '&configure=' . $this->module->name . '&save' . $this->module->name .
                '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => "Voltar",
            ),
        );

        return $helper;
    }

    abstract public function renderHtml();
    abstract public function postProcess();
    abstract protected function persistData();
}