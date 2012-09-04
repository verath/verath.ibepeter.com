<?php
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/user.class.php');
    require_once('../../lib/level.class.php');

    $user = new User();
    $level = new Level(4, $user);

    if( !$level->user_done_level() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }
        
    $smarty = new Smarty_Verath;
    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;

    
    $src = '
<script src="text/javascript">
    document.write("Javascript challenge");
</script>';

    $smarty -> assign('level', $level->get_level());
    $smarty -> assign('completed', isset($_GET['completed'] ));
    $smarty -> assign('src_settings', 'brush: xml highlight: [1]');
    $smarty -> assign('vuln_type', 'Exposed password');
    $smarty -> assign('vuln_how', 'The password is sent to the client. There is simply no way of 100% securing a password if sent to a client (in the source code).');
    $smarty -> assign('vuln_fix', 'Once again. Do NOT store passwords where the client can access them.');
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