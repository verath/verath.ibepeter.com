<?php
    require_once('db.php');
    require_once('config.php');

    class Level{
        private $level;
        private $num_tries;
        private $hints;
        private $password;
        private $user;

        public static $num_levels = 8;

        private $pdo;

        /**
        * Initiates the level
        * @param int $level The current level
        * @param User The current user
        * @param array $hints An array with hints, numTries=>Hint
        * @param string $level_password If set, this password is used for the level
        * @return Level for chaining
        */
        function __construct($level, $user, $hints=null, $level_password=null){
            // Grab the pdo class
            global $pdo;
            $this->pdo = $pdo;

            $this->level = $level;
            $this->user = $user;
            
            if( !is_null($hints) ){
                $this->set_hints($hints);
            } else {
                $this->hints = null;
            }

            if( !is_null($level_password) ){
                $this->password = $level_password;
            } else {
                $this->generate_level_password();
            }

            return $this;
        }


        /**
        * Returns the current level as a string
        */
        public function __toString(){
            return (string)$this->get_level();
        }


        /**
        * Returns the current level number
        * @return int The level number
        */
        public function get_level(){
            return $this->level;
        }


        /*
        * Generates a password, a-Z
        */
        private function generate_password( $len = 7 ){
            $r = '';
            for($i=0; $i<$len; $i++){
                $r .= chr( (rand(0, 1) ? ord('A') : ord('a')) + rand(0, 25) );
            }
            return $r;
        }


        /**
        * Generates a password for the current level and saves it in
        * the session.
        */
        private function generate_level_password(){
            if( !isset($this->password) ){
                if( !isset($_SESSION['levelPass']) ){
                    $_SESSION['levelPass'] = array();
                }
                if( isset($_SESSION['levelPass'][$this->level]) ){
                    $this->password = $_SESSION['levelPass'][$this->level];
                } else {
                    $this->password = $this->generate_password(7);
                    $_SESSION['levelPass'][$this->level] = $this->password;
                }
            }
        }


        /**
        * Returns the password for the current level
        * @return string The level password
        */
        public function get_password(){
            if( !isset($this->password) ){
                $this->generate_level_password();
            }
            return $this->password;
        }


        /**
        * Compares a password to the level password
        * @param string The password to check against
        * @return bool True if they match, else false
        */
        public function check_password($password){
            if( isset($this->password) ){
                return ($password === $this->password);
            } else {
                return false;
            }
        }



        /**
        * Gets the number of tries for the current level
        * @return int Number of tries
        */
        private function get_tries(){
            if( !isset($this->num_tries) ){
                if( !isset($_SESSION['levelTries']) ){
                    $_SESSION['levelTries'] = array();
                }
                if( isset($_SESSION['levelTries'][$this->level]) ){
                    $this->num_tries = $_SESSION['levelTries'][$this->level];
                } else {
                    $this->num_tries = 0;
                    $_SESSION['levelTries'][$this->level] = 0;
                }
            }

            return $this->num_tries;
        }


        /**
        * Adds one to the current try/session/level
        * @return Level Used for chaining
        */
        public function add_try(){
            $this->num_tries = $this->get_tries()+1;
            $_SESSION['levelTries'][$this->level] = $this->num_tries;
            return $this;
        }


        /**
        * Sets the hints to be used with the level
        * @param array An array of hints, key is num tries
        * @return Level Used for chaining
        */
        public function set_hints($hints){
            if( is_array($hints) ){
                $this->hints = $hints;
            } else {
                $this->hints = null;
            }
        }


        /**
        * Returns hints for the current amount of tries
        * @return string Hints for the current tries
        */
        public function get_hints(){
            if(!isset($this->hints) || is_null($this->hints)){
                return '';
            }
            
            $hint_str = '';
            foreach ($this->hints as $tries => $hint) {
                if( $this->get_tries() > $tries ){
                    $hint_str .= $hint . "\n";
                }
            }
            return trim($hint_str);
        }


        /**
        * Returns an array of comments for the current level
        * @return array Comments for the level
        */
        public function get_comments(){
            $sql = 'SELECT `message`, `username`, `post_time` FROM `comments`
                    WHERE `level` = :level ORDER BY `post_time` DESC LIMIT 0, 14';
            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam( ':level', $this->level, PDO::PARAM_INT );
            
            if( $stmt->execute() ) {
                return $stmt->fetchAll();
            } else {
                return array();
            }
            
        }


        /**
        * Returns a string used to validate a user
        * @param User $user An optional other User instance to use
        * @return string Secret string
        */
        public function get_comments_secret($user=null){
            if( is_null($user) ){
               $user = $this->user; 
            }
            return sha1( date('h') . $this->get_level() . sha1($this->user->get_name()) );
        }


        /**
        * Adds a comment to the current level by the user.
        * @param User $user An optional other User instance to use
        * @return bool true on success, else false
        */
        public function add_comment($message, $user=null){
            if( is_null($user) ){
               $user = $this->user; 
            }

            $sql = 'INSERT INTO `comments` (`level`, `username`, `message`)
                    VALUES ( :level, :username, :message)';
            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam( ':level', $this->get_level(), PDO::PARAM_INT );
            $stmt->bindParam( ':username', $user->get_name(), PDO::PARAM_STR );
            $stmt->bindParam( ':message', $message, PDO::PARAM_STR );
            
            if( $stmt->execute() ){
                return true;
            } else {
                return false;
            }
            
        }


        /**
        * Complets the level for the user
        * @param User $user An optional other User instance to use
        * @return bool true if success, else false
        */
        public function complete($user=null){
            if( is_null($user) ){
               $user = $this->user; 
            }
            return $user->complete_level( $this->get_level() );
        }


        /**
        * Checks if the user has access to the current level
        * @param User $user An optional other User instance to use
        * @return bool true if allowed, else false
        */
        public function user_has_access($user=null){
            if( is_null($user) ){
               $user = $this->user; 
            }
            return $user->has_access_to_level( $this->get_level() );
        }


        /**
        * Checks if the user has completed the level already
        * @param User $user An optional other User instance to use
        * @return bool true if allowed, else false
        */
        public function user_done_level($user=null){
            if( is_null($user) ){
               $user = $this->user; 
            }

            if( $user->get_level() && $user->get_level() > $this->get_level() ){
                return true;
            } else {
                return false;
            }
        }

    }

?>