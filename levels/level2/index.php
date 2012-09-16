<?php
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/user.class.php');
    require_once('../../lib/level.class.php');
    
    $user = new User();
    $hints = array( 
        2  => 'It is not actually "encrypted".', 
        5 => 'The algorithm is know for using equals sign (=) as padding.',
        9 => 'The encoding is commonly used to encode binary data that need to be stored and transferred.'
    );
    $level = new Level(2, $user, $hints);
    
    if( !$level->user_has_access() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }
    
    function login(){
        global $level;
        if( $level->check_password($_POST['password']) ){
            if( $level->complete() ){
                header('location: explained.php?completed');
                die();
            } else {
                return 'Database error, please try again later. Sorry for the inconvenience.';
            }
        } else {            
            return 'Password incorrect!';
        }
    }

    $error = false;
    if( isset($_POST['password']) ){
       $error = login();

       if( !empty($_POST['password']) ){
            $level->add_try();
        }
    }

    
    $hint = $level->get_hints();
    $userDoneLevel = $level->user_done_level();
    $levelPass = base64_encode( $level->get_password() );

    $smarty = new Smarty_Verath;
    $smarty->assign('level',  $level->get_level());
    $smarty->assign('hint',  $hint);
    $smarty->assign('error',  $error);
    $smarty->assign('userDoneLevel',  $userDoneLevel);
    $smarty->assign('levelPass',  $levelPass);

    $cache_id = '';
    if($error){
        $cache_id .= $error;
    }
    if($userDoneLevel){
        $cache_id .= '_DoneLevel';
    }
    if( !empty($hint) ){
        $cache_id .= $hint;
    }
    $cache_id = ($cache_id !== '') ? md5($cache_id) : '';

    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
    $smarty->display(
        $smarty->getTemplateDir('levels') . '/level_2.html', 
        $cache_id
    );
?>