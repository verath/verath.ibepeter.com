<?php
    require_once('databaseUser.class.php');

    class SessionUser extends DatabaseUser {
        private $pdo;

        private $SESSION_KEYS_USED = array(
            'userId',
            'userIp'
        );

        public function __construct( $pdo ) {
            parent::__construct( $pdo );

            $this->pdo = $pdo;

            if( ! isset($_SESSION) ) {
                session_start();
            }
        }

        /**
         * Regenerates the user's session_id to prevent session
         * hijacking.
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

        private function checkIp(){
            if( !$this->isAuth() ){ return false; }
            return ( $_SESSION['userIp'] == $_SERVER['REMOTE_ADDR'] );
        }

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

        public function login( $name, $password ) {
            if( parent::login($name, $password) ) {
                $_SESSION['userId'] = parent::getId();
                // Save the ip of the user in the session, anti-xss
                $_SESSION['userIp'] = $_SERVER['REMOTE_ADDR'];
                return $this->regenerateSessionId();
            } else {
                return false;
            }
        }

        public function auth(){
            if( !isset($_SESSION) || !isset($_SESSION['userId']) ) {
                return false;
            } elseif( !parent::fetchFromDB($_SESSION['userId']) ){
                return false;
            } else {
                return ( $this->checkIp() && $this->checkSessionId() );
            }
        }

        public function isAuth( ) {
            if( parent::getName() == null || parent::getId() == -1 ) {
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
