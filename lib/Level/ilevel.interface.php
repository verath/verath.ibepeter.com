<?php
interface ILevel {
	/**
	 * Compares a password to the level password
	 *
	 * @param string $password The password to check against
	 * @return bool True if they match, else false
	 */
	public function checkPassword( $password );

	/**
	 * Adds one to the current number of tries
	 *
	 */
	public function addTry();

	/**
	 * Returns hints for the current amount of tries
	 *
	 * @return string Hints for the current tries
	 */
	public function getHints();

	/**
	 * Returns the current level id
	 *
	 * @return int The level id
	*/
	public function getLevelId();

	/**
	 * Returns the password for the level
	 *
	 * @return string The level password
	*/
	public function getPassword();

	/**
	 * Generates a random password using the letters a-Z
	 *
	 * @param int $length Length of the password.
	 * @return string A password of lenght $length.
	*/
	public static function generatePassword( $length );
}
?>