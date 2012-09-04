<?php
    require_once('config.php');
    require_once('sensitive.class.php');
    

    class User{
        private $name;
        private $level;
        private $id;

        private $pdo;


        /**
        * Tries to validate user, or log in user if $username
        * and $password is passed
        * @param string $username username to log in
        * @param string $password password to log in
        * @return User for chaining
        */
        function __construct($username=null, $password=null){
            // Grab the pdo class
            global $pdo;
            $this->pdo = $pdo;

            if( !is_null($username) && !is_null($password) ){
                $this->login($username, $password);
            } elseif( !$this->is_auth() ){
                $this->auth_user();
            }

            return $this;
        }


        /*
        * Loads the database settings file on demand
        */
        private function setup_db(){
            if( !isset($this->pdo) ){
                require_once('db.php');
                $this->pdo =& $pdo;
            }
        }


        /**
        * Generates a password hash from the $password
        * and $username
        * @param string $username
        * @param string $password
        * @return string A password hash for use in the database
        */
        private function get_password_hash($password, $username){
            $hash = '';
            for( $i=0; $i < 100; $i++ ){
                $hash = sha1( Sensitive::$secret_hash_phrase . substr($hash, 0, 5) . $password . $username );
            }

            return $hash;
        }


        /**
        * Regenerates the user's session_id to prevent session
        * hijacking.
        * @return bool true if success, else false
        */
        private function set_new_session_id(){
            session_regenerate_id();
            $session_id = session_id();

            $this->setup_db();

            $sql = 'UPDATE `users` SET `session_id` = :session_id WHERE `id` = :user_id LIMIT 1';
            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT );
            $stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);

            return $stmt->execute();
        }

        /**
        * Validates a password
        * @param string $password
        * @param string $password_confirm 
        * @return mixed true on success, else an error string
        */
        private function validate_password($password, $password_confirm){
            if( $password !== $password_confirm ){
                return 'The passwords didn\'t match';
            }
            if( strlen($password) < 4 ){
                return 'Password must be > 4 chars';
            }

            return true;
        }

        /**
        * Validates a username
        * @param string $username
        * @return mixed true on success, else an error string
        */
        private function validate_username($username){
            $this->setup_db();

            if( strlen($username) > 30 ){
                return 'Username must be <= 30 chars';
            }
            if( strlen($username) < 1 ){
                return 'Username must be >= 1 char';
            }
            if( !preg_match( '/^[a-z0-9_]+$/i', $username) ){
                return 'Username must match only contain a-Z, 0-9 and _';
            }

            $sql = 'SELECT 1 FROM `users` WHERE `username` = :username';
            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam(':username', $username, PDO::PARAM_STR );

            if( !$stmt->execute() ){
                return 'DB error, please try again later';
            }
            if( $stmt->rowCount() !== 0 ){
                return 'That username already exist';
            }

            return true;
        }


        /**
        * Validates user info and adds the new user to the table
        * @param string $username
        * @param string $password 
        * @param string $password_confirm 
        * @return mixed true on success, else an error string
        */
        public function register($username, $password, $password_confirm){
            $this->setup_db();

            $username_validate_status = $this->validate_username($username);
            if( $username_validate_status !== true ){
                return $username_validate_status;
            }

            $password_validate_status = $this->validate_password($password, $password_confirm);
            if( $password_validate_status !== true ){
                return $password_validate_status;
            }



            $password = $this->get_password_hash($password, $username);

            $sql = 'INSERT INTO `users` (`username`, `password`, `registered_at`)
                    VALUES (:username, :password, NOW())';
            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam(':username', $username, PDO::PARAM_STR );
            $stmt->bindParam(':password', $password, PDO::PARAM_STR );

            if( !$stmt->execute() ){
                return 'DB error, please try again later';
            } 

            return true;
        }


        /**
        * Attempts to log in a user
        * @param string $username
        * @param string $password 
        * @return mixed false if failed, User for chaining if success
        */
        public function login($username, $password){
            $this->setup_db();

            if( !isset($_SESSION) ) {
                session_start();
            }

            $password = $this->get_password_hash($password, $username);

            $sql = 'SELECT `username`,`level`,`id` FROM `users`
                    WHERE `username` = :username
                    AND `password` = :password LIMIT 1';
            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam(':username', $username, PDO::PARAM_STR );
            $stmt->bindParam(':password', $password, PDO::PARAM_STR );
            
            if( $stmt->execute() != true ){
                return false;
            }
            
            if( $stmt->rowCount() !== 1 ){
                return false;
            }

            $user = $stmt->fetchObject();
            
            $this->name = $user->username;
            $this->level = $user->level;
            $this->user_id = $user->id;
            $this->set_new_session_id();

            $_SESSION['username'] = $this->name;
            $_SESSION['user_id'] = $this->user_id;
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
            
            return $this;
        }


        /**
        * Logs out a user, delets the session and redirects to the main
        * page.
        * @param bool $forced
        * @param bool $redirect 
        */
        public function logout($forced=false, $redirect=true){
            // If it is a forced logout, the user logged out is
            // not the real current user. Possibly xss, or could be another 
            // computer. Don't unset the session
            if( ini_get("session.use_cookies") ){
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            if( $forced ){
                header('location: /?forced_logout=true');
            } else {
                $_SESSION = array();
                session_destroy();
            }
            
            if( $redirect ){
                header('location: /');
                die();
            }

            return $this;
        }

        
        /**
        * Attempts to auth a user from the session cookie
        * @return User, for chaining
        */
        public function auth_user(){
            if( isset($_SESSION['username']) && isset($_SESSION['user_id']) ){
                $this->name = $_SESSION['username'];
                $this->user_id = $_SESSION['user_id'];

                $this->setup_db();

                $sql = 'SELECT `level`, `session_id` FROM `users`
                        WHERE `id` = :user_id';
                $stmt = $this->pdo->prepare( $sql );
                $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT );

                if( $stmt->execute() ){
                    $result = $stmt->fetchObject();

                    // Only allow one session per user
                    if( $result->session_id !== session_id() ){
                        $this->logout(true);
                    }

                    // Require re-login if ip changes (could be xss)
                    if( $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] ){
                        $this->logout(true);
                    }

                    $this->level = $result->level;
                }
            }
            return $this;
        }

        /**
        * Checks if user is set
        * @param int $level
        * @return bool true if user is set, else false
        */
        public function is_auth(){
            if( isset($this->name) && isset($this->level) ){
                return true;
            } else {
                return false;
            }
        }


        /**
        * Checks if user is allowed to view a level
        * @param int $level
        * @return bool true if allowed, else false
        */
        public function has_access_to_level($level=0){
            if( !$this->is_auth() ){
                return false;
            }

            if( $this->level >= $level ){
                return true;
            } else {
                return false;
            }
        }


        /**
        * Returns the current level of the user
        * @return int The level of the user, or false if none
        */
        public function get_level(){
            if( $this->is_auth() ){
                return $this->level;
            } else {
                return false;
            }
        }

        /**
        * Returns the name of the user
        * @return string The name of the user, or false if none
        */
        public function get_name(){
            if( $this->is_auth() ){
                return $this->name;
            } else {
                return false;
            }
        }


        /**
        * Complets a level for the user
        * @param int $level
        * @return bool true if success, else false
        */
        public function complete_level( $level ){
            if( !$this->is_auth() ){
                return false;
            }

            $next_level = ($level +1);
            
            $this->setup_db();

            $sql = 'UPDATE `users` SET `level` = :level 
                    WHERE `id` = :user_id AND `level` < :level';
            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam(':user_id',  $this->user_id, PDO::PARAM_INT );
            $stmt->bindParam(':level', $next_level, PDO::PARAM_INT);
            
            if( $stmt->execute() === false ){
                return false;
            }

            $this->level = $next_level;

            // Add stats for completion if first time.
            if($stmt->rowCount() === 1){
                $sql = 'INSERT INTO `level_stats_completion` (`username`,`level`)
                        VALUES (:username,  :level)';
                $stmt = $this->pdo->prepare( $sql );
                $stmt->bindParam(':username',  $this->name, PDO::PARAM_STR );
                $stmt->bindParam(':level', $level, PDO::PARAM_INT);
                
                $stmt->execute();
            }

            return true;
        }

    }
?>