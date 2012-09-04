<?php
    require_once('../../lib/user.class.php');
    require_once('../../lib/level.class.php');
    
    $user = new User();
    $level = new Level(7, $user);
    
    if( !$level->user_has_access() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }

	if(isset($_POST['link'])){   
		$link = preg_replace('/\s/', '', $_POST['link']);
		$regex =  '#\?ref=.*?"/?>.*?<script.*?>.*?document.cookie.*?</script>#i';
		$regex2 = '#\?ref=.*?"/?>.*?<script.*?src.*?></script>#i';

		if( preg_match($regex, $link) || preg_match($regex2, $link) ){
			$alert = 'alert("You got one!\ndocument.cookie: user_id=55642; pass='.$level->get_password().'");';
		} else {
			$error = 'You can not see submited links if you are not logged in';
		}
	}
?>
<html>
<head>
	<title>Share a link</title>
	<link href="main.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			var error = '<?php if(isset($error)){ echo $error; } ?>';
			if( error != '' ){
				$("#error").html(error).slideDown();
			} 
		});
		<?php if( isset($alert) ){echo $alert;} ?>
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
		<span class="title">Share a link</span>
		<div id="error"></div>
		<form action="" method="post" id="mainForm">

			<label for="link">Link: </label>
			<input type="text" name="link" id="link" />   

			<input type="submit" id="linkSubmit" value="Submit" />

		</form>
		<p style="text-align: right; float: left; width: 400px; padding-left: 50px; padding-right: 50px;"><a href="login.php?ref=share.php">Login</a></p>
	</div>
</body>
</html>