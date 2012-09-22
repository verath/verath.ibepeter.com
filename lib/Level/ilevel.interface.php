<?php
	interface ILevel {
		public function checkPassword( $password );
		
		public function addTry();
		
		public function getHints();
		
		public function getLevelId();
		
		public function getPassword();

		public static function generatePassword( $length );
	}
?>