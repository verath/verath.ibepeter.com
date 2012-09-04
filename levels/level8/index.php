<?php
    require_once('../../lib/smarty_verath.php');
    require_once('../../lib/user.class.php');
    require_once('../../lib/level.class.php');
    
    $user = new User();
    $level = new Level(8, $user);
    
    if( !$level->user_has_access() ){
        die('You are not ready. Meet Yoda you must. <a href="/">Home</a>');
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Level 8 - To be created...</title>
        <style>
            /* 
                Both of these are from the blueprint css framework
                http://www.blueprintcss.org/
            */
            @import url("../../css/typography.css");
            @import url("../../css/forms.css"); 
            #error{
                width: 300px;
            }
        </style>
        <script src="../../scripts/main.js"></script>
		
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
        <h1>Level 8</h1>
        <p>This level is not created yet, sorry :(. Congratulations on completing all the levels though!</p>
    </body>
</html>