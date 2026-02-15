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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="registration">Registrasi Anggota</h1>
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
                <div class="text-center">
                    <h3 class="mb-2"><i class="fas fa-users me-2"></i>Pendaftaran Anggota Baru</h3>
                    <p class="mb-0">Koperasi Simpan Pinjam Samosir</p>
                </div>
            </div>
            
            <div class="p-4">
                <!-- Progress Indicator -->
                <div class="progress-bar-custom mb-4">
                    <div class="progress-fill" id="progressBar" style="width: 20%"></div>
                </div>
                
                <!-- Step Indicator -->
                <div class="step-indicator mb-4">
                    <div class="step active" data-step="1">
                        <div class="step-circle">1</div>
                        <small>Data Pribadi</small>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-circle">2</div>
                        <small>Alamat</small>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-circle">3</div>
                        <small>Data Keuangan</small>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-circle">4</div>
                        <small>Dokumen</small>
                    </div>
                    <div class="step" data-step="5">
                        <div class="step-circle">5</div>
                        <small>Tanda Tangan</small>
                    </div>
                </div>
                
                <!-- Form Sections -->
                <form id="registrationForm">
                    <!-- Step 1: Data Pribadi -->
                    <div class="form-section active" data-section="1">
                        <h5 class="mb-4"><i class="fas fa-user me-2"></i>Data Pribadi</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label required-field">Tempat Lahir</label>
                                <input type="text" class="form-control" name="tempat_lahir" required>
                            </div>
                            <div class="col-md-3 mb-3">
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
                                <label class="form-label required-field">Status Perkawinan</label>
                                <select class="form-select" name="status_perkawinan" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Belum Kawin">Belum Kawin</option>
                                    <option value="Kawin">Kawin</option>
                                    <option value="Cerai">Cerai</option>
                                    <option value="Janda/Duda">Janda/Duda</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Agama</label>
                                <select class="form-select" name="agama" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Budha">Budha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Pendidikan Terakhir</label>
                                <select class="form-select" name="pendidikan" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="SD">SD</option>
                                    <option value="SMP">SMP</option>
                                    <option value="SMA">SMA</option>
                                    <option value="D1">D1</option>
                                    <option value="D2">D2</option>
                                    <option value="D3">D3</option>
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Pekerjaan</label>
                                <input type="text" class="form-control" name="pekerjaan" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">No. KTP</label>
                                <input type="text" class="form-control" name="no_ktp" maxlength="16" pattern="[0-9]{16}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">No. HP</label>
                                <input type="tel" class="form-control" name="no_hp" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 2: Alamat -->
                    <div class="form-section" data-section="2">
                        <h5 class="mb-4"><i class="fas fa-map-marker-alt me-2"></i>Alamat</h5>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label required-field">Alamat Lengkap</label>
                                <textarea class="form-control" name="alamat_lengkap" rows="3" required></textarea>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label required-field">RT</label>
                                <input type="text" class="form-control" name="rt" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label required-field">RW</label>
                                <input type="text" class="form-control" name="rw" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Kelurahan</label>
                                <input type="text" class="form-control" name="kelurahan" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Kecamatan</label>
                                <input type="text" class="form-control" name="kecamatan" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Kabupaten/Kota</label>
                                <input type="text" class="form-control" name="kabupaten" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Provinsi</label>
                                <select class="form-select" name="provinsi" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Aceh">Aceh</option>
                                    <option value="Sumatera Utara">Sumatera Utara</option>
                                    <option value="Sumatera Barat">Sumatera Barat</option>
                                    <option value="Sumatera Selatan">Sumatera Selatan</option>
                                    <option value="Riau">Riau</option>
                                    <option value="Kepulauan Riau">Kepulauan Riau</option>
                                    <option value="Jambi">Jambi</option>
                                    <option value="Bengkulu">Bengkulu</option>
                                    <option value="Lampung">Lampung</option>
                                    <option value="DKI Jakarta">DKI Jakarta</option>
                                    <option value="Jawa Barat">Jawa Barat</option>
                                    <option value="Jawa Tengah">Jawa Tengah</option>
                                    <option value="DI Yogyakarta">DI Yogyakarta</option>
                                    <option value="Jawa Timur">Jawa Timur</option>
                                    <option value="Banten">Banten</option>
                                    <option value="Bali">Bali</option>
                                    <option value="Nusa Tenggara Barat">Nusa Tenggara Barat</option>
                                    <option value="Nusa Tenggara Timur">Nusa Tenggara Timur</option>
                                    <option value="Kalimantan Barat">Kalimantan Barat</option>
                                    <option value="Kalimantan Tengah">Kalimantan Tengah</option>
                                    <option value="Kalimantan Selatan">Kalimantan Selatan</option>
                                    <option value="Kalimantan Timur">Kalimantan Timur</option>
                                    <option value="Kalimantan Utara">Kalimantan Utara</option>
                                    <option value="Sulawesi Utara">Sulawesi Utara</option>
                                    <option value="Sulawesi Tengah">Sulawesi Tengah</option>
                                    <option value="Sulawesi Selatan">Sulawesi Selatan</option>
                                    <option value="Sulawesi Tenggara">Sulawesi Tenggara</option>
                                    <option value="Gorontalo">Gorontalo</option>
                                    <option value="Sulawesi Barat">Sulawesi Barat</option>
                                    <option value="Maluku">Maluku</option>
                                    <option value="Maluku Utara">Maluku Utara</option>
                                    <option value="Papua">Papua</option>
                                    <option value="Papua Barat">Papua Barat</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label required-field">Kode Pos</label>
                                <input type="text" class="form-control" name="kode_pos" maxlength="5" pattern="[0-9]{5}" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 3: Data Keuangan -->
                    <div class="form-section" data-section="3">
                        <h5 class="mb-4"><i class="fas fa-wallet me-2"></i>Data Keuangan</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Penghasilan per Bulan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="penghasilan" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Sumber Penghasilan</label>
                                <input type="text" class="form-control" name="sumber_penghasilan" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Nama Bank</label>
                                <select class="form-select" name="nama_bank" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="BCA">BCA</option>
                                    <option value="BNI">BNI</option>
                                    <option value="BRI">BRI</option>
                                    <option value="Mandiri">Mandiri</option>
                                    <option value="Danamon">Danamon</option>
                                    <option value="CIMB Niaga">CIMB Niaga</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">No. Rekening</label>
                                <input type="text" class="form-control" name="no_rekening" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required-field">Atas Nama Rekening</label>
                                <input type="text" class="form-control" name="atas_nama_rekening" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Simpanan Pokok</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="simpanan_pokok" value="100000" min="0" required>
                                </div>
                                <small class="text-muted">Minimal Rp 100.000</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Simpanan Wajib per Bulan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="simpanan_wajib" value="50000" min="0" required>
                                </div>
                                <small class="text-muted">Minimal Rp 50.000 per bulan</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 4: Dokumen -->
                    <div class="form-section" data-section="4">
                        <h5 class="mb-4"><i class="fas fa-file-alt me-2"></i>Dokumen</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Foto Diri</label>
                                <div class="photo-preview" onclick="document.getElementById('photoInput').click()">
                                    <div id="photoPlaceholder">
                                        <i class="fas fa-camera fa-2x text-muted"></i>
                                        <p class="mb-0 mt-2 text-muted">Klik untuk upload foto</p>
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
                    
                    <!-- Step 5: Tanda Tangan Digital -->
                    <div class="form-section" data-section="5">
                        <h5 class="mb-4"><i class="fas fa-signature me-2"></i>Tanda Tangan Digital</h5>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label required-field">Tanda Tangan</label>
                                <canvas id="signaturePad" class="signature-pad" width="760" height="200"></canvas>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSignature()">
                                        <i class="fas fa-eraser me-1"></i>Hapus
                                    </button>
                                    <small class="text-muted ms-2">Gunakan mouse atau touch untuk tanda tangan</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Pernyataan</h6>
                                    <p class="mb-0">Saya yang bertanda tangan di bawah ini menyatakan bahwa data yang saya isi adalah benar dan bersedia mematuhi semua peraturan koperasi.</p>
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
            address_data: {},
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
                const response = await fetch('/api/registration/form');
                const data = await response.json();
                
                if (data.success) {
                    // Form loaded successfully
                    console.log('Form loaded:', data.data);
                }
            } catch (error) {
                console.error('Error loading form:', error);
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
            }
            
            if (currentStep === 2) {
                const kodePos = document.querySelector('input[name="kode_pos"]');
                if (kodePos.value && kodePos.value.length !== 5) {
                    kodePos.classList.add('is-invalid');
                    isValid = false;
                    alert('Kode pos harus 5 digit');
                }
            }
            
            if (currentStep === 4) {
                if (!submissionData.document_data.photo) {
                    alert('Foto diri wajib diupload');
                    isValid = false;
                }
                if (!submissionData.document_data.ktp) {
                    alert('Scan KTP wajib diupload');
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
                    submissionData.address_data = Object.fromEntries(formData);
                    break;
                case 3:
                    submissionData.financial_data = Object.fromEntries(formData);
                    break;
                case 4:
                    // Documents already saved in file upload handlers
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
            const response = await fetch('/api/registration/draft', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    form_id: 1,
                    personal_data: submissionData.personal_data,
                    address_data: submissionData.address_data,
                    financial_data: submissionData.financial_data,
                    document_data: submissionData.document_data
                })
            });
            
            return await response.json();
        }
        
        async function submitRegistrationData() {
            const response = await fetch('/api/registration/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    submission_id: submissionId,
                    token: submissionToken,
                    personal_data: submissionData.personal_data,
                    address_data: submissionData.address_data,
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
            const registrationNumber = 'REG-' + Date.now();
            document.getElementById('registrationNumber').textContent = registrationNumber;
            
            const modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
        }
    </script>
