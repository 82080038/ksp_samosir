# Shared Libraries — KSP Samosir

Direktori ini berisi kode PHP dan JavaScript yang dipakai bersama oleh berbagai modul.

## PHP (`shared/php/`)

| File | Fungsi |
|---|---|
| `formatters.php` | Format rupiah, tanggal, angka |
| `audit_compliance_monitoring.php` | Audit trail & compliance helpers |
| `ai_credit_scoring.php` | AI credit scoring logic |
| `risk_management.php` | Risk assessment helpers |
| `notification_system.php` | Notification dispatch |
| `digital_document_management.php` | Document upload/management |
| `koperasi_*.php` | Koperasi-specific business logic |

## JavaScript (`shared/js/`)

| File | Fungsi |
|---|---|
| `fetch_wrapper.js` | Fetch API wrapper dengan error handling |
| `formatters.js` | Format currency, date di frontend |
| `validation.js` | Client-side form validation |

> **Catatan**: Library UI utama ada di `assets/js/ksp-ui.js` (bukan di shared).
