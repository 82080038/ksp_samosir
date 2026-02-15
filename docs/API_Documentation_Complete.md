# API Documentation — KSP Samosir

## Base URL

```
http://localhost/ksp_samosir/api/
```

## Authentication

Semua API endpoint memerlukan session cookie (`PHPSESSID`) yang valid. Login terlebih dahulu melalui web interface.

## Endpoints

### Address API (`api/address/`)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/address/?action=provinces` | Daftar provinsi |
| GET | `/api/address/?action=regencies&province_id={id}` | Kabupaten/kota per provinsi |
| GET | `/api/address/?action=districts&regency_id={id}` | Kecamatan per kabupaten |
| GET | `/api/address/?action=villages&district_id={id}` | Kelurahan per kecamatan |

### Admin API (`api/admin/`)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/admin/?action=stats` | Statistik dashboard admin |
| GET | `/api/admin/?action=users` | Daftar user |

### Koperasi API (`api/koperasi/`)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/koperasi/?action=jenis` | Daftar jenis koperasi |
| GET | `/api/koperasi/?action=moduls` | Daftar modul per jenis |
| GET | `/api/koperasi/?action=unit` | Daftar unit koperasi |

### Agricultural API (`api/agricultural/`)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/agricultural/?action=dashboard` | Dashboard pertanian |
| GET | `/api/agricultural/?action=lahan` | Data lahan |

### Multi Database API (`api/multi_database.php`)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/multi_database.php?action=stats` | Statistik multi-database |
| GET | `/api/multi_database.php?action=people` | Data people cross-DB |

### AJAX Helper (`api/ajax.php`)

Generic AJAX endpoint untuk operasi CRUD dari frontend.

| Method | Endpoint | Deskripsi |
|---|---|---|
| POST | `/api/ajax.php` | Generic CRUD operations |

#### Parameter POST:

```json
{
  "action": "create|read|update|delete",
  "table": "nama_tabel",
  "data": { ... }
}
```

## Response Format

Semua endpoint mengembalikan JSON:

```json
{
  "success": true,
  "message": "Operation successful",
  "data": [ ... ]
}
```

Error response:

```json
{
  "success": false,
  "message": "Error description",
  "data": null
}
```

## OpenAPI Spec

Lihat `docs/openapi.yaml` untuk spesifikasi lengkap.
