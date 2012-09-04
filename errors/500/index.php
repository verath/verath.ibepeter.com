<?php
    require_once('../../lib/config.php');
    require_once('../../lib/smarty_verath.php');

    $smarty = new Smarty_Verath;
	$smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
	
	$smarty->display(
	    '500.html'
	);
?>