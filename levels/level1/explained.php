<?php
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/user.class.php');
    require_once('../../lib/level.class.php');

    $user = new User();
    $level = new Level(1, $user);

    if( !$level->user_done_level() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }
    
    
    $smarty = new Smarty_Verath;
    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;

    
    $src = '
<form action="" method="post">
    <p>Password: <br /><input type="password" name="password" class="title" /></p>
    <!-- This is your password: '.$level->get_password().' -->
    <p><input type="submit" value="Submit" /></p>
</form>';

    $smarty->assign('level', $level->get_level());
    $smarty->assign('completed', isset($_GET['completed']));
    $smarty->assign('src_settings', 'brush: xml highlight: [3]');
    $smarty->assign('vuln_type', 'Exposed password');
    $smarty->assign('vuln_how', 'The password is (as you can see at line 3) is written out in clear-text in the html source.');
    $smarty->assign('vuln_fix', 'Don\'t write passwords in the source code. Ever! Not even in a "hidden"-field.');
    $smarty->assign('src', htmlentities($src) );
    
    $smarty->assign('comments', $level->get_comments() );
    $smarty->assign('com_secret', $level->get_comments_secret() );
    $smarty->assign('com_error', isset($_GET['error']) ? $_GET['error'] : false);
    
    
    if(isset($_GET['completed'])){
        $smarty->display('explained.html', $level->get_level().'_completed');
    } else {
        $smarty->display('explained.html', $level->get_level());
    }
?>