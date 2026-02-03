<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
$conn = new mysqli("localhost","root","","employees");

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=attendance_'.date('Y-m-d').'.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['First Name','Last Name','Position','Time In','Time Out']);

$results = $conn->query("SELECT e.firstName,e.lastName,e.position,a.time_in,a.time_out
    FROM attendance a JOIN employees e ON a.employee_id=e.id
    WHERE a.date='".date('Y-m-d')."' ORDER BY a.time_in ASC");

while($row=$results->fetch_assoc()){
    fputcsv($output, [$row['firstName'],$row['lastName'],$row['position'],$row['time_in'],$row['time_out']]);
}
fclose($output);
exit();
?>
