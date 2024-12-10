<?php
// Set the database connection details
$host = 'localhost';         // your database host, usually 'localhost'
$dbname = 'moviereview'; // your database name
$username = 'root';           // your database username, default is 'root' for XAMPP
$password = '';               // your database password, default is empty for XAMPP

try {
    // Create a PDO instance to connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
 

} catch (PDOException $e) {
    // Handle the error if the connection fails
    echo "Connection failed: " . $e->getMessage();
    exit; // Stop further script execution if connection fails
}

// Your registration logic here...
?>
