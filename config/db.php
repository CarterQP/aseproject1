<?php
//database configuration
$host = "localhost";
$db_name = "musicdb";
$username = "root";
$password = "mypassword";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection error: " . $e->getMessage());
}
?>
