<?php
require_once( 'ilevel.interface.php' );

/**
 * A class implementing a basic level. The BasicLevel
 * does not handle users.
 *
*/
class BasicLevel implements ILevel {
	/**
	 * The level id
	 * @var int
	 */
	private $level_id;

	/**
	 * The number of tries user has done this
	 * session.
	 * @var int
	 */
	private $num_tries;

	/**
	 * An array mapping number of tries to
	 * a hint.
	 * @var array
	 */
	private $hints;

	/**
	 * The password for the level, generated on
	 * a per session basis.
	 * @var string
	 */
	private $password;

	/**
	 * The total number of levels.
	 * @var int
	 */
	const NUM_LEVELS = 7;

	/**
	 * Initiates the BasicLevel
	 *
	 * @param int $level The level id
	 * @param array $hints An array with hints, numTries=>hint
	 * @param string $password If set, this password is used for
	 *                         the level, else it is randomized.
	 */
	function __construct($level_id, $hints=null, $password=null) {
		if( filter_var($level_id, FILTER_VALIDATE_INT) ){
			$this->level_id = $level_id;
		} else {
			throw new InvalidArgumentException('level_id must be
					an integer.');
		}

		if( !is_null($hints) && !is_array($hints) ){
			throw new InvalidArgumentException('Hint must be
					either null or an array.');
		} elseif( is_array($hints) ) {
			$this->hints = $hints;
		}

		if( is_string($password) ){
			$this->password = $password;
		} else {
			$this->generateLevelPassword();
		}
	}

	/**
	 * Gets the number of tries done for the
	 * current level from the session.
	 *
	 * @return int Number of tries
	 */
	private function getTries() {
		if( !isset($this->num_tries) ){
			if( !isset($_SESSION['levelTries']) ){
				$_SESSION['levelTries'] = array();
			}
			if( isset($_SESSION['levelTries'][$this->level_id]) ){
				$this->num_tries = $_SESSION['levelTries'][$this->level_id];
			} else {
				$this->num_tries = 0;
				$_SESSION['levelTries'][$this->level_id] = 0;
			}
		}

		return $this->num_tries;
	}

	/**
	 * Generates a password for the current level and saves it in
	 * the session.
	 */
	private function generateLevelPassword(){
		if( !isset($this->password) ){
			if( !isset($_SESSION['levelPass']) ){
				$_SESSION['levelPass'] = array();
			}
			if( isset($_SESSION['levelPass'][$this->level_id]) ){
				$this->password = $_SESSION['levelPass'][$this->level_id];
			} else {
				$this->password = BasicLevel::generatePassword(7);
				$_SESSION['levelPass'][$this->level_id] = $this->password;
			}
		}
	}

	/**
	 * Compares a password to the level password
	 *
	 * @param string $password The password to check against
	 * @return bool True if they match, else false
	 */
	public function checkPassword( $password ) {
		return (isset($this->password) && $password == $this->password);
	}

	/**
	 * Adds one to the current try/session/level
	 *
	 */
	public function addTry() {
		$this->num_tries = $this->getTries() + 1;
		$_SESSION['levelTries'][$this->level_id] = $this->num_tries;
	}

	/**
	 * Returns hints for the current amount of tries
	 *
	 * @return string Hints for the current tries
	 */
	public function getHints() {
		if( !isset($this->hints) || is_null($this->hints) ) {
			return '';
		}

		$hint_str = '';
		foreach( $this->hints as $tries => $hint ) {
			if( $this->getTries() > $tries ){
				$hint_str .= $hint . "\n";
			}
		}
		return trim($hint_str);
	}

	/**
	 * Returns the current level number
	 * 
	 * @return int The level number
	 */
	public function getLevelId(){
		return $this->level_id;
	}

	/**
	 * Returns the password for the current level
	 *
	 * @return string The level password
	 */
	public function getPassword() {
		if( !isset($this->password) ){
			$this->generateLevelPassword();
		}
		return $this->password;
	}

	/**
	 * Generates a random password using the letters a-Z
	 *
	 * @param int $length Length of the password.
	 * @return string A password of lenght $length.
	 */
	public static function generatePassword( $length ) {
		$r = '';
		for( $i = 0; $i < $length; $i++ ){
			$r .= chr( (rand(0, 1) ? ord('A') : ord('a')) + rand(0, 25) );
		}
		return $r;
	}
}
?>