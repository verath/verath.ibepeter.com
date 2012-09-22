<?php
    require_once('databaseUser.class.php');

    class SessionUser extends DatabaseUser {
        private $pdo;

        /**
         * Keys that needs to be in every session for 
         * the session to be valid.
         */
        private $SESSION_KEYS_USED = array(
            'userId',
            'userIp'
        );

         /** 
         * SessionUser constructor. Sets default values and
         * the pdo instance to use. Also starts the session if
         * it is not started already.
         * 
         * @param PDO $pdo The PDO instance to use for DB queries.
         */
        public function __construct( $pdo ) {
            parent::__construct( $pdo );

            $this->pdo = $pdo;

            if( ! isset($_SESSION) ) {
                session_start();
            }
        }

        /**
         * Regenerates the user's session_id to prevent session
         * hijacking. The value is then stored in the database
         * for comparison later.
         * 
         * @return bool true if success, else false
         */
        private function regenerateSessionId(){
            if( !$this->isAuth() ){ return false; }

            $sql = 'UPDATE `users` 
                    SET `session_id` = :session_id 
                    WHERE `id` = :user_id 
                    LIMIT 1';

            session_regenerate_id();
            $sessionId = session_id();
            $userId = $this->getId();

            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT );
            $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);

            return $stmt->execute();
        }

        /**
         * Compares a user's IP to the one stored in the session
         * cookie. This is to prevent others from hijacking.
         *
         * @return bool If the IPs match.
         */
        private function checkIp(){
            if( !$this->isAuth() ){ return false; }
            return ( $_SESSION['userIp'] == $_SERVER['REMOTE_ADDR'] );
        }

        /**
         * Compares a user's session ID to the one stored
         * in the database. This is done to prevent multiple
         * logins to the same account, and also to make sure
         * every session is terminated if the user loggs out.
         *
         * @return bool True if session IDs match.
         */
        private function checkSessionId(){
            if( !$this->isAuth() ){ return false; }

            $sql = 'SELECT 1 FROM `users`
                    WHERE `id` = :user_id
                    AND `session_id` = :session_id
                    LIMIT 1';

            $userId = $this->getId();
            $sessionId = session_id();

            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT );
            $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);

            return ($stmt->execute() && $stmt->rowCount() == 1 );
        }

        /**
         * Attempts to log a user in with the provided user name 
         * and password. If the login is successful the id is stored
         * in the session variable, and the sessionId is regenerated
         * to prevent XSS.
         * 
         * @return bool True if the user was logged in.
         */
        public function login( $name, $password ) {
            if( parent::login($name, $password) ) {
                $_SESSION['userId'] = $this->getId();
                // Save the ip of the user in the session, anti-xss
                $_SESSION['userIp'] = $_SERVER['REMOTE_ADDR'];
                return $this->regenerateSessionId();
            } else {
                return false;
            }
        }

        /**
         * Attempts to authenticate a user by looking at the userId
         * saved from the login. Also fetches data for the user from
         * the DB if the authentication was successful.
         *
         * @return bool True if the user could be validated and data
         *              was fetched.
         */
        public function auth(){
            if( !isset($_SESSION) || !isset($_SESSION['userId']) ) {
                return false;
            } elseif( !parent::fetchFromDB($_SESSION['userId']) ){
                return false;
            } else {
                return ( $this->checkIp() && $this->checkSessionId() );
            }
        }

        /**
         * Checks to see if the user is authenticated.
         *
         * @return bool True if the user is authenticated.
         */
        public function isAuth( ) {
            if( $this->getName() == null || $this->getId() == -1 ) {
                return false;
            } elseif( isset($_SESSION) ) {
                // Make sure we have all the required session values.
                foreach( $this->SESSION_KEYS_USED as $key ){
                    if( ! array_key_exists($key, $_SESSION) ){
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        }

        /**
         * Logs a user out. Removes the session from both the
         * client and the DB and un-sets all values attached to
         * the current user instance.
         *
         * @return bool True if logout was successful.
         */
        public function logout( ) {
            if( ini_get("session.use_cookies") ) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            parent::logout();
            $_SESSION = array();
            session_destroy();
        }
    }
?>
