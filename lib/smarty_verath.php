<?php
    require_once('Smarty/Smarty.class.php');
    require_once('config.php');
    require_once('sensitive.class.php');

    class Smarty_Verath extends Smarty {

        function __construct(){
            
            parent::__construct();

            if( IS_PRODUCTION ){
                $this->setTemplateDir(array(
                    'main' => Sensitive::$smarty_prod_data_dir . '/templates',
                    'levels' => Sensitive::$smarty_prod_data_dir . '/templates/levels'
                ));
                $this->setConfigDir(Sensitive::$smarty_prod_data_dir . '/config');
                $this->setCompileDir(Sensitive::$smarty_prod_data_dir . '/templates_c');
                $this->setCacheDir(Sensitive::$smarty_prod_data_dir . '/cache');

                $this->caching = Smarty::CACHING_OFF;
                $this->force_compile = false;
                $this->compile_check = false;

            } else {
                $this->setTemplateDir(array(
                    'main' => 'C:/wamp/www/templates',
                    'levels' => 'C:/wamp/www/templates/levels'
                ));
                $this->setConfigDir('C:/wamp/www/data/smarty/config');
                $this->setCompileDir('C:/wamp/www/data/smarty/templates_c');
                $this->setCacheDir('C:/wamp/www/data/smarty/cache');

                $this->caching = Smarty::CACHING_OFF;
            }
            
            $this->assign('app_name', 'Verath');
        }

    }
?>