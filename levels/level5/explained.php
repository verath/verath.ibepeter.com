<?php
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/user.class.php');
    require_once('../../lib/level.class.php');

    $user = new User();
    $level = new Level(5, $user);

    if( !$level->user_done_level() ){
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

    $smarty -> assign('level', $level->get_level());
    $smarty -> assign('completed', isset($_GET['completed'] ));
    $smarty -> assign('src_settings', 'brush: php highlight: [2]');
    $smarty -> assign('vuln_type', 'Trusting client supplied data');
    $smarty -> assign('vuln_how', 'Trusting the user agent string. Allowing a bot to crawl your page without signing in.');
    $smarty -> assign('vuln_fix', 'Always validate the bot by ip (<a href="http://www.google.com/support/webmasters/bin/answer.py?answer=80553">How to validate Googlebot</a>)');
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