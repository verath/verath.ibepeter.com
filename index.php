<?php
    require_once('lib/user.class.php');

    $user = new User();
    $login_error = '';

    if( isset($_POST['submit']) ){
        if( isset($_POST['username']) && isset($_POST['password']) ){
            $login_status = $user->login($_POST['username'], $_POST['password']);
            
            if( $login_status == false ){
                $login_error = 'You fail at typing! (wrong name and/or password)';
            } 
        } else {
            $login_error = 'Not all fields filled.';
        }
    }

    

    if( $user->is_auth() ){
        Header( "HTTP/1.1 303 See Other" ); 
        Header( "Location: /levels" ); 
        die();
    } else {
        // Not logged in
        
        require_once('lib/smarty_verath.php');
        $smarty = new Smarty_Verath;
        
        $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
        $cache_id = '';

        if($login_error !== ''){
           $smarty->assign('error', $login_error);
           $cache_id .= 'login_error';
        }

        if( isset($_GET['forced_logout']) ){
            $smarty->assign('forced_logout', true);
            $cache_id .= '_forceLogout';
        }

        if(isset($_GET['reg'])){
            $smarty->assign('did_register', true);
            $cache_id .= '_reg';
        }

        $smarty->display('index_not_logged_in.html', $cache_id);
    }
?>