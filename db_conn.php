<?php
$conn = new mysqli("localhost", "root", "", "employees");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>