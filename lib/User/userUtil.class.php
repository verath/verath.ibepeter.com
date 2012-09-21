<?php
    class UserUtil {

        /**
         * Returns a leaderboard with the top
         * $numResults sorted by score.
         *
         * @param PDO the pdo instance to run the query on.
         * @param int $numResults The number of users to return
         * @return array An array with names and scores for 
         *               the users or an empty array on failure.
         */
        public static function getLeaderboard( $pdo, $numResults ){
            $sql = 'SELECT COUNT(`level`) AS `level`, `username` 
                    FROM `level_stats_completion` 
                    GROUP BY `username` 
                    ORDER BY COUNT(`level`) DESC 
                    LIMIT :numResults';

            $stmt = $pdo->prepare( $sql );
            $stmt->bindParam(':numResults', $numResults, PDO::PARAM_INT );

            if( $stmt->execute() ){
                return $stmt->fetchAll();
            } else {
                return array();
            }
        }

        /**
         * Returns the average number of levels
         * completed per user.
         *
         * @param PDO the pdo instance to run the query on.
         * @return int
         */
        public static function getAverageLevel( $pdo ){
            $sql = 'SELECT ROUND( 
                        SUM(tbl1.count) /
                        (SELECT COUNT(`id`) FROM `users`) 
                    , 1)
                    FROM (
                        SELECT COUNT(`level`) as `count`
                        FROM `level_stats_completion`
                        GROUP BY `username`
                    ) as tbl1';

            $stmt = $pdo->prepare( $sql );
            
            if( $stmt->execute() ) {
                $result = $stmt->fetch(PDO::FETCH_NUM);
                return $result[0];
            } else {
                return 0;
            }
            
        }
    }

?>