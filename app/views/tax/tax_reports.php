<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tax Reports</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print All Reports
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportReports()">
                <i class="bi bi-download"></i> Export Reports
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Generate New Report
        </button>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Laporan Pajak</strong> - Ringkasan lengkap semua laporan pajak yang telah dibuat dan disampaikan.
</div>

<!-- Report Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Filter Laporan</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label for="report-type" class="form-label">Jenis Laporan</label>
                <select class="form-select" id="report-type">
                    <option value="">Semua Jenis</option>
                    <option value="pph21">PPh 21</option>
                    <option value="pph23">PPh 23</option>
                    <option value="pph25">PPh 25</option>
                    <option value="annual">Spt Tahunan</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="report-year" class="form-label">Tahun</label>
                <select class="form-select" id="report-year">
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="report-status" class="form-label">Status</label>
                <select class="form-select" id="report-status">
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Submitted</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-primary" onclick="filterReports()">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tax Reports Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Daftar Laporan Pajak</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No. Laporan</th>
                        <th>Jenis Pajak</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Jumlah</th>
                        <th>Tanggal Submit</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="reports-table-body">
                    <!-- Sample data - in real implementation this would be populated from database -->
                    <tr>
                        <td>LAP-PPH21-2026-001</td>
                        <td>
                            <span class="badge bg-primary">PPh 21</span>
                        </td>
                        <td>Januari 2026</td>
                        <td>
                            <span class="badge bg-success">Submitted</span>
                        </td>
                        <td>Rp 15,750,000</td>
                        <td>2026-02-10</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewReport('LAP-PPH21-2026-001')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="downloadReport('LAP-PPH21-2026-001')">
                                    <i class="bi bi-download"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteReport('LAP-PPH21-2026-001')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>LAP-PPH23-2026-001</td>
                        <td>
                            <span class="badge bg-info">PPh 23</span>
                        </td>
                        <td>Januari 2026</td>
                        <td>
                            <span class="badge bg-success">Submitted</span>
                        </td>
                        <td>Rp 8,500,000</td>
                        <td>2026-02-12</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewReport('LAP-PPH23-2026-001')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="downloadReport('LAP-PPH23-2026-001')">
                                    <i class="bi bi-download"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteReport('LAP-PPH23-2026-001')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>LAP-PPH25-2026-001</td>
                        <td>
                            <span class="badge bg-warning">PPh 25</span>
                        </td>
                        <td>Januari 2026</td>
                        <td>
                            <span class="badge bg-warning">Draft</span>
                        </td>
                        <td>Rp 22,000,000</td>
                        <td>-</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewReport('LAP-PPH25-2026-001')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="submitReport('LAP-PPH25-2026-001')">
                                    <i class="bi bi-send"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteReport('LAP-PPH25-2026-001')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>LAP-TAHUNAN-2025-001</td>
                        <td>
                            <span class="badge bg-secondary">SPT Tahunan</span>
                        </td>
                        <td>2025</td>
                        <td>
                            <span class="badge bg-success">Approved</span>
                        </td>
                        <td>Rp 45,200,000</td>
                        <td>2026-01-15</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewReport('LAP-TAHUNAN-2025-001')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="downloadReport('LAP-TAHUNAN-2025-001')">
                                    <i class="bi bi-download"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Report Summary Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h1 text-primary mb-2">12</div>
                <div class="text-muted">Total Laporan</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h1 text-success mb-2">8</div>
                <div class="text-muted">Submitted</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h1 text-warning mb-2">3</div>
                <div class="text-muted">Draft</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h1 text-info mb-2">Rp 91.45M</div>
                <div class="text-muted">Total Pajak</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any interactive features
    console.log('Tax reports page loaded');

    // Add event listeners for filter buttons
    const filterBtn = document.querySelector('button[onclick="filterReports()"]');
    if (filterBtn) {
        filterBtn.addEventListener('click', function() {
            // In real implementation, this would filter the table
            console.log('Filtering reports...');
        });
    }
});

function filterReports() {
    const type = document.getElementById('report-type').value;
    const year = document.getElementById('report-year').value;
    const status = document.getElementById('report-status').value;

    console.log('Filtering by:', { type, year, status });
    // In real implementation, this would filter the table rows
}

function viewReport(reportId) {
    console.log('Viewing report:', reportId);
    // In real implementation, this would open report details modal
    alert('View report functionality will be implemented');
}

function downloadReport(reportId) {
    console.log('Downloading report:', reportId);
    // In real implementation, this would download the report file
    alert('Download functionality will be implemented');
}

function deleteReport(reportId) {
    if (confirm('Apakah Anda yakin ingin menghapus laporan ini?')) {
        console.log('Deleting report:', reportId);
        // In real implementation, this would delete the report
        alert('Delete functionality will be implemented');
    }
}

function submitReport(reportId) {
    if (confirm('Submit laporan ke DJP?')) {
        console.log('Submitting report:', reportId);
        // In real implementation, this would submit to tax authority
        alert('Submit functionality will be implemented');
    }
}

function exportReports() {
    console.log('Exporting all reports...');
    // In real implementation, this would export all reports to Excel/PDF
    alert('Export functionality will be implemented');
}
</script>
