<?php
    require_once('../../lib/db.php');
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/User/sessionUser.class.php');
    require_once('../../lib/Level/userLevel.class.php');

    $user = new SessionUser( $pdo );
    $level = new UserLevel(1, $user, $pdo );
    $nextLevel = new UserLevel(2, $user, $pdo);

    if( !$level->userDoneLevel() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }
    
    
    $smarty = new Smarty_Verath;
    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;

    
    $src = '
<form action="" method="post">
    <p>Password: <br /><input type="password" name="password" class="title" /></p>
    <!-- This is your password: '.$level->getPassword().' -->
    <p><input type="submit" value="Submit" /></p>
</form>';

    $smarty->assign('level', $level->getLevelId());
    $smarty->assign('completed', isset($_GET['completed']));
    $smarty->assign('src_settings', 'brush: xml highlight: [3]');
    $smarty->assign('vuln_type', 'Exposed password');
    $smarty->assign('vuln_how', 'The password is (as you can see at line 3) is written out in clear-text in the html source.');
    $smarty->assign('vuln_fix', 'Don\'t write passwords in the source code. Ever! Not even in a "hidden"-field.');
    $smarty->assign('src', htmlentities($src) );
    
    $smarty->assign('comments', $level->getComments() );
    $smarty->assign('com_secret', $level->getCommentsSecret() );
    $smarty->assign('com_error', isset($_GET['error']) ? $_GET['error'] : false);

    $smarty->assign('can_access_next_level', $nextLevel->userHasAccess());
    
    
    if(isset($_GET['completed'])){
        $smarty->display('explained.html', $level->getLevelId().'_completed');
    } else {
        $smarty->display('explained.html', $level->getLevelId());
    }
?>