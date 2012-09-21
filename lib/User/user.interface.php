<?php
    interface User {
        
        // Handled by DB
        public function getAllCompletedLevels();
        public function hasDoneLevel( $levelId );
        public function hasAccessToLevel( $levelId );
        public function completeLevel( $levelId );
        
        public function register( $name, $password );

        public function getName( );
        public function getId( );
        
        // Handled by both sessions/cookies and DB
        public function login( $name, $password );

        // Handled by sessions / cookie
        public function auth();    
        public function isAuth( );
        public function logout( );
    }
?>