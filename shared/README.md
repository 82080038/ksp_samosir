# Shared Components (Komponen Universal)

Folder ini disiapkan untuk menampung komponen yang dapat digunakan lintas modul/aplikasi, dengan fokus awal pada blok alamat (provinsi/kabupaten/kecamatan/kelurahan) dan utilitas umum.

## Tujuan
- Single source of truth untuk komponen frontend & backend yang reusable.
- Meminimalkan duplikasi logic (dropdown alamat, validasi hirarki, formatting alamat, caching).
- Mempermudah dokumentasi dan adopsi oleh seluruh tim/fitur.

## Rencana Konten Awal
- `js/` : modul JS reusable (contoh: `address_cascade.js`, helper cache, formatter).
- `php/`: helper/partial PHP (contoh: `address_component.php` untuk render blok form, validator umum).
- `docs/`: panduan singkat penggunaan komponen.

## Struktur Direktori yang Disarankan
- `js/`
  - `address_cascade.js` — init dropdown berjenjang (provinsi → kabupaten → kecamatan → kelurahan) dengan opsi selector & event hook.
  - `address_cache.js` — utilitas cache localStorage (ikut aturan kapasitas & validasi data).
  - `address_formatter.js` — formatter alamat (singkat/panjang) dengan fallback data kosong.
  - `fetch_wrapper.js` — fetch helper dengan timeout, JSON parsing, dan error handling konsisten.
  - `validation.js` — validator frontend (required/email/numeric/length) + helper tampilkan error.
  - `formatters.js` — formatter angka/rupiah/tanggal + normalizer/validator nomor telepon.
- `php/`
  - `address_component.php` — partial form blok alamat (HTML + class/ID default + helper error state).
  - `address_helper.php` — wrapper helper untuk validasi hirarki alamat di backend.
  - `response.php` — helper JSON success/error + wrapper `respond()`.
  - `auth_guard.php` — guard login + role check (`require_login`, `require_role`).
  - `validation.php` — sanitizer + validator backend.
  - `formatters.php` — formatter angka/rupiah/tanggal + normalizer/validator nomor telepon.
- `docs/`
  - `address.md` — panduan konsumsi komponen JS/PHP, contoh integrasi.

## Panduan Singkat Komponen Baru
- **PHP response** (`shared/php/response.php`)
  - Gunakan `respond(fn() => [...])` di endpoint untuk auto success/error.
  - Manual: `json_success($data);`, `json_error('pesan', 400);`.

- **Auth guard** (`shared/php/auth_guard.php`)
  - Panggil `require_login();` di awal halaman/API.
  - Role: `require_role(['admin', 'superadmin']);` menggunakan `$_SESSION['user']['roles']`.

- **Backend validation** (`shared/php/validation.php`)
  - `collect_errors([ 'email' => ['label' => 'Email', 'value' => $_POST['email'], 'rules' => ['required', 'email']] ]);`
  - Return array error; gabungkan dengan `json_error` jika ada.

- **Fetch wrapper** (`shared/js/fetch_wrapper.js`)
  - `import { apiRequest, buildQuery } from '/shared/js/fetch_wrapper.js';`
  - Contoh: `const data = await apiRequest(buildQuery('/public/api/anggota.php', { page: 1 }));`

- **Frontend validation** (`shared/js/validation.js`)
  - `import { validate, showErrors, clearErrors } from '/shared/js/validation.js';`
  - Contoh: `const errors = validate({ email: { value: emailInput.value, label: 'Email', rules: ['required','email'] } });`

- **Formatters JS** (`shared/js/formatters.js`)
  - `formatRupiah(15000)` → `Rp15.000` (Intl, locale id-ID).
  - `formatNumber(1234.5, { maximumFractionDigits: 1 })` → `1.234,5`.
  - `formatDate('2024-01-02', { withTime: false })` → `02 Jan 2024`.
  - `normalizePhone('0812-3456-7890')` → `+6281234567890`; `isValidPhone(...)`.
  - Email/URL: `normalizeEmail/isValidEmail`, `sanitizeUrl`.
  - Input tanggal: `attachDateInputBehavior(inputEl, { defaultValue })` → saat focus format `dd/mm/yyyy`, saat blur tampil `dd MMM yyyy`.
  - Input angka/nominal: `attachNumberInputBehavior(inputEl, { defaultValue: 0, decimals: 0, mode: 'currency'|'number' })` → focus kosongkan jika 0, ketik langsung diformat, blur kembali ke format rupiah/angka.
  - Sentence case detail alamat: `attachSentenceCaseBehavior(inputEl)` agar huruf pertama kapital pada change/blur.
  - Identitas: `normalizeNIK/isValidNIK`, `normalizeNPWP/formatNPWP/isValidNPWP`, `normalizeRekening/isValidRekening`.
  - KK/SIM/Paspor/Kode Pos: `normalizeKK/isValidKK`, `normalizeSIM/isValidSIM`, `normalizePassport/isValidPassport`, `normalizePostalCode/isValidPostalCode`.
  - Persentase: `parsePercent`, `formatPercent`.
  - Angka lokal toleran: `parseLocalizedNumber`.
  - Waktu: `attachTimeInputBehavior`, `attachDateTimeInputBehavior(dateEl, timeEl)`.
  - Zona/durasi: `formatDuration`, `convertToUTC`, `convertFromUTC`.
  - RT/RW: `normalizeRTRW('1')` → `01`.
  - Kode: `generateCode('KOP', 4)` → `KOP-20260211-ABCD`.
  - Slug/ID: `slugify`, `shortId`.
  - File upload: `validateFileMeta(file, { maxSizeMB, allowedTypes })`.
  - Nama/alamat: `titleCaseName`, `titleCaseAddress`.
  - Virtual Account: `normalizeVA/isValidVA`.
  - Sanitize & util: `escapeHTML`, `debounce`, `getCsrfToken` (placeholder meta), `applyPhoneMask` (butuh Inputmask).

