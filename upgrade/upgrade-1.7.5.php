<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_7_5($module)
{
    //reinstala os overrides
    $files = [
        'configuration.tpl',
        'ps-alert.tpl',
        'ps-form.tpl',
        'ps-panel.tpl',
        'ps-table.tpl',
        'ps-tabs.tpl',
        'ps-tags.tpl'
    ];

    foreach ($files as $file) {
        unlink(_PS_MODULE_DIR_ . 'agcliente/views/templates/admin/' . $file);
    }

    return true;
}
