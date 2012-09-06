<?php
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/user.class.php');
    require_once('../../lib/level.class.php');

    $user = new User();
    $level = new Level(3, $user, null, 'REkaOlQ');

    if( !$level->user_done_level() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }
    
        
    $smarty = new Smarty_Verath;
    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
    
    $src = '
 function login(){
    if( $_SERVER[\'HTTP_REFERER\'] !== \'http://nonexistentdomain.domain/\' ){
        return \'We only allow logins from http://nonexistentdomain.domain/\';
    }
    if( $_POST[\'password\'] !== \''.$level->get_password().'\' ){
        return \'Wrong password\';
    }
    return true;
}';

    $smarty->assign('level', $level->get_level());
    $smarty->assign('completed', isset($_GET['completed'] ));
    $smarty->assign('src_settings', 'brush: php highlight: [2]');
    $smarty->assign('vuln_type', 'Trusting client supplied data');
    $smarty->assign('vuln_how', 'Relying on the HTTP Referer header. This should be avoided since the HTTP headers are easy to manipulate (e.g using <a href="https://addons.mozilla.org/en-US/firefox/addon/966/">TamperData</a>).');
    $smarty->assign('vuln_fix', 'There are no real fix for this. It is best to avoid using it.');
    $smarty->assign('src', htmlentities($src) );
    
    $smarty->assign('comments', $level->get_comments() );
    $smarty->assign('com_secret', $level->get_comments_secret() );
    $smarty->assign('com_error', isset($_GET['error']) ? $_GET['error'] : false);
    
    
    if(isset($_GET['completed'])){
        $smarty->display('explained.html', $level->get_level() .'_completed');
    } else {
        $smarty->display('explained.html', $level->get_level());
    }
    
?>