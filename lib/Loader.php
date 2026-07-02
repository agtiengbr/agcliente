<?php

class AgLoader
{
	public static function loadDir($dirname)
    {
        if (!empty($dirname)) {
            if (!is_dir($dirname)) {
                return;
            }

            if (substr($dirname, -7) == '/vendor') {
                return;
            }

            if (substr($dirname, -7) == '/tests') {
                return;
            }

            //a pasta "upgrade" não pode ser incluída, ou o PrestaShop não permitirá que os scripts de update
            //dos módulos sejam executados.
            if (substr($dirname, -8) == '/upgrade') {
                return;
            }

            //ignora a pasta de overrides
            if (substr($dirname, -9) == '/override') {
                return;
            }

            //ignora a pasta de views
            if (substr($dirname, -6) == '/views') {
                return;
            }


            //ignora a pasta de translations
            if (substr($dirname, -13) == '/translations') {
                return;
            }
            
            $classes = scandir($dirname);

            if (is_array($classes)) {
                foreach ($classes as $class) {
                    if (str_ends_with($class, '.php') && $class !== 'index.php') {
                        require_once $dirname . '/' . $class;
                    } elseif (is_dir($dirname . '/' . $class) && $class !== '.' && $class !== '..') {
                        self::loadDir($dirname . '/' . $class);
                    }
                }
            }
        }
    }
}
