<?php
    require_once('Smarty/Smarty.class.php');
    require_once('config.php');
    require_once('sensitive.class.php');

    class Smarty_Verath extends Smarty {

        function __construct(){
            
            parent::__construct();

            if( IS_PRODUCTION ){
                $this->setTemplateDir(array(
                    'main' => Sensitive::$SMARTY_PROD_DATA_DIR . '/templates',
                    'levels' => Sensitive::$SMARTY_PROD_DATA_DIR . '/templates/levels'
                ));
                $this->setConfigDir(Sensitive::$SMARTY_PROD_DATA_DIR . '/config');
                $this->setCompileDir(Sensitive::$SMARTY_PROD_DATA_DIR . '/templates_c');
                $this->setCacheDir(Sensitive::$SMARTY_PROD_DATA_DIR . '/cache');

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

            
            if( !IS_PRODUCTION && SHOW_DEBUG ){
                //$this->debugging = true;
            } else {
                $this->debugging = false;
            }
            
            $this->assign('app_name', 'Verath');
        }

    }
?>