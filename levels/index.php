<?php
    require_once('../lib/db.php');
    require_once('../lib/User/sessionUser.class.php');
    require_once('../lib/User/userUtil.class.php');
    require_once('../lib/Level/userLevel.class.php');

    $user = new SessionUser( $pdo );

    if( $user->auth() ){
        require_once('../lib/smarty_verath.php');
        $smarty = new Smarty_Verath;

        $leaderboard = UserUtil::getLeaderboard( $pdo, 20 );
        $average_level = UserUtil::getAverageLevel( $pdo );

        $levelStatus = array();
        $levelsCompleted = 0;
        for( $i=1; $i <= BasicLevel::NUM_LEVELS; $i++ ) {
            if($user->hasDoneLevel($i)) {
                $levelsCompleted ++;
            }
            array_push($levelStatus, array(
                'completed' => $user->hasDoneLevel($i),
                'id' => $i,
                'hasAccess' => $user->hasAccessToLevel($i)
            ));
        }

        $smarty->assign('leaderboard_users',  $leaderboard);
        $smarty->assign('average_level', $average_level);
        $smarty->assign('total_levels', BasicLevel::NUM_LEVELS);
        $smarty->assign('levels_status', $levelStatus);
        $smarty->assign('levels_completed', $levelsCompleted);
        $smarty->assign('name', $user->getName());

        $smarty->display('index_logged_in.html');
        
    } else {
        $user->logout();
        header('location: /');
        die();
    }
?>