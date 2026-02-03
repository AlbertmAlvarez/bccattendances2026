<?php
require('fpdf186/fpdf.php');
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$conn = new mysqli("localhost","root","","employees");
$results = $conn->query("SELECT e.firstName,e.lastName,e.position,a.time_in,a.time_out
    FROM attendance a JOIN employees e ON a.employee_id=e.id
    WHERE a.date='".date('Y-m-d')."' ORDER BY a.time_in ASC");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Attendance for '.date('Y-m-d'),0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,10,'First Name',1);
$pdf->Cell(40,10,'Last Name',1);
$pdf->Cell(50,10,'Position',1);
$pdf->Cell(30,10,'Time In',1);
$pdf->Cell(30,10,'Time Out',1);
$pdf->Ln();

$pdf->SetFont('Arial','',12);
while($row=$results->fetch_assoc()){
    $pdf->Cell(40,10,$row['firstName'],1);
    $pdf->Cell(40,10,$row['lastName'],1);
    $pdf->Cell(50,10,$row['position'],1);
    $pdf->Cell(30,10,$row['time_in'],1);
    $pdf->Cell(30,10,$row['time_out'] ?? '-',1);
    $pdf->Ln();
}

$pdf->Output('D','attendance_'.date('Y-m-d').'.pdf');
?>
