<?php
$conn = new mysqli("localhost", "root", "", "employees");
if ($conn->connect_error) die("DB Error");

$barcode = $_POST['barcode'] ?? '';
$dateToday = date("Y-m-d");
$timeNow = date("H:i:s");


$emp = $conn->query("SELECT id FROM employees WHERE barcode='$barcode'");
if ($emp->num_rows === 0) {
    die("Invalid barcode");
}
$employee = $emp->fetch_assoc();
$employee_id = $employee['id'];


$check = $conn->query("
    SELECT * FROM attendance 
    WHERE employee_id=$employee_id AND date='$dateToday'
");

if ($check->num_rows === 0) {

    $conn->query("
        INSERT INTO attendance (employee_id, barcode, date, time_in)
        VALUES ($employee_id, '$barcode', '$dateToday', '$timeNow')
    ");
    $status = "TIME IN";
} else {
    $row = $check->fetch_assoc();
    if ($row['time_out'] === null) {
      
        $conn->query("
            UPDATE attendance 
            SET time_out='$timeNow'
            WHERE id={$row['id']}
        ");
        $status = "TIME OUT";
    } else {
        $status = "ALREADY TIMED OUT";
    }
}

header("Location: registration.php?status=$status");
exit;
