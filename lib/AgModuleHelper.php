<?php
class AgModuleHelper
{
    public static function getTabsTokens($tabs)
    {
        $return = [];
        foreach ($tabs as $tab) {
            $return[$tab['className']] = Tools::getAdminTokenLite($tab['className']);

            if (isset($tab['childs']) && is_array($tab['childs'])) {
                $childs = self::getTabsTokens($tab['childs']);
                $return = array_merge($return, $childs);
            }
        }

        return $return;
    }
}