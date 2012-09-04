<?php
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/user.class.php');
    require_once('../../lib/level.class.php');

    $user = new User();
    $level = new Level(6, $user);

    if( !$level->user_done_level() ){
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

    $smarty -> assign('level', $level->get_level());
    $smarty -> assign('completed', isset($_GET['completed'] ));
    $smarty -> assign('src_settings', 'brush: php highlight: [2]');
    $smarty -> assign('vuln_type', 'Trusting client supplied data');
    $smarty -> assign('vuln_how', 'Trusting a single cookie to validate a user. It is vulnerable since even cookies are saved at the client, where they can be edited pretty easy.');
    $smarty -> assign('vuln_fix', 'Use sessions or add a control cookie (hashed username + other).');
    $smarty -> assign('src', htmlentities($src) );
    
    $smarty -> assign('comments', $level->get_comments() );
    $smarty -> assign('com_secret', $level->get_comments_secret() );
    $smarty -> assign('com_error', isset($_GET['error']) ? $_GET['error'] : false);
    
    if(isset($_GET['completed'])){
        $smarty->display('explained.html', $level->get_level() .'_completed');
    } else {
        $smarty->display('explained.html', $level->get_level());
    }
    
?>