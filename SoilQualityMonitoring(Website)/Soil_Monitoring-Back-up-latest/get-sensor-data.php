<?php
include "connection.php";

$data = array();

$sql = "SELECT * FROM `soil` ORDER BY timestamp DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

// Encode the data as JSON and send the response
header('Content-Type: application/json');
echo json_encode($data);
?>