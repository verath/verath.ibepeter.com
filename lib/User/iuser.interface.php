<?php
interface IUser {

	// Handled by DB
	/**
	 * Gets all the levels the user has completed.
	 *
	 * @return array Array of completed levels or empty if no levels has
	 * 						been completed, or if none could be fetched.
	 */
	public function getAllCompletedLevels();
	
	/**
	 * Tests if a user has done a level
	 *
	 * @param int $levelId The id of the level.
	 * @return bool True if the level has been completed or false if not
	 */
	public function hasDoneLevel( $levelId );
	
	/**
	 * Tests if a user has access to a level
	 *
	 * @param int $levelId The id of the level.
	 * @return bool True if user has access or false if not or failure
	 */
	public function hasAccessToLevel( $levelId );
	
	/**
	 * Completes a level for a user.
	 *
	 * @param int $levelId The id of the level to complete
	 * @return bool True if completion was successful or false on failure
	 */
	public function completeLevel( $levelId );

	/**
	 * Adds a new user to the table.
	 *
	 * @param string $name
	 * @param string $password
	 * @return bool true on success or false on failure.
	 */
	public function register( $name, $password );
	
	/**
	 * Getter for the name of the user
	 *
	 * @return string The name of the user
	 */
	public function getName( );
	
	/**
	 * Getter for the id of the user
	 *
	 * @return string The ID assosiated with the user in the DB
	 */
	public function getId( );

	// Handled by both sessions/cookies and DB
	/**
	 * Attempts to log the user in using the provided
	 * username and password
	 *
	 * @param string $name
	 * @param string $password
	 * @return bool True on success, or false on failure.
	 */
	public function login( $name, $password );

	// Handled by sessions / cookie
	/**
	 * Attempts to authenticate a user. Also fetches data for the user 
	 * if the authentication was successful.
	 *
	 * @return bool True if the user could be validated and data
	 *              was fetched or false on failure
	 */
	public function auth( );
	
	/**
	 * Checks to see if the user is authenticated.
	 *
	 * @return bool True if the user is authenticated or false if not
	 */
	public function isAuth( );
	
	/**
	 * Logs a user out, removing data assoiated with the instance
	 *
	 * @return bool True if logout was successful or false on failure
	 */
	public function logout( );
}
?>