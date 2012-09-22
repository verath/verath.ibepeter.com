<?php
	require_once('basicLevel.class.php');

	/**
	 * A class extending the BasicLevel with methods
	 * that requires a user. Such as completing a level
	 * or checking if the user is allowed. Also comment
	 * handling.
	 *
	 */
	class UserLevel extends BasicLevel {
		/**
		 * A user instance
		 * @var User
		 */
		private $user;

		/**
		 * A pdo instance
		 * @var PDO
		 */
		private $pdo;

		/**
         * Initiates the UserLevel
         * 
         * @param int $level The level id
         * @param User A user instance for the current user.
         * @param PDO A PDO instance to use for DB queries.
         * @param array $hints An array with hints, numTries=>hint
         * @param string $password If set, this password is used for
         *                         the level, else it is randomized.
         */
        function __construct($levelId, $user, $pdo, $hints=null, $password=null){
            parent::__construct( $levelId, $hints, $password );

            if( get_class($pdo) != "PDO" ) {
                throw new InvalidArgumentException('First argument must be 
                    an instance of PDO.');
            } else {
            	$this->pdo = $pdo;
            }

            if( ! ($user instanceOf IUser) ) {
            	throw new InvalidArgumentException('The user must implement 
                    the IUser interface.');
            } else {
            	$this->user = $user;
            }
        }

        /**
         * Complets the level for the user.
         *
         * @return bool True on success
         */
        public function complete(){
            if( !$this->user->isAuth() && !$this->user->auth() ){return false;}
            return $this->user->completeLevel( $this->getLevelId() );
        }

        /**
         * Checks if the user has access to the level.
         *
         * @return bool True if allowed.
         */
        public function userHasAccess(){
            if( !$this->user->isAuth() && !$this->user->auth() ){return false;}
            
            return $this->user->hasAccessToLevel( $this->getLevelId() );
        }

        /**
         * Checks if the user has completed the Level.
         *
         * @return bool true if allowed, else false
         */
        public function userDoneLevel(){
            if( !$this->user->isAuth() && !$this->user->auth() ){return false;}
            
            return $this->user->hasDoneLevel( $this->getLevelId() );
        }

        /**
         * Returns an array of comments for the current level
         * @return array Comments for the level
         */
        public function getComments(){
            if( !$this->user->isAuth() && !$this->user->auth() ){return array();}

            $sql = 'SELECT `message`, `username`, `post_time`
                    FROM `comments`
                    WHERE `level` = :levelId 
                    ORDER BY `post_time` DESC 
                    LIMIT 0, 14';

           	$levelId = $this->getLevelId();

            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam( ':levelId', $levelId, PDO::PARAM_INT );
            
            if( $stmt->execute() ) {
                return $stmt->fetchAll();
            } else {
                return array();
            }
            
        }

        /**
         * Returns a string used to validate a user to prevent
         * CSRF.
         * 
         * @return string "Secret" string.
         */
        public function getCommentsSecret(){
            if( !$this->user->isAuth() && !$this->user->auth() ){return '';}

            return sha1(
            	date('h') 
            	. $this->getLevelId() 
            	. sha1( $this->user->getName() )
            );
        }

        /**
         * Adds a comment to the current level by the user.
         * 
         * @param string $message The message to add.
         * @return bool True on success
         */
        public function addComment( $message ){
            if( !$this->user->isAuth() && !$this->user->auth() ){return false;}

            $sql = 'INSERT INTO `comments` (`level`, `username`, `message`)
                    VALUES ( :levelId, :username, :message)';
            
            $level_id = $this->getLevelId();
            $username = $this->user->getName();

            $stmt = $this->pdo->prepare( $sql );
            $stmt->bindParam( ':levelId', $level_id, PDO::PARAM_INT );
            $stmt->bindParam( ':username', $username, PDO::PARAM_STR );
            $stmt->bindParam( ':message', $message, PDO::PARAM_STR );
            
            return $stmt->execute();    
        }
	}
?>