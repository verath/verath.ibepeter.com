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
                    ORDER BY COUNT(`level`) DESC, `username`
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

        /**
         * Validates a password.
         * 
         * @param string $password
         * @param string $password_confirm 
         * @return mixed true on success, else an error message
         */
        public static function validatePassword($password, $password_confirm){
            if( $password !== $password_confirm ){
                return 'The passwords didn\'t match';
            }
            if( strlen($password) < 4 ){
                return 'Password must be > 4 chars';
            }
            return true;
        }

        /**
         * Validates a username.
         * 
         * @param PDO the pdo instance to run the query on.
         * @param string $username
         * @return mixed true on success, else an error message
         */
        public static function validateUsername( $pdo, $username ){
            if( strlen($username) > 30 ){
                return 'Username must be <= 30 chars';
            }
            if( strlen($username) < 1 ){
                return 'Username must be >= 1 char';
            }
            if( !preg_match('/^[a-z0-9_]+$/i', $username) ){
                return 'Username must only contain a-Z, 0-9 and _';
            }

            $sql = 'SELECT 1 
                    FROM `users` 
                    WHERE `username` = :username';

            $stmt = $pdo->prepare( $sql );
            $stmt->bindParam( ':username', $username, PDO::PARAM_STR );

            if( !$stmt->execute() ){
                return 'DB error, please try again later';
            }
            if( $stmt->rowCount() !== 0 ){
                return 'That username already exist';
            }
            return true;
        }
    }

?>