<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "employees");
if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $lastName = $_POST['lastName'];
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $age = $_POST['age'];
    $birthDate = $_POST['birthDate'];
    $contactNumber = $_POST['contactNumber'];
    $gender = $_POST['email']; 
    $position = $_POST['position'];
    $address = $_POST['address'];

   
    $photo = null;
    $hasPhoto = false;
    if (!empty($_FILES['photo']['tmp_name'])) {
        $photo = file_get_contents($_FILES['photo']['tmp_name']);
        $hasPhoto = true;
    }

    if ($hasPhoto) {
        
        $stmt = $conn->prepare("UPDATE employees SET 
            lastName=?, firstName=?, middleName=?, age=?, birthDate=?, contactNumber=?, email=?, position=?, address=?, photo=? 
            WHERE id=?");
        $stmt->bind_param("sssisssssbi", $lastName, $firstName, $middleName, $age, $birthDate, $contactNumber, $gender, $position, $address, $null, $id);
        $stmt->send_long_data(9, $photo); 
    } else {
       
        $stmt = $conn->prepare("UPDATE employees SET 
            lastName=?, firstName=?, middleName=?, age=?, birthDate=?, contactNumber=?, email=?, position=?, address=? 
            WHERE id=?");
        $stmt->bind_param("sssisssssi", $lastName, $firstName, $middleName, $age, $birthDate, $contactNumber, $gender, $position, $address, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['status'] = "Employee Updated Successfully!";
        $_SESSION['status_code'] = "success";
    } else {
        $_SESSION['status'] = "Update Failed: " . $stmt->error;
        $_SESSION['status_code'] = "error";
    }

    $stmt->close();
}

$conn->close();
header("Location: employees.php");
exit();
?>