<?php
include "connection.php";

$data = array();

$sql = "SELECT * FROM `soil` ORDER BY id DESC LIMIT 1"; 

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($data);
?>
