<?php
    require_once('/lib/db.php');
    require_once('/lib/User/sessionUser.class.php');
    require_once('/lib/Level/userLevel.class.php');

    function handle_comment(){
        global $pdo;
        $add_to_level = intval($_POST['level']);
        $secret = $_POST['secret'];
        $message = $_POST['message'];
        $user = new SessionUser( $pdo );

        if( isset($_SESSION['spam']) ){
            if( time() < ($_SESSION['spam'] + 10) ) {
                return 'Please wait at least 10 sec before posting another message.';
            }
        }

        if($add_to_level < 1 || $add_to_level > BasicLevel::NUM_LEVELS ){
            return 'Invalid level.';
        }

        $level = new UserLevel($add_to_level, $user, $pdo);

        if( $secret !== $level->getCommentsSecret() ) {
            return 'Unable to identify you.';
        }

        if( !$level->userDoneLevel() ){
            return 'Not allowed.';
        }

        if( strlen($message) < 1 || strlen($message) > 300 ) { 
            return 'Message must be between 1 and 300 chars.';
        }

        if( $level->addComment($message) ){
            $_SESSION['spam'] = time();
            return false;
        } else {
            return 'Database error, please try again later. Sorry for the inconvenience.';
        }
    }


    $error = false;


    if( isset($_POST['secret']) && isset($_POST['level']) && isset($_POST['message']) ){
        $error = handle_comment();
    } else {
        $error = 'Missing info.';
    }


    if( isset($_POST['level']) && !$error ){
        $loc = '/levels/level' . $_POST['level'] . '/explained.php';
    } elseif( isset($_POST['level']) && $error ){
        $loc = '/levels/level' . $_POST['level'] . '/explained.php?error=' . urlencode($error);
    } else {
        $loc = '/';
    }


    header('location: '. $loc);
    die();
?>