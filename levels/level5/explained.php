<?php
    require_once('../../lib/db.php');
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/User/sessionUser.class.php');
    require_once('../../lib/Level/userLevel.class.php');

    $user = new SessionUser( $pdo );
    $level = new UserLevel(5, $user, $pdo );
    $nextLevel = new UserLevel( $level->getLevelId() + 1, $user, $pdo);

    if( !$level->userDoneLevel() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }
        
    $smarty = new Smarty_Verath;
    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;

    
    $src = '
function login(){
    if( strpos( $_SERVER[\'HTTP_USER_AGENT\'], \'Googlebot\' ) !== false ){
        // Google bot need no login...
        return true;
    } else {
        return \'Sorry, but you must logon to see this page.\';
    }
}';

    $smarty->assign('level', $level->getLevelId());
    $smarty->assign('completed', isset($_GET['completed'] ));
    $smarty->assign('src_settings', 'brush: php highlight: [2]');
    $smarty->assign('vuln_type', 'Trusting client supplied data');
    $smarty->assign('vuln_how', 'Trusting the user agent string. Allowing a bot to crawl your page without signing in.');
    $smarty->assign('vuln_fix', 'Always validate the bot by ip (<a href="http://www.google.com/support/webmasters/bin/answer.py?answer=80553">How to validate Googlebot</a>)');
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