<?php
    require_once('config.php');
    require_once('sensitive.class.php');

    global $pdo;

    if( IS_PRODUCTION ){
        $dsn = Sensitive::$DB_PROD_DSN;
        $user = Sensitive::$DB_PROD_USER;
        $password = Sensitive::$DB_PROD_PASSWORD;
    } else {
        $dsn = 'mysql:dbname=verath.ibepeter.com;host=localhost';
        $user = 'root';
        $password = '';
    }

    try {
        $pdo = new PDO($dsn, $user, $password, array(
            PDO::ATTR_PERSISTENT => true
        ));
    } catch (PDOException $e) {
        header('Location: /errors/500');
        die();
    }
    
    $pdo->exec('SET CHARACTER SET utf8');

    if( SHOW_DEBUG ){
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    } else {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }
       
?>