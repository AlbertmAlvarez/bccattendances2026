<?php
$conn = new mysqli("localhost", "root", "", "employees");
if ($conn->connect_error) die("Connection failed");

$barcode = $_POST['barcode'];
$today = date("Y-m-d");
$now = date("Y-m-d H:i:s");


$check = $conn->prepare("
    SELECT * FROM attendance 
    WHERE barcode = ? AND date = ?
    ORDER BY id DESC LIMIT 1
");
$check->bind_param("ss", $barcode, $today);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {

  
    $insert = $conn->prepare("
        INSERT INTO attendance (barcode, time_in, date)
        VALUES (?, ?, ?)
    ");
    $insert->bind_param("sss", $barcode, $now, $today);
    $insert->execute();

} else {
    $row = $result->fetch_assoc();

    if ($row['time_out'] == NULL) {

        $update = $conn->prepare("
            UPDATE attendance
            SET time_out = ?
            WHERE id = ?
        ");
        $update->bind_param("si", $now, $row['id']);
        $update->execute();
    }
}

header("Location: monitoring.php");
exit;
