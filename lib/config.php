<?php
    if( isset($_SERVER['IS_ON_LOCAL']) && $_SERVER['IS_ON_LOCAL'] === 'TRUE' ){
        // On local machine, SetEnv varname "variable value" in apache config.
        $DEBUG = true;
        $PRODUCTION = false;
    } else {
        $DEBUG = false;
        $PRODUCTION = true;
    }

    if( $DEBUG ) {
        // Full error reporting
        error_reporting(-1);
    } else {
        // Turn off error reporting
        error_reporting(0);
    }

    // Set the session cookie to HTTPOnly (_should_ not be changeable by javascript)
    $sessionParams = session_get_cookie_params(); 
    session_set_cookie_params( 
        $sessionParams["lifetime"],
        $sessionParams["path"],
        $sessionParams["domain"],
        $sessionParams["secure"], 
        true
    );
    unset($sessionParams);

    session_start();

?>