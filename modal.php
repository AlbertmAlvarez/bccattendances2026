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
                        <div class="col-md-6"><label class="small fw-bold">Gender</label><select name="gender" class="form-select" required><option value="Male">Male</option><option value="Female">Female</option></select></div>
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
                            <div class="id-header">
                                <h5 class="mb-0 fw-bold pt-3">BARAS COCKPIT</h5>
                                <small class="opacity-75">OFFICIAL EMPLOYEE ID</small>
                            </div>
                            <div class="id-body">
                                <img id="idPhoto" src="" class="id-photo">
                                <h4 id="idName" class="fw-bold mt-2 mb-0 text-uppercase">Juan Dela Cruz</h4>
                                <p id="idPosition" class="text-danger fw-bold small mb-2">Staff</p>
                                <svg id="idBarcode" class="mt-3 w-100" style="max-height: 50px;"></svg>
                                <p class="small text-muted mt-2">ID NO: <span id="idNumberDisplay"></span></p>
                            </div>
                            <div class="id-footer">Always wear this ID within premises.</div>
                        </div>

                        <div class="id-card">
                            <div class="id-header" style="height: 60px; padding: 15px;">
                                <h6 class="mb-0 fw-bold">EMERGENCY INFO</h6>
                            </div>
                            <div class="id-body text-start p-4">
                                <div class="back-details">
                                    <span class="back-label">ADDRESS:</span>
                                    <span id="idAddress" class="text-muted">...</span>
                                </div>
                                <div class="back-details mt-3">
                                    <span class="back-label">DATE OF BIRTH:</span>
                                    <span id="idAgeBirth" class="text-muted">...</span>
                                </div>
                                <div class="back-details mt-3">
                                    <span class="back-label">EMERGENCY CONTACT:</span>
                                    <span id="idContact" class="fw-bold fs-5">...</span>
                                </div>
                                <div class="mt-4 text-center">
                                    <div class="signature-line">EMPLOYEE SIGNATURE</div>
                                </div>
                            </div>
                            <div class="id-footer bg-danger text-white">If found, return to Admin Office.</div>
                        </div>

                    </div>
                </div>
                <div class="d-flex gap-2 mt-4 justify-content-center">
                    <button type="button" class="btn btn-primary px-5" onclick="printID()"><i class="bi bi-printer me-2"></i>Print Front & Back</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
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