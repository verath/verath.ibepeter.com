<?php
    require_once('../../lib/db.php');
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/User/sessionUser.class.php');
    require_once('../../lib/Level/userLevel.class.php');

    
    $user = new SessionUser( $pdo );
    $hints = array( 
        3  => 'Look closer.', 
        6 => 'Use the resources tab in the developer tools.'
    );
    $level = new UserLevel( 4, $user, $pdo, $hints );
        
    if( !$level->userHasAccess() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }
    
    function login(){
        global $level;
        if( $level->checkPassword($_GET['password']) ){
            if( $level->complete() ){
                header('location: explained.php?completed');
                die();
            } else {
                return 'Database error, please try again later. Sorry for the inconvenience.';
            }
        } else {
            return 'Wrong password';
        }   
    }
    
    $error = false;
    if( isset($_GET['password']) ){
       $error = login();
    }

    $level->addTry();
    
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
    if( !empty($hint) ){
        $cache_id .= $hint;
    }
    $cache_id = ($cache_id !== '') ? md5($cache_id) : '';

    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
    $smarty->display(
        $smarty->getTemplateDir('levels') . '/level_4.html', 
        $cache_id
    );
?>