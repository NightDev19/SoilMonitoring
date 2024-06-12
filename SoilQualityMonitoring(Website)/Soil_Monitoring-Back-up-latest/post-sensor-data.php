<?php
include "connection.php";

$temperature = "";
$moisture = "";
$conductivity = "";
$flow = "";

// Set the timezone to 'Asia/Manila' (Philippine Time)
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $temperature = $_POST['temperature'];
    $moisture = $_POST['moisture'];
    $conductivity = $_POST['conductivity'];
    $flow = $_POST['flow']; 

    $timestamp = date('Y-m-d H:i:s'); // Get the current timestamp in 'Y-m-d H:i:s' format

    $stmt = $conn->prepare("INSERT INTO `soil` (timestamp, temperature, moisture, conductivity, flow) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $timestamp, $temperature, $moisture, $conductivity, $flow); 

    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error";
    }

    $stmt->close();
}
?>