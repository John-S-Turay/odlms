<?php

// Enable error reporting for debugging (disable in production)
error_reporting(0);
ini_set('display_errors', 1);

// Define database credentials as constants
define('DB_HOST', 'localhost'); // Database host (usually 'localhost')
define('DB_USER', 'root');      // Database username
define('DB_PASS', '');          // Database password (empty in this case)
define('DB_NAME', 'odlmsdb');   // Database name

// Establish database connection using PDO
try {
    // Create a new PDO instance
    $dbh = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, // DSN (Data Source Name)
        DB_USER, // Database username
        DB_PASS, // Database password
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'") // Set charset to UTF-8
    );
} catch (PDOException $e) {
    // Handle connection errors
    exit("Error: " . $e->getMessage());
}
?>