<?php
    require_once('../../lib/db.php');
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/User/sessionUser.class.php');
    require_once('../../lib/Level/userLevel.class.php');

    $user = new SessionUser( $pdo );
    $level = new UserLevel(7, $user, $pdo );
    $nextLevel = new UserLevel( $level->getLevelId() + 1, $user, $pdo);

    if( !$level->userDoneLevel() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }
        
    $smarty = new Smarty_Verath;
    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;

    
    $src = '
<form action="" method="post" id="mainForm">
	<div class="mainFormInputElement">
		<label for="user_id">User ID: </label>
		<input type="text" name="user_id" id="user_id" />
	</div>     
	<div class="mainFormInputElement">
		<label for="pin">PIN code: </label>
		<input type="password" name="pin" id="pin" />
	</div>
	<input type="submit" value="Log in" />
	<input type="hidden" name="ref" value="<?=$_GET[\'ref\']?>" />
</form>';

    $smarty->assign('level', $level->getLevelId());
    $smarty->assign('completed', isset($_GET['completed'] ));
    $smarty->assign('src_settings', 'brush: php highlight: [11]');
    $smarty->assign('vuln_type', 'Trusting client supplied data');
    $smarty->assign('vuln_how', 'Not validating a variable that can be modified by the user to run scripts crafted by the user.');
    $smarty->assign('vuln_fix', 'Always use <a href="http://www.php.net/manual/en/function.filter-input.php">filter_input</a> (for PHP) on any variables that could have been modified by the user.');
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