<?php
    require_once('../../lib/db.php');
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/User/sessionUser.class.php');
    require_once('../../lib/Level/userLevel.class.php');

    
    $user = new SessionUser( $pdo );
    $level = new UserLevel( 7, $user, $pdo );
    
    if( !$level->userHasAccess() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }
    
    function login(){
        global $level;
        if( $_SESSION['lvl7_user_id'] == '55642' && $level->checkPassword($_SESSION['lvl7_pin']) ){
            if( $level->complete() ){
                unset($_SESSION['lvl7_user_id']);
                unset($_SESSION['lvl7_pin']);
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
    if( isset($_SESSION['lvl7_user_id']) && isset($_SESSION['lvl7_pin']) ){
       $error = login();
    }

    $hint = $level->getHints();
    $userDoneLevel = $level->userDoneLevel();
    $levelPass = $level->getPassword();

    $smarty = new Smarty_Verath;
    $smarty->assign('level',  $level->getLevelId());
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
    $cache_id = ($cache_id !== '') ? md5($cache_id) : '';

    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
    $smarty->display(
        $smarty->getTemplateDir('levels') . '/level_7.html', 
        $cache_id
    );

?>