<?php
        $server = "localhost";
        $database = "sensor_db2";
        $username = "root";
        $password = "";

$conn = new mysqli($server, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
