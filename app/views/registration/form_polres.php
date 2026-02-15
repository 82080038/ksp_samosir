<?php
// Dependency management
if (!function_exists('initView')) {
    require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';
}
if (!function_exists('getCurrentUser')) {
    require_once __DIR__ . '/../../../config/config.php';
}
$pageInfo = $pageInfo ?? (function_exists('initView') ? initView() : []);
$user = $user ?? (function_exists('getCurrentUser') ? getCurrentUser() : []);
$role = $role ?? ($user['role'] ?? 'admin');
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="registration-polres">Registrasi Polres</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<div class="container py-5">
        <div class="registration-container">
            <!-- Header -->
            <div class="form-header">
                <div class="polres-badge mb-2">POLRES SAMOSIR</div>
                <h3>FORMULIR PERMOHONAN MENJADI ANGGOTA</h3>
                <p>KOPERASI PEMASARAN POLRES SAMOSIR</p>
            </div>
            
            <div class="p-4">
                <!-- Info Box -->
                <div class="info-box">
                    <h6><i class="fas fa-info-circle me-2"></i>INFORMASI PENDAFTARAN</h6>
                    <p class="mb-0">Formulir ini khusus untuk anggota Polri aktif yang bertugas di Polres Samosir. Pastikan semua data yang diisi adalah benar dan sesuai dengan identitas resmi.</p>
                </div>
                
                <!-- Progress Indicator -->
                <div class="progress-bar-custom mb-4">
                    <div class="progress-fill" id="progressBar" style="width: 20%"></div>
                </div>
                
                <!-- Step Indicator -->
                <div class="step-indicator mb-4">
                    <div class="step active" data-step="1">
                        <div class="step-circle">1</div>
                        <small>Identitas</small>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-circle">2</div>
                        <small>Simpanan</small>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-circle">3</div>
                        <small>Dokumen</small>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-circle">4</div>
                        <small>Pernyataan</small>
                    </div>
                    <div class="step" data-step="5">
                        <div class="step-circle">5</div>
                        <small>Tanda Tangan</small>
                    </div>
                </div>
                
                <!-- Form Sections -->
                <form id="registrationForm">
                    <!-- Step 1: Identitas Pemohon -->
                    <div class="form-section active" data-section="1">
                        <h5 class="mb-4"><i class="fas fa-user me-2"></i>1. IDENTITAS PEMOHON</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">NIK</label>
                                <input type="text" class="form-control" name="no_ktp" maxlength="16" pattern="[0-9]{16}" required>
                                <small class="text-muted">16 digit nomor induk kependudukan</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" required>
                                <small class="text-muted">Sesuai KTP</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Tempat Lahir</label>
                                <input type="text" class="form-control" name="tempat_lahir" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Tanggal Lahir</label>
                                <input type="date" class="form-control" name="tanggal_lahir" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Jenis Kelamin</label>
                                <select class="form-select" name="jenis_kelamin" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">NRP</label>
                                <input type="text" class="form-control" name="nrp" required>
                                <small class="text-muted">Nomor Register Personel Polri</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Satker</label>
                                <input type="text" class="form-control" name="satker" required>
                                <small class="text-muted">Satuan Kerja</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Jabatan</label>
                                <input type="text" class="form-control" name="jabatan" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alamat Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Nomor HP</label>
                                <input type="tel" class="form-control" name="no_hp" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 2: Simpanan -->
                    <div class="form-section" data-section="2">
                        <h5 class="mb-4"><i class="fas fa-coins me-2"></i>2. SYARAT DAN KETENTUAN</h5>
                        <div class="alert alert-info">
                            <h6>Biaya yang Dikenakan:</h6>
                            <ul class="mb-0">
                                <li><strong>Simpanan Pokok:</strong> Rp 100.000,- (seratus ribu rupiah)</li>
                                <li><strong>Simpanan Wajib:</strong> Rp 50.000,- (lima puluh ribu rupiah) per bulan</li>
                            </ul>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Simpanan Pokok</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="simpanan_pokok" value="100000" min="100000" readonly>
                                </div>
                                <small class="text-muted">Modal dasar menjadi anggota koperasi (tidak dapat ditarik kembali)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Simpanan Wajib per Bulan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="simpanan_wajib" value="50000" min="50000" readonly>
                                </div>
                                <small class="text-muted">Iuran bulanan (bisa dicairkan saat pensiun/pindah tugas/meninggal)</small>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>KETENTUAN:</h6>
                            <ul>
                                <li>Pemohon merupakan Anggota Polri aktif yang bertugas di Polres Samosir</li>
                                <li>Simpanan Pokok merupakan modal dasar menjadi anggota dan tidak dapat diambil kembali</li>
                                <li>Simpanan Wajib dapat diambil dengan ketentuan: Pensiun, Pindah Tugas, atau Meninggal Dunia</li>
                                <li>Simpanan Wajib dibayarkan setiap bulan terhitung sejak dinyatakan sebagai anggota</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Step 3: Dokumen -->
                    <div class="form-section" data-section="3">
                        <h5 class="mb-4"><i class="fas fa-file-alt me-2"></i>3. DOKUMEN PENDUKUNG</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Foto Diri (Berpolri)</label>
                                <div class="photo-preview" onclick="document.getElementById('photoInput').click()">
                                    <div id="photoPlaceholder">
                                        <i class="fas fa-camera fa-2x text-muted"></i>
                                        <p class="mb-0 mt-2 text-muted">Klik untuk upload foto</p>
                                        <small class="text-muted">Menggunakan atribut lengkap</small>
                                    </div>
                                    <img id="photoPreview" style="display: none;">
                                </div>
                                <input type="file" id="photoInput" accept="image/*" style="display: none;" required>
                                <small class="text-muted">Format: JPG/PNG, Max: 2MB</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Scan KTP</label>
                                <div class="document-upload" onclick="document.getElementById('ktpInput').click()">
                                    <div id="ktpPlaceholder">
                                        <i class="fas fa-file-upload fa-2x text-muted"></i>
                                        <p class="mb-0 mt-2 text-muted">Klik untuk upload KTP</p>
                                    </div>
                                    <div id="ktpFileName" style="display: none;"></div>
                                </div>
                                <input type="file" id="ktpInput" accept="image/*,.pdf" style="display: none;" required>
                                <small class="text-muted">Format: JPG/PNG/PDF, Max: 5MB</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 4: Pernyataan -->
                    <div class="form-section" data-section="4">
                        <h5 class="mb-4"><i class="fas fa-file-contract me-2"></i>3. PERNYATAAN PEMOHON</h5>
                        <div class="alert alert-primary">
                            <p>Dengan ini saya mengajukan permohonan menjadi anggota Koperasi Pemasaran Polres Samosir, dengan mendaftarkan identitas pribadi saya sesuai dengan yang tercantum diatas.</p>
                            
                            <p>Berkenaan dengan permohonan diatas, menyatakan dengan sesungguhnya bahwa saya:</p>
                            <ol>
                                <li>Merupakan personil aktif dan saat ini bertugas di Polres Samosir</li>
                                <li>Bahwa saya siap dan bersedia dikenakan biaya berupa:
                                    <ul>
                                        <li>Simpanan Pokok: Rp 100.000,- (seratus ribu rupiah)</li>
                                        <li>Simpanan Wajib: Rp 50.000,- (lima puluh ribu rupiah)</li>
                                    </ul>
                                </li>
                                <li>Bahwa saya bersedia membayarkan Simpanan Pokok sebesar Rp 100.000,- pada saat pertama kali menjadi anggota dan sebagai modal dasar menjadi anggota koperasi</li>
                                <li>Bahwa sanggup dan bersedia membayar iuran bulanan sebagai simpanan wajib sebesar Rp 50.000,- dan dibayarkan setiap bulannya terhitung saya dinyatakan sebagai anggota Koperasi</li>
                                <li>Bahwa saya bersedia tidak mengambil simpanan wajib selama saya sebagai anggota koperasi, dan simpanan saya dapat diambil dengan ketentuan: Pensiun, Pindah Tugas, Meninggal Dunia</li>
                            </ol>
                            
                            <p><strong>Demikian formular permohonan ini saya buat dengan sebenar-benarnya.</strong></p>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="agreeStatement" required>
                            <label class="form-check-label" for="agreeStatement">
                                Saya menyatakan bahwa data di atas adalah benar dan saya setuju dengan semua syarat dan ketentuan yang berlaku
                            </label>
                        </div>
                    </div>
                    
                    <!-- Step 5: Tanda Tangan Digital -->
                    <div class="form-section" data-section="5">
                        <h5 class="mb-4"><i class="fas fa-signature me-2"></i>TANDA TANGAN PEMOHON</h5>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label required-field">Tanda Tangan Digital</label>
                                <canvas id="signaturePad" class="signature-pad" width="860" height="200"></canvas>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSignature()">
                                        <i class="fas fa-eraser me-1"></i>Hapus
                                    </button>
                                    <small class="text-muted ms-2">Gunakan mouse atau touch untuk tanda tangan digital</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Navigation Buttons -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary" id="prevBtn" onclick="previousStep()" style="display: none;">
                        <i class="fas fa-arrow-left me-2"></i>Sebelumnya
                    </button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextStep()">
                        Selanjutnya<i class="fas fa-arrow-right ms-2"></i>
                    </button>
                    <button type="button" class="btn btn-success" id="submitBtn" onclick="submitRegistration()" style="display: none;">
                        <i class="fas fa-check me-2"></i>Submit Pendaftaran
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Pendaftaran Berhasil!</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h5>Terima Kasih!</h5>
                        <p>Pendaftaran Anda telah berhasil disubmit. Tim kami akan melakukan verifikasi dan menghubungi Anda segera.</p>
                        <div class="alert alert-info">
                            <strong>No. Registrasi:</strong> <span id="registrationNumber"></span>
                        </div>
                        <p class="text-muted">Simpan nomor registrasi Anda untuk tracking status pendaftaran.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="window.location.href='/'">
                        <i class="fas fa-home me-2"></i>Kembali ke Beranda
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    
    
    <script>
        let currentStep = 1;
        let submissionData = {
            personal_data: {},
            financial_data: {},
            document_data: {},
            signature_data: {}
        };
        let submissionToken = null;
        let submissionId = null;
        let signaturePad = null;
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initializeSignaturePad();
            setupFileUploads();
            loadRegistrationForm();
        });
        
        // Initialize Signature Pad
        function initializeSignaturePad() {
            const canvas = document.getElementById('signaturePad');
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });
            
            // Resize canvas for better resolution
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear();
            }
            
            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();
        }
        
        // Setup File Uploads
        function setupFileUploads() {
            // Photo upload
            document.getElementById('photoInput').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file maksimal 2MB');
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('photoPreview').src = e.target.result;
                        document.getElementById('photoPreview').style.display = 'block';
                        document.getElementById('photoPlaceholder').style.display = 'none';
                        submissionData.document_data.photo = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // KTP upload
            document.getElementById('ktpInput').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Ukuran file maksimal 5MB');
                        return;
                    }
                    document.getElementById('ktpPlaceholder').style.display = 'none';
                    document.getElementById('ktpFileName').style.display = 'block';
                    document.getElementById('ktpFileName').innerHTML = `<i class="fas fa-file me-2"></i>${file.name}`;
                    document.querySelector('.document-upload').classList.add('has-file');
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        submissionData.document_data.ktp = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Load Registration Form
        async function loadRegistrationForm() {
            try {
                const response = await fetch('/api/registration_multi/form');
                const data = await response.json();
                
                if (data.success) {
                    console.log('Form loaded:', data.data);
                    // Update form fields based on template
                    updateFormFields(data.data);
                } else {
                    console.error('Error loading form:', data.message);
                    showNotification('Gagal memuat formulir', 'error');
                }
            } catch (error) {
                console.error('Error loading form:', error);
                showNotification('Terjadi kesalahan saat memuat formulir', 'error');
            }
        }
        
        // Update form fields based on template
        function updateFormFields(formData) {
            // Update form title if needed
            const formTitle = document.querySelector('.form-header h3');
            if (formTitle && formData.form_name) {
                formTitle.textContent = formData.form_name;
            }
            
            // Show/hide fields based on requirements
            const photoRequired = formData.photo_required;
            const ktpRequired = formData.ktp_required;
            const signatureRequired = formData.signature_required;
            
            // Update required attributes
            const photoInput = document.getElementById('photoInput');
            if (photoInput) {
                photoInput.required = photoRequired;
            }
            
            const ktpInput = document.getElementById('ktpInput');
            if (ktpInput) {
                ktpInput.required = ktpRequired;
            }
            
            // Update signature step requirement
            if (!signatureRequired) {
                // Skip signature step if not required
                document.querySelector('.step[data-step="5"]').style.display = 'none';
            }
        }
        
        // Navigation Functions
        function nextStep() {
            if (validateCurrentStep()) {
                saveCurrentStepData();
                
                document.querySelector(`.form-section[data-section="${currentStep}"]`).classList.remove('active');
                document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
                document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('completed');
                
                currentStep++;
                
                document.querySelector(`.form-section[data-section="${currentStep}"]`).classList.add('active');
                document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');
                
                updateNavigationButtons();
                updateProgressBar();
            }
        }
        
        function previousStep() {
            document.querySelector(`.form-section[data-section="${currentStep}"]`).classList.remove('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
            
            currentStep--;
            
            document.querySelector(`.form-section[data-section="${currentStep}"]`).classList.add('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');
            
            updateNavigationButtons();
            updateProgressBar();
        }
        
        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');
            
            prevBtn.style.display = currentStep > 1 ? 'block' : 'none';
            nextBtn.style.display = currentStep < 5 ? 'block' : 'none';
            submitBtn.style.display = currentStep === 5 ? 'block' : 'none';
        }
        
        function updateProgressBar() {
            const progress = (currentStep / 5) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }
        
        // Validation Functions
        function validateCurrentStep() {
            const currentSection = document.querySelector(`.form-section[data-section="${currentStep}"]`);
            const requiredFields = currentSection.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Additional validations
            if (currentStep === 1) {
                const noKtp = document.querySelector('input[name="no_ktp"]');
                if (noKtp.value && noKtp.value.length !== 16) {
                    noKtp.classList.add('is-invalid');
                    isValid = false;
                    alert('No. KTP harus 16 digit');
                }
                
                const nrp = document.querySelector('input[name="nrp"]');
                if (!nrp.value.trim()) {
                    nrp.classList.add('is-invalid');
                    isValid = false;
                    alert('NRP wajib diisi untuk anggota Polri');
                }
            }
            
            if (currentStep === 3) {
                if (!submissionData.document_data.photo) {
                    alert('Foto diri wajib diupload');
                    isValid = false;
                }
                if (!submissionData.document_data.ktp) {
                    alert('Scan KTP wajib diupload');
                    isValid = false;
                }
            }
            
            if (currentStep === 4) {
                const agreeCheckbox = document.getElementById('agreeStatement');
                if (!agreeCheckbox.checked) {
                    alert('Anda harus menyetujui pernyataan di atas');
                    isValid = false;
                }
            }
            
            if (currentStep === 5) {
                if (signaturePad.isEmpty()) {
                    alert('Tanda tangan wajib diisi');
                    isValid = false;
                }
            }
            
            return isValid;
        }
        
        // Data Management Functions
        function saveCurrentStepData() {
            const currentSection = document.querySelector(`.form-section[data-section="${currentStep}"]`);
            const formData = new FormData(currentSection);
            
            switch(currentStep) {
                case 1:
                    submissionData.personal_data = Object.fromEntries(formData);
                    break;
                case 2:
                    submissionData.financial_data = Object.fromEntries(formData);
                    break;
                case 3:
                    // Documents already saved in file upload handlers
                    break;
                case 4:
                    // Agreement handled in validation
                    break;
                case 5:
                    submissionData.signature_data = {
                        image: signaturePad.toDataURL(),
                        coordinates: {
                            x: 0,
                            y: 0
                        },
                        device_info: {
                            userAgent: navigator.userAgent,
                            timestamp: new Date().toISOString()
                        }
                    };
                    break;
            }
        }
        
        // Submit Registration
        async function submitRegistration() {
            if (!validateCurrentStep()) {
                return;
            }
            
            saveCurrentStepData();
            
            try {
                // Show loading
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengirim...';
                
                // Save draft first
                if (!submissionId) {
                    const draftResponse = await saveDraft();
                    if (!draftResponse.success) {
                        throw new Error(draftResponse.message);
                    }
                    submissionId = draftResponse.submission_id;
                    submissionToken = draftResponse.token;
                }
                
                // Submit registration
                const submitResponse = await submitRegistrationData();
                
                if (submitResponse.success) {
                    showSuccessModal();
                } else {
                    throw new Error(submitResponse.message);
                }
                
            } catch (error) {
                console.error('Error submitting registration:', error);
                alert('Terjadi kesalahan: ' + error.message);
                
                // Reset button
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Submit Pendaftaran';
            }
        }
        
        // API Functions
        async function saveDraft() {
            const response = await fetch('/api/registration_multi/draft', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    form_id: 1,
                    personal_data: submissionData.personal_data,
                    financial_data: submissionData.financial_data,
                    document_data: submissionData.document_data,
                    submission_id: submissionId
                })
            });
            
            return await response.json();
        }
        
        async function submitRegistrationData() {
            const response = await fetch('/api/registration_multi/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    submission_id: submissionId,
                    token: submissionToken,
                    personal_data: submissionData.personal_data,
                    financial_data: submissionData.financial_data,
                    document_data: submissionData.document_data,
                    signature_data: submissionData.signature_data,
                    photo_data: submissionData.document_data
                })
            });
            
            return await response.json();
        }
        
        // UI Functions
        function clearSignature() {
            signaturePad.clear();
        }
        
        function showSuccessModal() {
            const registrationNumber = 'POLREG-' + Date.now();
            document.getElementById('registrationNumber').textContent = registrationNumber;
            
            const modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
        }
    </script>
