<?php
    require_once('../lib/user.class.php');
    require_once('../lib/smarty_verath.php');
    
    function register(){
        $user = new User();

        // is bot-field filled?
        if( !isset($_POST['email']) || $_POST['email'] !== ''){
            return 'Sorry, no bots allowed at the moment';
        }

        // Do we have all required fields
        if( !isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['password2']) ){
            return 'All fields are required';
        }

        // Get fields
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_confirm = $_POST['password2'];
        
        $register_status = $user->register($username, $password, $password_confirm);

        if( $register_status !== true ){
            return $register_status;
        }

        $user->login($username, $password);

        // Yippi, registered
        header('location: /?reg=true');
    }
    
    if( isset($_POST['submit']) ){
        $error = register();
    }

    $smarty = new Smarty_Verath;
    
    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
    $cache_id = '';

    if( isset($error) ){
       $smarty->assign('error', $error);
       $cache_id .= $error;
    }

    $cache_id = md5($cache_id);

    $smarty->display('register.html', $cache_id);

?>