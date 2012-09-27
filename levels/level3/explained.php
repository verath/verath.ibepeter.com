<?php
    require_once('../../lib/db.php');
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/User/sessionUser.class.php');
    require_once('../../lib/Level/userLevel.class.php');

    $user = new SessionUser( $pdo );
    $level = new UserLevel(3, $user, $pdo, null, 'REkaOlQ');
    $nextLevel = new UserLevel( $level->getLevelId() + 1, $user, $pdo);

    if( !$level->userDoneLevel() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }
        
    $smarty = new Smarty_Verath;
    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
    
    $src = '
 function login(){
    if( $_SERVER[\'HTTP_REFERER\'] !== \'http://nonexistentdomain.domain/\' ){
        return \'We only allow logins from http://nonexistentdomain.domain/\';
    }
    if( $_POST[\'password\'] !== \''.$level->getPassword().'\' ){
        return \'Wrong password\';
    }
    return true;
}';

    $smarty->assign('level', $level->getLevelId());
    $smarty->assign('completed', isset($_GET['completed'] ));
    $smarty->assign('src_settings', 'brush: php highlight: [2]');
    $smarty->assign('vuln_type', 'Trusting client supplied data');
    $smarty->assign('vuln_how', 'Relying on the HTTP Referer header. This should be avoided since the HTTP headers are easy to manipulate (e.g using <a href="https://addons.mozilla.org/en-US/firefox/addon/966/">TamperData</a>).');
    $smarty->assign('vuln_fix', 'There are no real fix for this. It is best to avoid using it.');
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