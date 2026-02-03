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


$lastName      = trim($_POST['lastName'] ?? '');
$firstName     = trim($_POST['firstName'] ?? '');
$middleName    = trim($_POST['middleName'] ?? '');
$age           = (int)($_POST['age'] ?? 0);
$contactNumber = trim($_POST['contactNumber'] ?? '');
$email         = trim($_POST['email'] ?? '');
$position      = trim($_POST['position'] ?? '');
$address       = trim($_POST['address'] ?? '');
$barcode       = trim($_POST['barcodeData'] ?? '');


$birthDate = null;

if (!empty($_POST['birthDate'])) {
    $dateObj = DateTime::createFromFormat('Y-m-d', $_POST['birthDate']);
    if ($dateObj && $dateObj->format('Y-m-d') === $_POST['birthDate']) {
        $birthDate = $dateObj->format('Y-m-d');
    } else {
        die("❌ Invalid birth date format.");
    }
} else {
    die("❌ Birth date is required.");
}


$photo = null;

if (!empty($_FILES['photo']['tmp_name'])) {

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; 

    $fileType = mime_content_type($_FILES['photo']['tmp_name']);
    $fileSize = $_FILES['photo']['size'];

    if (!in_array($fileType, $allowedTypes)) {
        die("❌ Invalid image type. JPG, PNG, WEBP only.");
    }

    if ($fileSize > $maxSize) {
        die("❌ Image too large. Max 2MB.");
    }

    $photo = file_get_contents($_FILES['photo']['tmp_name']);
}


$stmt = $conn->prepare("
    INSERT INTO employees 
    (photo, lastName, firstName, middleName, age, birthDate, contactNumber, email, position, address, barcode)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "bsssissssss",
    $photo,
    $lastName,
    $firstName,
    $middleName,
    $age,
    $birthDate,
    $contactNumber,
    $email,
    $position,
    $address,
    $barcode
);

if ($photo !== null) {
    $stmt->send_long_data(0, $photo);
}

if ($stmt->execute()) {
    header("Location: employees.php?success=1");
    exit();
} else {
    echo "❌ SQL Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
