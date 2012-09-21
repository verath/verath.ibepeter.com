<?php
    require_once('../lib/db.php');
    require_once('../lib/User/sessionUser.class.php');
    require_once('../lib/User/userUtil.class.php');
    require_once('../lib/smarty_verath.php');
    
    function register(){
        global $pdo;

        $user = new SessionUser( $pdo );

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
        
        // Validate
        $passValidStatus = UserUtil::validatePassword( $password, $password_confirm );
        if( $passValidStatus !== true ) {
            return $passValidStatus;
        }

        $nameValidStatus = UserUtil::validateUsername( $pdo, $username );
        if( $nameValidStatus !== true ) {
            return $nameValidStatus;
        }

        // Insert
        if( !$user->register($username, $password) ){
            return 'Database error, please try again later. Sorry for the inconvenience.';
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