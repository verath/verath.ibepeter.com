<?php
	require_once('lib/db.php');
    require_once('lib/User/sessionUser.class.php');

	$user = new SessionUser( $pdo );
	$user->logout();

	header('location: /')
?>