<?php
    require_once('../../lib/db.php');
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/User/sessionUser.class.php');
    require_once('../../lib/Level/userLevel.class.php');

    $user = new SessionUser( $pdo );
    $level = new UserLevel(6, $user, $pdo );
    $nextLevel = new UserLevel( $level->getLevelId() + 1, $user, $pdo);

    if( !$level->userDoneLevel() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }

        
    $smarty = new Smarty_Verath;
    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;

    
    $src = '
function login(){
    if( strtolower($_COOKIE[\'name\']) == \'verath\' ){
        return true;
    } else {
        return \'Sorry, but you must logon to see this page.\';
    }
}';

    $smarty->assign('level', $level->getLevelId());
    $smarty->assign('completed', isset($_GET['completed'] ));
    $smarty->assign('src_settings', 'brush: php highlight: [2]');
    $smarty->assign('vuln_type', 'Trusting client supplied data');
    $smarty->assign('vuln_how', 'Trusting a single cookie to validate a user. It is vulnerable since even cookies are saved at the client, where they can be edited pretty easy.');
    $smarty->assign('vuln_fix', 'Use sessions or add a control cookie (hashed username + other).');
    $smarty->assign('src', htmlentities($src) );
    
    $smarty->assign('comments', $level->getComments() );
    $smarty->assign('com_secret', $level->getCommentsSecret() );
    $smarty->assign('com_error', isset($_GET['error']) ? $_GET['error'] : false);
    
    $smarty->assign('can_access_next_level', $nextLevel->userHasAccess());

    if(isset($_GET['completed'])){
        $smarty->display('explained.html', $level->getLevelId() .'_completed');
    } else {
        $smarty->display('explained.html', $level->getLevelId());
    }
    
?>