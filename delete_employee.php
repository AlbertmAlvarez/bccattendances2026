<?php
session_start();
$conn = new mysqli("localhost", "root", "", "employees");

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    if($conn->query("DELETE FROM employees WHERE id = $id")) {
        $_SESSION['status'] = "Employee Deleted Successfully!";
        $_SESSION['status_code'] = "success";
    } else {
        $_SESSION['status'] = "Something went wrong!";
        $_SESSION['status_code'] = "error";
    }
}
header("Location: employees.php");
exit();
?>