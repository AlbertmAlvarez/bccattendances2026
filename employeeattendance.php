<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Attendance System</title>
<link rel="stylesheet" href="Style/style.css">

<script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>

</head>

<body>
<div class="container">

<!-- EMPLOYEE REGISTRATION -->
<div class="card">
<h2>Employee Registration</h2>
<input type="text" id="empId" placeholder="Employee ID (e.g. EMP001)">
<input type="text" id="empName" placeholder="Employee Name">
<button onclick="registerEmployee()">Register & Generate QR</button>

<div class="qr-box">
<div id="qrcode"></div>
<p id="qrText"></p>
</div>
</div>


<div class="card">
<h2>Scan Employee QR (Time In / Time Out)</h2>
<input type="text" id="scanInput" placeholder="Scan / Enter Employee ID">
<button onclick="scanEmployee()">Submit</button>
<p id="scanStatus"></p>
</div>

<div class="card">
<h2>Attendance Records</h2>
<table>
<thead>
<tr>
<th>Employee ID</th>
<th>Name</th>
<th>Date</th>
<th>Time In</th>
<th>Time Out</th>
</tr>
</thead>
<tbody id="attendanceTable"></tbody>
</table>
</div>

</div>

<script>
let employees = JSON.parse(localStorage.getItem("employees")) || {};
let attendance = JSON.parse(localStorage.getItem("attendance")) || [];

function registerEmployee() {
    const id = empId.value.trim();
    const name = empName.value.trim();

    if (!id || !name) {
        alert("Please complete all fields");
        return;
    }

    employees[id] = name;
    localStorage.setItem("employees", JSON.stringify(employees));

    document.getElementById("qrcode").innerHTML = "";
    new QRCode(document.getElementById("qrcode"), id);

    qrText.innerText = "QR for: " + name + " (" + id + ")";
    empId.value = "";
    empName.value = "";
}

function scanEmployee() {
    const id = scanInput.value.trim();
    if (!employees[id]) {
        scanStatus.innerText = "Employee not registered!";
        scanStatus.style.color = "red";
        return;
    }

    const today = new Date().toLocaleDateString();
    const time = new Date().toLocaleTimeString();

    let record = attendance.find(a => a.id === id && a.date === today && !a.timeOut);

    if (!record) {
        attendance.push({
            id: id,
            name: employees[id],
            date: today,
            timeIn: time,
            timeOut: ""
        });
        scanStatus.innerText = "Time In recorded!";
        scanStatus.style.color = "green";
    } else {
        record.timeOut = time;
        scanStatus.innerText = "Time Out recorded!";
        scanStatus.style.color = "blue";
    }

    localStorage.setItem("attendance", JSON.stringify(attendance));
    scanInput.value = "";
    loadAttendance();
}

function loadAttendance() {
    attendanceTable.innerHTML = "";
    attendance.forEach(a => {
        attendanceTable.innerHTML += `
        <tr>
            <td>${a.id}</td>
            <td>${a.name}</td>
            <td>${a.date}</td>
            <td>${a.timeIn}</td>
            <td>${a.timeOut}</td>
        </tr>`;
    });
}

loadAttendance();
</script>
</body>
</html>
