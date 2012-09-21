<?php
    require_once('user.interface.php');

    abstract class DatabaseUser implements User {
        private $pdo;
        
        private $name;
        private $id;
        private $levels_done;

        /** 
         * DatabaseUser constructor. Sets default values and
         * the pdo instance to use.
         * 
         * @param PDO $pdo The PDO instance to use for DB queries.
         */
        public function __construct( $pdo ){
            if( get_class($pdo) != "PDO" ) {
                throw new InvalidArgumentException('First argument must be 
                    an instance of PDO.');
            }

            $this->pdo          = $pdo;
            $this->name         = null;
            $this->id           = -1;
            $this->levels_done  = null;
        }

        /**
         * Generates a password hash from the $password
         * and $username.
         * 
         * @param string $username
         * @param string $password
         * @return string A password hash for use in the database
         */
        private function getPasswordHash( $username, $password ) {
            $hash = '';
            for( $i=0; $i < 100; $i++ ){
                $hash = sha1(
                    Sensitive::$HASH_PHRASE 
                    . substr($hash, 0, 5) 
                    . $password 
                    . $username 
                );
            }
            return $hash;
        }

        /**
         * Quieries the database for all levels the user
         * has completed already.
         *
         * @return bool true if the value could be fetched.
         */
        private function fetchCompletedLevels(){
            if( $this->name == null ){ return false; }

            $sql = 'SELECT `level` FROM `level_stats_completion` 
                    WHERE  `username` =  :username';

            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam(':username', $this->name, PDO::PARAM_STR );
            
            if( $stmt->execute() ){
                $this->levels_done = array();
                while( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
                    array_push($this->levels_done, intval($row['level']));
                }
                return true;
            } else {
                return false;
            }
        }

        /**
         * Fetches info about the user from the DB.
         *
         * @param int $userId The id of the user.
         * @return bool True if the user could be fetched.
         */
        protected function fetchFromDB( $userId ) {
            if( !filter_var($userId, FILTER_VALIDATE_INT) ){
                throw new InvalidArgumentException('userId must be an integer.');
            }

            $sql = 'SELECT `id`, `username`
                    FROM `users`
                    WHERE `id` = :userId
                    LIMIT 1';
            
            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT );
            
            if( $stmt->execute() && $stmt->rowCount() == 1 ){
                $dbUser = $stmt->fetchObject();
                $this->id = $dbUser->id;
                $this->name = $dbUser->username;
                $this->fetchCompletedLevels();
                return true;
            } else {
                return false;
            }
        }

        /**
         * Gets all the levels the user has completed.
         *
         * @return array Empty if no levels has been completed, 
         *               or if none could be fetched.
         */
        public function getAllCompletedLevels() {
            if( !is_array($this->levels_done) && !$this->fetchCompletedLevels() ) {
                return array();
            } else {
                return $this->levels_done;
            }
        }

        /**
         * Tests if a user has done a level.
         * 
         * @param int $levelId The id of the level.
         * @return bool
         */
        public function hasDoneLevel( $levelId ) {
            if( !is_array($this->levels_done) && !$this->fetchCompletedLevels() ) {
                return false;
            } else {
                return in_array($levelId, $this->levels_done);
            }
        }

        /**
         * Tests if a user has access to a level.
         * 
         * The test is done by looking at the number
         * of completed levels the user has. Every
         * 2nd completion grants access to two more levels.
         * 0-1 => access to 1-3, 2-3 => access to 1-5...
         * 
         * @param int $levelId The id of the level.
         * @return bool
         */
        public function hasAccessToLevel( $levelId ){
            if( !is_array($this->levels_done) && !$this->fetchCompletedLevels() ){
                return false;
            }
            $maxAllowedLevel = ceil((count($this->levels_done)+1) / 2) * 2 + 1;
            return ($maxAllowedLevel >= $levelId);
        }

        /**
         * Completes a level for a user. Returns true
         * if it was added successfully or if the user
         * had already completed the level.
         * 
         * @param int $levelId The id of the level.
         * @return bool True if completion was successful, else false.
         */
        public function completeLevel( $levelId ){
            if( $this->name == null ){
                return false;
            } elseif( $this->hasDoneLevel($levelId) ) {
                return true;
            } else {
                // Haven't completed the level before, insert it.
                $sql = 'INSERT INTO `level_stats_completion` (`username`,`level`)
                        VALUES (:username,  :level)';

                $stmt = $this->pdo->prepare( $sql );
                $stmt->bindParam(':username',  $this->name, PDO::PARAM_STR );
                $stmt->bindParam(':level', $levelId, PDO::PARAM_INT);
                
                if( $stmt->execute() ) {
                    array_push($this->levels_done, $levelId);
                    return true;
                } else {
                    return false;
                }
            }
        }

        /**
         * Adds a new user to the table.
         *
         * @param string $name
         * @param string $password 
         * @return bool true on success.
         */
        public function register( $name, $password ){
            $password = $this->getPasswordHash($name, $password);

            $sql = 'INSERT INTO `users` (`username`, `password`, `registered_at`)
                    VALUES (:username, :password, NOW())';

            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam( ':username', $name, PDO::PARAM_STR );
            $stmt->bindParam( ':password', $password, PDO::PARAM_STR );

            return $stmt->execute();
        }

        /**
         * Getter for the name of the user.
         * 
         * @return string
         */
        public function getName() {
            return $this->name;
        }

        /**
         * Getter for the id of the user.
         *
         * @return string
         */
        public function getId() {
            return $this->id;
        }

        /**
         * Attempts to log the user in using the provided
         * username and password.
         * 
         * @param string $name
         * @param string $password
         * @return bool False on error, else true.
         */
        public function login( $name, $password ) {
            $pass = $password;
            $password = $this->getPasswordHash($name, $pass);

            $sql = 'SELECT `id`
                    FROM `users`
                    WHERE `username` = :username
                    AND `password` = :password 
                    LIMIT 1';
            
            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam(':username', $name, PDO::PARAM_STR );
            $stmt->bindParam(':password', $password, PDO::PARAM_STR );
            
            if( $stmt->execute() && $stmt->rowCount() == 1 ){
                // Populate our instance with data
                return $this->fetchFromDB($stmt->fetchObject()->id);
            } else {
                return false;
            }
        }

        /**
         * Un-sets all our fetched data for the current user.
         */
        public function logout() {
            $this->name         = null;
            $this->id           = -1;
            $this->levels_done  = null;
        }
    }
?>
