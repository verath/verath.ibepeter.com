<?php
    require_once('../lib/user.class.php');
    require_once('../lib/level.class.php');

    $user = new User();

    if( $user->is_auth() ){
        require_once('../lib/smarty_verath.php');
        $smarty = new Smarty_Verath;

        // Get done board
        $sql = 'SELECT `username`, `level` FROM `users` 
                WHERE username != \'verath\'
                ORDER BY `level` DESC
                LIMIT 15';

        $stmt = $pdo -> prepare( $sql );
        $stmt -> execute();
        $top20 = $stmt->fetchAll();

        $sql = 'SELECT ROUND(AVG(`level`)) FROM `users`';
        $stmt = $pdo -> prepare( $sql );
        $stmt -> execute();
        $average_level = $stmt->fetch(PDO::FETCH_NUM);
        $average_level = $average_level[0];

        $smarty->assign('leaderboard_users',  $top20);
        $smarty->assign('average_level', $average_level);
        $smarty->assign('total_levels', Level::$num_levels);
        $smarty->assign('name', $user->get_name());

        if( $user->get_level() > 1){
            $smarty->assign('completed_levels', range(1, $user->get_level()-1) );
        }

        if( $user->get_level() <= Level::$num_levels){
            $smarty->assign('next_level', $user->get_level() );
        }

        $smarty->display('index_logged_in.html');
        
    } else {
        $user->logout();
        die();
    }
?>