- **Formatters PHP** (`shared/php/formatters.php`)
  - `format_rupiah(15000)` → `Rp15.000`; `format_number(1234.5, 1)` → `1.234,5`.
  - `format_date_id('2024-01-02 13:45', true)` → `02 Jan 2024 13:45`.
  - `normalize_phone('0812-3456-7890')` → `+6281234567890`; `is_valid_phone(...)`.
  - Email/URL: `normalize_email/is_valid_email`, `sanitize_url`.
  - Identitas: `normalize_nik/is_valid_nik`, `normalize_npwp/format_npwp/is_valid_npwp`, `normalize_rekening/is_valid_rekening`.
  - KK/SIM/Paspor/Kode Pos: `normalize_kk/is_valid_kk`, `normalize_sim/is_valid_sim`, `normalize_passport/is_valid_passport`, `normalize_postal_code/is_valid_postal_code`.
  - Persentase: `parse_percent`, `format_percent`.
  - Angka lokal toleran: `parse_localized_number`.
  - RT/RW: `normalize_rt_rw('1')` → `01`.
  - Kode: `generate_code('KOP', 4)`.
  - Slug/ID: `slugify`, `short_id`.
  - File upload meta: `validate_file_meta($file, [...])`.
  - Nama/alamat: `title_case_name`, `title_case_address`.
  - Virtual Account: `normalize_va/is_valid_va`.
  - (Sanitasi sederhana) gunakan `htmlspecialchars` standar untuk output; CSRF token disesuaikan dengan stack.

## Panduan Penggunaan Komponen Alamat (draft)
1) Sertakan modul JS (prefer ES module)
```html
<script type="module">
  import { initAddressCascade } from '/shared/js/address_cascade.js';
  initAddressCascade({
    selectors: {
      province: '#provinsi',
      regency: '#kabupaten',
      district: '#kecamatan',
      village: '#kelurahan'
    },
    apiBase: '/public/api', // bisa di-override per environment
    onChange: { afterLoad: (level, data) => console.debug('loaded', level, data) }
  });
</script>
```

2) Include partial PHP (opsional)
```php
<?php include __DIR__ . '/php/address_component.php'; ?>
```

3) Validasi backend
- Gunakan helper `Address::validateHierarchy($provinceId, $regencyId, $districtId, $villageId)` atau wrapper baru di `shared/php/address_helper.php`.
- Pastikan input disanitasi & gunakan prepared statements (ikuti pola di `public/api/*`).

## Standar Konfigurasi
- **API base path**: default `/public/api` dengan endpoint `provinces.php`, `regencies.php`, `districts.php`, `villages.php`.
- **Nama param**: `province_id`, `regency_id`, `district_id`, `village_id` (camelCase di frontend diperbolehkan, mapping di JS).
- **Caching**: gunakan kapasitas maksimal 80% localStorage, validasi data sebelum simpan, fallback ke network jika cache invalid.
- **Styling**: minimal; expose class hook `form-control` + `is-invalid` agar mudah di-branding per halaman.

## Checklist Implementasi
- [ ] Ekstrak logic dari `public/assets/js/register_koperasi.js` ke `shared/js/address_cascade.js` dengan opsi konfigurasi selector dan base path API.
- [ ] Tambah utilitas cache ke `shared/js/address_cache.js` (ikuti policy kapasitas & validasi).
- [ ] Buat partial `shared/php/address_component.php` + helper backend.
- [ ] Tulis `shared/docs/address.md` (cara pakai JS, partial PHP, validasi backend, contoh integrasi).
- [ ] Tambah contoh integrasi di halaman existing (register koperasi / superadmin modal) sebagai referensi.
- [ ] Tambah contoh pemakaian formatters di API + frontend (rupiah, tanggal, telepon).
- [ ] Tambah snippet penggunaan `attachDateInputBehavior` dan `attachNumberInputBehavior` di form nyata.
- [ ] Tambah snippet penggunaan identitas/NPWP/rekening/persentase/time/RT-RW di form nyata.
- [ ] Tambah snippet penggunaan email/URL, KK/SIM/paspor/kode pos, slug/ID, file upload meta, VA di form nyata.

## Prinsip
1. Konsisten dengan API dan schema alamat yang distandarkan.
2. Tidak bergantung pada halaman spesifik; gunakan konfigurasi/props.
3. Terkapsulasi: styling minimal, ekspos hook untuk override.
4. Dokumentasi wajib dalam setiap modul.

## Next Step (alamat)
- Ekstrak cascade dropdown dari `public/assets/js/register_koperasi.js` menjadi `shared/js/address_cascade.js` dengan opsi konfigurasi selector dan base path API.
- Buat partial PHP untuk blok form alamat yang bisa di-include lintas halaman.
- Sambungkan validasi backend ke helper yang sama (`app/Address.php` atau wrapper baru di `shared/php`).
