<?php
    require_once('../../lib/user.class.php');
    require_once('../../lib/level.class.php');
    
    $user = new User();
    $level = new Level(7, $user);
    
    if( !$level->user_has_access() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }

	function login(){
        global $level;
        
        if( $_POST['user_id'] == '55642' && $level->check_password($_POST['pass']) ){
        	$_SESSION['lvl7_user_id'] = $_POST['user_id'];
			$_SESSION['lvl7_pin'] = $_POST['pass'];
			
			header('location: index.php');
			die();
        } else {
        	return 'Wrong user ID and/or password!';
        }
    }
    
    $error = false;
    if( isset($_POST['user_id']) ){
    	$error = login();
    }
    
	$ref = '';
	if( isset($_GET['ref']) ){
		$ref = $_GET['ref'];
	}

?>
<html>
<head>
	<title>Login</title>
	<link href="main.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			var error = '<?php if( isset($error) && $error ){ echo $error; } ?>';
			if( error != '' ){
				$("#error").html(error).slideDown();
			}
		});
	</script>
	
		<!-- Google Analytics -->
		<script type="text/javascript">
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-20402876-2']);
			_gaq.push(['_trackPageview']);
			(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script>
	</head>
<body>
	<div id="container">
	<span class="title">Login</span>
	<div id="error"></div>
	<form action="" method="post" id="mainForm">

		<div class="mainFormInputElement">
			<label for="user_id">User ID: </label>
			<input type="text" name="user_id" id="user_id" autocomplete="off" />
		</div>     
		<div class="mainFormInputElement">
			<label for="pass">Password: </label>
			<input type="password" name="pass" id="pass" autocomplete="off" />
		</div>

		<input type="submit" value="Log in" />

		<input type="hidden" name="ref" value="<?php echo $ref; ?>" />

	</form>
	<p style="text-align: right; float: left; width: 400px; padding-left: 50px; padding-right: 50px;"><a href="share.php">Share a link</a></p>
	</div>
</body>
</html>