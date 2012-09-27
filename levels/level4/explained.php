<?php
    require_once('../../lib/db.php');
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/User/sessionUser.class.php');
    require_once('../../lib/Level/userLevel.class.php');

    $user = new SessionUser( $pdo );
    $level = new UserLevel(4, $user, $pdo );
    $nextLevel = new UserLevel( $level->getLevelId() + 1, $user, $pdo);

    if( !$level->userDoneLevel() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }
        
    $smarty = new Smarty_Verath;
    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;

    
    $src = '
<script src="text/javascript">
    document.write("Javascript challenge");
</script>';

    $smarty->assign('level', $level->getLevelId());
    $smarty->assign('completed', isset($_GET['completed'] ));
    $smarty->assign('src_settings', 'brush: xml highlight: [1]');
    $smarty->assign('vuln_type', 'Exposed password');
    $smarty->assign('vuln_how', 'The password is sent to the client. There is simply no way of 100% securing a password if sent to a client (in the source code).');
    $smarty->assign('vuln_fix', 'Once again. Do NOT store passwords where the client can access them.');
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