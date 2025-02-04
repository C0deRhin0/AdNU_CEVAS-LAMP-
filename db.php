<?php
$host = "localhost";
$dbname = "adnu_cevas";
$username = "root";
$password = "root";
                                                    //*IMPORTANT* 
ini_set('session.cookie_httponly', 1); //Prevent access to session cookies via JavaScript
ini_set('session.cookie_secure', 1);  //Ensure cookies are sent over HTTPS
ini_set('session.use_strict_mode', 1); //Reject uninitialized session IDs

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
