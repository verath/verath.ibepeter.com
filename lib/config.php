<?php

    define('SHOW_DEBUG', false);
    define('IS_PRODUCTION', true);

    if( isset($_SERVER['IS_ON_LOCAL']) && $_SERVER['IS_ON_LOCAL'] === 'TRUE' ){
        // On local machine, SetEnv varname "variable value" in apache config.
        define('SHOW_DEBUG', true);
        define('IS_PRODUCTION', false);
    }

    if( SHOW_DEBUG ) {
        // Full error reporting
        error_reporting(-1);
        ini_set("display_errors", 1);

    } else {
        // Turn off error displaying, log instead
        error_reporting(-1);
        ini_set("display_errors", 0);
        ini_set("log_errors", 1);

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