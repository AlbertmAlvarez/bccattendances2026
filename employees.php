<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION['user']['name'] ?? 'Admin';


$conn = new mysqli("localhost", "root", "", "employees");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$result = $conn->query("SELECT * FROM employees ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management | Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root { --primary-bg: #f8f9fa; --sidebar-color: #0f172a; --accent-color: #6366f1; --glass-white: rgba(255, 255, 255, 0.9); }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--primary-bg); margin: 0; }

      
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar-color); position: fixed; left: 0; top: 0; transition: 0.3s; z-index: 1000; color: white; display: flex; flex-direction: column; }
        .sidebar.collapsed { width: 80px; }
        .sidebar .nav-link { color: #94a3b8; padding: 12px 20px; margin: 5px 15px; border-radius: 8px; display: flex; align-items: center; text-decoration: none; transition: 0.2s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .sidebar .nav-link.active { background: var(--accent-color); }
        .main-content { margin-left: 260px; transition: 0.3s; min-height: 100vh; }
        .sidebar.collapsed + .main-content { margin-left: 80px; }
        .top-navbar { height: 70px; background: var(--glass-white); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: space-between; padding: 0 30px; position: sticky; top: 0; z-index: 999; }

       
        .data-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin: 24px; border: 1px solid #e2e8f0; overflow: hidden; }
        .table thead th { background: #f1f5f9; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; padding: 16px; border: none; }
        .table tbody td { padding: 16px; vertical-align: middle; color: #334155; border-bottom: 1px solid #f1f5f9; }
        
        .btn-action { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; border: none; transition: 0.2s; }
        .btn-edit { background: #e0e7ff; color: #4338ca; }
        .btn-print { background: #fef3c7; color: #92400e; }
        .btn-delete { background: #fee2e2; color: #b91c1c; }
        .btn-action:hover { transform: translateY(-2px); opacity: 0.9; }

     
        .id-card-container { display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; }
        .id-card { width: 300px; height: 480px; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2); text-align: center; border: 1px solid #ddd; position: relative; display: flex; flex-direction: column; }
        
        .id-header { background: linear-gradient(135deg, #dc3545, #b02a37); color: white; padding: 5px; height: 180px; }
        .id-photo { width: 120px; height: 120px; border-radius: 50%; border: 5px solid white; margin-top: -10px; background: white; object-fit: cover; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .id-body { padding: 10px 20px; flex-grow: 1; }
        .id-footer { background: #f8f9fa; padding: 10px; font-size: 0.7rem; color: #666; border-top: 1px solid #eee; }

        .back-details { font-size: 0.8rem; text-align: left; margin-top: 20px; }
        .back-label { font-weight: bold; color: #dc3545; display: block; margin-bottom: 2px; }
        .signature-line { margin-top: 50px; border-top: 1px solid #333; width: 80%; margin-left: auto; margin-right: auto; padding-top: 5px; font-weight: bold; font-size: 0.9rem; }

        @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="p-4 text-center border-bottom border-secondary mb-3">
        <img src="img.jpg" class="rounded-circle mb-2 border border-2 border-primary" style="width: 60px; height: 60px; object-fit: cover;" alt="Admin">
        <h6 class="mb-0 fw-bold">Admin Portal</h6>
        <small class="text-muted"><?= htmlspecialchars($adminName) ?></small>
    </div>
    <div style="flex-grow: 1;">
        <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-1x2 me-2"></i> Dashboard</a>
        <a href="employees.php" class="nav-link active"><i class="bi bi-people me-2"></i> Employees</a>
        <a href="attendance.php" class="nav-link "><i class="bi bi-calendar-check me-2"></i> Attendance</a>
        <a href="reports.php" class="nav-link"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reports</a>
        
    </div>
    <div class="pb-4">
        <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
    </div>
</div>
</div>


<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="logoutModalLabel">Ready to Leave?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-muted">
                Select "Logout" below if you are ready to end your current session.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <a href="logout.php" class="btn btn-danger rounded-pill px-4">Logout</a>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</div>

<div class="main-content">
    <nav class="top-navbar">
        <button class="btn btn-light" onclick="document.getElementById('sidebar').classList.toggle('collapsed')"><i class="bi bi-list"></i></button>
        <div class="d-flex align-items-center gap-3">
            <div class="input-group" style="width: 300px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search employees...">
            </div>
            <button class="btn btn-outline-dark px-4" onclick="printAllEmployees()"><i class="bi bi-printer me-1"></i> Print All List</button>
            <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#employeeModal"><i class="bi bi-plus-lg me-1"></i> Add Employee</button>
        </div>
    </nav>

    <div class="data-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Age/Birth</th>
                        <th>Contact</th>
                        <th>Gender</th>
                        <th>Position</th>
                        <th>Address</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if(!empty($row['photo'])): ?>
                                            <img src="data:image/png;base64,<?= base64_encode($row['photo']) ?>" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-secondary rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">?</div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($row['firstName'].' '.$row['lastName']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($row['email'] ?? '') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= $row['age'] ?>y/o<br><small class="text-muted"><?= $row['birthDate'] ?></small></td>
                                <td><?= htmlspecialchars($row['contactNumber']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $row['email'] ?></span></td> 
                                <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= htmlspecialchars($row['position']) ?></span></td>
                                <td class="small text-truncate" style="max-width: 150px;"><?= htmlspecialchars($row['address']) ?></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>"><i class="bi bi-pencil-fill"></i></button>
                                        <button class="btn-action btn-print" onclick="openPrintModal(
                                            '<?= base64_encode($row['photo']) ?>', 
                                            '<?= addslashes($row['firstName'].' '.$row['lastName']) ?>', 
                                            '<?= addslashes($row['position']) ?>', 
                                            '<?= addslashes($row['address']) ?>', 
                                            '<?= $row['birthDate'] ?>', 
                                            '<?= $row['contactNumber'] ?>', 
                                            '<?= $row['barcode'] ?>'
                                        )"><i class="bi bi-printer-fill"></i></button>
                                        <button class="btn-action btn-delete" onclick="confirmDelete(<?= $row['id'] ?>)"><i class="bi bi-trash-fill"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header border-0"><h5 class="modal-title fw-bold">Edit Employee</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <form action="update_employee.php" method="post" enctype="multipart/form-data">
                                            <div class="modal-body p-4">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <div class="row g-3">
                                                    <div class="col-md-12 text-center mb-3">
                                                        <?php if(!empty($row['photo'])): ?>
                                                            <img src="data:image/png;base64,<?= base64_encode($row['photo']) ?>" class="rounded-pill mb-2 border" style="width: 100px; height: 100px; object-fit: cover;">
                                                        <?php endif; ?>
                                                        <input type="file" name="photo" class="form-control form-control-sm mx-auto" style="max-width: 250px;">
                                                    </div>
                                                    <div class="col-md-4"><label class="small fw-bold">First Name</label><input type="text" class="form-control" name="firstName" value="<?= $row['firstName'] ?>" required></div>
                                                    <div class="col-md-4"><label class="small fw-bold">Middle Name</label><input type="text" class="form-control" name="middleName" value="<?= $row['middleName'] ?>"></div>
                                                    <div class="col-md-4"><label class="small fw-bold">Last Name</label><input type="text" class="form-control" name="lastName" value="<?= $row['lastName'] ?>" required></div>
                                                    <div class="col-md-4"><label class="small fw-bold">Age</label><input type="number" class="form-control" name="age" value="<?= $row['age'] ?>" required></div>
                                                    <div class="col-md-4"><label class="small fw-bold">Birth Date</label><input type="date" class="form-control" name="birthDate" value="<?= $row['birthDate'] ?>" required></div>
                                                    <div class="col-md-4"><label class="small fw-bold">Contact</label><input type="tel" class="form-control" name="contactNumber" value="<?= $row['contactNumber'] ?>" required></div>
                                                    <div class="col-md-6">
                                                        <label class="small fw-bold">Gender</label>
                                                        <select name="email" class="form-select" required>
                                                            <option value="Male" <?= $row['email'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                                            <option value="Female" <?= $row['email'] == 'Female' ? 'selected' : '' ?>>Female</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6"><label class="small fw-bold">Position</label><input type="text" class="form-control" name="position" value="<?= $row['position'] ?>" required></div>
                                                    <div class="col-md-12"><label class="small fw-bold">Address</label><input type="text" class="form-control" name="address" value="<?= $row['address'] ?>" required></div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary px-4">Update</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No employees found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="employeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title fw-bold">Register New Employee</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form action="add_employee.php" method="post" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12 bg-light p-3 rounded-3 text-center mb-2">
                            <label class="d-block mb-2 fw-bold small">Employee Photo</label>
                            <div class="d-flex justify-content-center gap-2 mb-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="cameraBtn"><i class="bi bi-camera"></i> Take Photo</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="galleryBtn"><i class="bi bi-image"></i> Upload</button>
                            </div>
                            <video id="cameraStream" autoplay playsinline class="rounded-3 shadow-sm mx-auto mb-2" style="display:none; width:200px; height:150px; object-fit:cover;"></video>
                            <img id="photoPreview" src="#" class="rounded-3 shadow-sm mx-auto mb-2" style="display:none; width:150px; height:150px; object-fit:cover;">
                            <input type="file" id="galleryInput" name="photo" accept="image/*" style="display:none;">
                            <button type="button" class="btn btn-success btn-sm" id="takePhotoBtn" style="display:none;">Capture</button>
                        </div>
                        <div class="col-md-4"><label class="small fw-bold">Last Name</label><input type="text" class="form-control" name="lastName" required></div>
                        <div class="col-md-4"><label class="small fw-bold">First Name</label><input type="text" class="form-control" name="firstName" required></div>
                        <div class="col-md-4"><label class="small fw-bold">Middle Name</label><input type="text" class="form-control" name="middleName"></div>
                        <div class="col-md-3"><label class="small fw-bold">Age</label><input type="number" class="form-control" name="age" required></div>
                        <div class="col-md-4"><label class="small fw-bold">Birth Date</label><input type="date" class="form-control" name="birthDate" required></div>
                        <div class="col-md-5"><label class="small fw-bold">Contact</label><input type="tel" class="form-control" name="contactNumber" required></div>
                        <div class="col-md-6"><label class="small fw-bold">Gender</label><select name="email" class="form-select" required><option value="Male">Male</option><option value="Female">Female</option></select></div>
                        <div class="col-md-6"><label class="small fw-bold">Position</label><input type="text" class="form-control" name="position" required></div>
                        <div class="col-md-12"><label class="small fw-bold">Address</label><input type="text" class="form-control" name="address" required></div>
                        <div class="col-md-12 text-center mt-3"><svg id="employeeBarcode"></svg><input type="hidden" name="barcodeData" id="barcodeData"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light"><button type="submit" class="btn btn-primary px-5 fw-bold">Register</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="printModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-body p-4 bg-light rounded-4">
                <div id="printArea">
                    <div class="id-card-container">
                        <div class="id-card">
                            <div class="id-header"><h5 class="mb-0 fw-bold pt-3">BARAS COCKPIT CENTER</h5><small class="opacity-75">OFFICIAL EMPLOYEE ID</small></div>
                            <div class="id-body">
                                <img id="idPhoto" src="" class="id-photo">
                                <h4 id="idName" class="fw-bold mt-2 mb-0 text-uppercase">Juan Dela Cruz</h4>
                                <p id="idPosition" class="text-danger fw-bold small mb-2">Staff</p>
                                <svg id="idBarcode" class="mt-3 w-100" style="max-height: 50px;"></svg>
                                <p class="small text-muted mt-2">ID NO: <span id="idNumberDisplay"></span></p>
                                   <div class="mt-4 text-center"><div class="signature-line">EMPLOYEE SIGNATURE</div></div>
                            </div>
                            <div class="id-footer bg-danger text-white">Always wear this ID while within company premises.</div>
                        </div>
                        <div class="id-card">
                            <div class="id-header" style="height: 60px; padding: 15px;"><h6 class="mb-0 fw-bold">EMERGENCY INFO</h6></div>
                            <div class="id-body text-start p-4">
                                <div class="back-details"><span class="back-label">ADDRESS:</span><span id="idAddress" class="text-muted">...</span></div>
                                <div class="back-details mt-3"><span class="back-label">DATE OF BIRTH:</span><span id="idAgeBirth" class="text-muted">...</span></div>
                                <div class="back-details mt-3"><span class="back-label">EMERGENCY CONTACT:</span><span id="idContact" class="fw-bold fs-5">...</span></div>
                     
                            </div>
                            <div class="id-footer bg-danger text-white">If found, please return to Admin Office.</div>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4 justify-content-center"><button type="button" class="btn btn-primary px-5" onclick="printID()"><i class="bi bi-printer me-2"></i>Print Front & Back</button><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="text-danger fs-1 mb-2"><i class="bi bi-exclamation-circle-fill"></i></div>
                <h5 class="fw-bold">Are you sure?</h5>
                <form action="delete_employee.php" method="post">
                    <input type="hidden" name="id" id="deleteId">
                    <div class="d-grid gap-2"><button type="submit" class="btn btn-danger">Yes, Delete</button><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>

    document.getElementById('searchInput').addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
        });
    });

    
    function confirmDelete(id) {
        document.getElementById('deleteId').value = id;
        new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
    }

    
    function generateBarcode() {
        const uniqueCode = 'EMP-' + Date.now();
        JsBarcode("#employeeBarcode", uniqueCode, { format: "CODE128", width:2, height:40 });
        document.getElementById('barcodeData').value = uniqueCode;
    }
    document.getElementById('employeeModal').addEventListener('show.bs.modal', generateBarcode);

    const cameraBtn = document.getElementById('cameraBtn'), takePhotoBtn = document.getElementById('takePhotoBtn'), video = document.getElementById('cameraStream'), galleryInput = document.getElementById('galleryInput'), photoPreview = document.getElementById('photoPreview');
    let stream;

    cameraBtn.addEventListener('click', async () => {
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream; video.style.display = 'block'; takePhotoBtn.style.display = 'inline-block'; photoPreview.style.display = 'none';
    });

    takePhotoBtn.addEventListener('click', () => {
        const canvas = document.createElement('canvas'); canvas.width = video.videoWidth; canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        canvas.toBlob(blob => {
            const file = new File([blob], "capture.jpg", { type: "image/jpeg" });
            const dt = new DataTransfer(); dt.items.add(file); galleryInput.files = dt.files;
            photoPreview.src = URL.createObjectURL(blob); photoPreview.style.display = 'block';
        }, 'image/jpeg');
        video.style.display = 'none'; takePhotoBtn.style.display = 'none';
        if(stream) stream.getTracks().forEach(t => t.stop());
    });
    document.getElementById('galleryBtn').addEventListener('click', () => galleryInput.click());
    galleryInput.addEventListener('change', () => { if(galleryInput.files[0]) { photoPreview.src = URL.createObjectURL(galleryInput.files[0]); photoPreview.style.display = 'block'; video.style.display = 'none'; } });

    function openPrintModal(photo, name, position, address, bday, contact, barcode) {
        document.getElementById('idPhoto').src = photo.startsWith('data:') ? photo : 'data:image/png;base64,' + photo;
        document.getElementById('idName').textContent = name;
        document.getElementById('idPosition').textContent = position;
        document.getElementById('idAddress').textContent = address;
        document.getElementById('idAgeBirth').textContent = bday;
        document.getElementById('idContact').textContent = contact;
        document.getElementById('idNumberDisplay').textContent = barcode;
        JsBarcode("#idBarcode", barcode, { format: "CODE128", width: 2, height: 40, displayValue: false });
        new bootstrap.Modal(document.getElementById('printModal')).show();
    }

   function printID() {
     
        const content = document.getElementById('printArea').innerHTML;
   
        const win = window.open('', '', 'width=900,height=700');
        
      
        win.document.write(`
            <html>
            <head>
                <title>Print ID</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    /* Ibalik ang CSS para sa layout at kulay */
                    body { padding: 20px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .id-card-container { display: flex; gap: 20px; justify-content: center; }
                    .id-card { width: 300px; height: 480px; border: 1px solid #000; border-radius: 15px; overflow: hidden; text-align: center; display: flex; flex-direction: column; page-break-inside: avoid; }
                     .id-header { background: linear-gradient(135deg, #dc3545, #b02a37); color: white; padding: 5px; height: 180px; }
                    .id-photo { width: 120px; height: 120px; border-radius: 50%; border: 5px solid white; margin-top: -60px; object-fit: cover; z-index: 10; }
                    .id-body { padding: 10px; flex-grow: 1; }
                    .id-footer { background: #f8f9fa; padding: 10px; font-size: 0.7rem; border-top: 1px solid #eee; }
                    .back-details { font-size: 0.8rem; text-align: left; margin-top: 15px; }
                    .back-label { font-weight: bold; color: #dc3545; display: block; }
                    .signature-line { margin-top: 50px; border-top: 1px solid #000; width: 80%; margin: 50px auto 0; padding-top: 5px; font-size: 0.8rem; font-weight: bold; }
                    .bg-danger { background-color: #dc3545 !important; color: white !important; -webkit-print-color-adjust: exact; }
                </style>
            </head>
            <body>
                ${content}
            </body>
            </html>
        `);

        
        win.document.close(); 
        win.focus(); 

        
        win.onload = function() {
            setTimeout(function(){
                win.print();
                
                win.close(); 
            }, 1000); 
        };
        
        
        setTimeout(function(){
            win.print();
            win.close(); 
        }, 1500); 
    }
    function printAllEmployees() {
    const table = document.querySelector(".table").cloneNode(true);
    
    
    table.querySelectorAll("tr").forEach(row => {
        if (row.lastElementChild) {
            row.lastElementChild.remove();
        }
    });

    const win = window.open('', '', 'width=1000,height=700');
    win.document.write(`
        <html>
        <head>
            <title>Employee Master List - Print</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { padding: 40px; font-family: 'Plus Jakarta Sans', sans-serif; }
                .print-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background-color: #f8f9fa !important; color: black !important; }
                img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
                @media print {
                    .no-print { display: none; }
                    button { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h1>BARAS COCKPIT CENTER</h1>
                <h4>Employee Master List</h4>
                <p>Date Generated: ${new Date().toLocaleDateString()}</p>
            </div>
            ${table.outerHTML}
            <div style="margin-top: 50px;">
                <p>Prepared by: ____________________</p>
                <p>Admin Signature</p>
            </div>
        </body>
        </html>
    `);

    win.document.close();
    win.focus();
    
    
    setTimeout(() => {
        win.print();
        win.close();
    }, 1000);
}

</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Ito ang mag-ti-trigger ng popup kapag may laman ang SESSION status
    <?php if(isset($_SESSION['status'])): ?>
        Swal.fire({
            icon: '<?= $_SESSION['status_code']; ?>', // success, error, o warning
            title: '<?= $_SESSION['status']; ?>',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        <?php unset($_SESSION['status']); unset($_SESSION['status_code']); ?>
    <?php endif; ?>

    // Confirmation Modal para sa Delete
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Sigurado ka ba?',
            text: "Buburahin ang record ni " + name + ". Hindi ito maibabalik!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Gagawa ng hidden form para i-submit ang delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete_employee.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        })
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    <?php if(isset($_SESSION['status'])): ?>
        Swal.fire({
            icon: '<?= $_SESSION['status_code']; ?>',
            title: '<?= $_SESSION['status']; ?>',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        <?php unset($_SESSION['status']); unset($_SESSION['status_code']); ?>
    <?php endif; ?>
</script>
</body>
</html>