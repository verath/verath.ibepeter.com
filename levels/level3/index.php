<?php
   require_once('../../lib/db.php');
   require_once('../../lib/smarty_verath.php');
   require_once('../../lib/User/sessionUser.class.php');
   require_once('../../lib/Level/userLevel.class.php');

    $user = new SessionUser( $pdo );
    $hints = array( 
        3 => 'Headers.', 
        6 => 'The HTTP header you need to change can be hidden in HTML5 with rel="noreferrer".',
        9 => 'One way to change the header is using the addon TamperData for Firefox.'
    );
    $level = new UserLevel( 3, $user, $pdo, $hints, 'REkaOlQ' );
    
    if( !$level->userHasAccess() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }

    function login(){
        global $level;
        if( strpos($_SERVER['HTTP_REFERER'], 'nonexistentdomain.domain') === false ){
            return 'We only allow logins from http://nonexistentdomain.domain/';
        }

        if( !$level->checkPassword($_POST['password']) ){
            return 'Wrong password';
        }

        if( $level->complete() ){
            header('location: explained.php?completed'); 
            die();
        } else {
            return 'Database error, please try again later. Sorry for the inconvenience.';
        }
    }
    
    $error = false;
    if( isset($_POST['password']) ){
        $error = login();
        
        if( !empty($_POST['password']) ){
            $level->addTry();
        }
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
    if( !empty($hint) ){
        $cache_id .= $hint;
    }
    $cache_id = ($cache_id !== '') ? md5($cache_id) : '';

    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
    $smarty->display(
        $smarty->getTemplateDir('levels') . '/level_3.html', 
        $cache_id
    );
?>