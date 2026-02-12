// Formatter dan normalizer untuk angka, rupiah, tanggal, dan nomor telepon.
const defaultLocale = 'id-ID';

// ---------------- Tanggal Input Behavior ----------------
function toInputDate(value) {
  const date = value instanceof Date ? value : new Date(value);
  if (isNaN(date.getTime())) return '';
  const dd = String(date.getDate()).padStart(2, '0');
  const mm = String(date.getMonth() + 1).padStart(2, '0');
  const yyyy = date.getFullYear();
  return `${dd}/${mm}/${yyyy}`;
}

function fromInputDate(value) {
  if (!value) return null;
  const parts = value.split('/');
  if (parts.length !== 3) return null;
  const [dd, mm, yyyy] = parts.map((p) => parseInt(p, 10));
  if (!dd || !mm || !yyyy) return null;
  const date = new Date(yyyy, mm - 1, dd);
  return isNaN(date.getTime()) ? null : date;
}

export function attachDateInputBehavior(inputEl, { defaultValue = '' } = {}) {
  if (!inputEl) return;

  const setDisplay = (val) => {
    const date = val ? new Date(val) : null;
    inputEl.value = date ? formatDate(date) : '';
  };

  // Init display
  if (inputEl.value) {
    setDisplay(inputEl.value);
  } else if (defaultValue) {
    setDisplay(defaultValue);
  }

  inputEl.addEventListener('focus', () => {
    const date = fromDisplayDate(inputEl.value) || fromDisplayDate(defaultValue) || fromInputDate(inputEl.value) || null;
    inputEl.value = date ? toInputDate(date) : '';
  });

  inputEl.addEventListener('blur', () => {
    const date = fromInputDate(inputEl.value) || fromDisplayDate(inputEl.value);
    if (date) {
      inputEl.value = formatDate(date);
    } else if (defaultValue) {
      setDisplay(defaultValue);
    } else {
      inputEl.value = '';
    }
  });
}

function fromDisplayDate(value) {
  // Try parse dd MMM yyyy via Date
  if (!value) return null;
  const date = new Date(value);
  return isNaN(date.getTime()) ? null : date;
}

export function formatRupiah(value, options = {}) {
  const { locale = defaultLocale, minimumFractionDigits = 0, maximumFractionDigits = 0 } = options;
  const num = toNumberSafe(value);
  if (num === null) return '';
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits,
    maximumFractionDigits,
  }).format(num);
}

export function formatNumber(value, options = {}) {
  const { locale = defaultLocale, minimumFractionDigits = 0, maximumFractionDigits = 2 } = options;
  const num = toNumberSafe(value);
  if (num === null) return '';
  return new Intl.NumberFormat(locale, { minimumFractionDigits, maximumFractionDigits }).format(num);
}

export function toNumberSafe(value) {
  if (value === undefined || value === null || value === '') return null;
  const str = String(value).replace(/[^0-9.,-]/g, '').replace(',', '.');
  const num = Number(str);
  return Number.isFinite(num) ? num : null;
}

export function formatDate(value, options = {}) {
  const { locale = defaultLocale, withTime = false } = options;
  const date = value instanceof Date ? value : new Date(value);
  if (isNaN(date.getTime())) return '';
  return new Intl.DateTimeFormat(locale, {
    day: '2-digit', month: 'short', year: 'numeric',
    ...(withTime ? { hour: '2-digit', minute: '2-digit' } : {}),
  }).format(date);
}

// Nomor telepon: normalisasi dan validator longgar
export function normalizePhone(input) {
  if (!input) return '';
  const raw = String(input).trim();
  // Hapus karakter non-digit kecuali leading +
  const cleaned = raw.replace(/(?!^\+)[^0-9]/g, '');
  // Jika mulai dengan 0, ubah ke 62; jika sudah +62 atau 62 biarkan
  if (cleaned.startsWith('+62')) return cleaned;
  if (cleaned.startsWith('62')) return '+' + cleaned;
  if (cleaned.startsWith('0')) return '+62' + cleaned.slice(1);
  // fallback: tambahkan + jika belum ada
  return cleaned.startsWith('+') ? cleaned : '+' + cleaned;
}

export function isValidPhone(input) {
  const norm = normalizePhone(input);
  // Allow +62 diikuti 8-13 digit (umum untuk nomor seluler ID)
  return /^\+62\d{8,13}$/.test(norm);
}

// ---------------- Email & URL ----------------
export function normalizeEmail(input) {
  return String(input || '').trim().toLowerCase();
}

export function isValidEmail(input) {
  const email = normalizeEmail(input);
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

export function sanitizeUrl(input) {
  const url = String(input || '').trim();
  if (!url) return '';
  try {
    const u = new URL(url, window.location.origin);
    if (u.protocol === 'http:' || u.protocol === 'https:') return u.toString();
  } catch (e) {
    return '';
  }
  return '';
}

// ---------------- Identitas & nomor ----------------
export function normalizeNIK(input) {
  return String(input || '').replace(/\D/g, '').slice(0, 16);
}

export function isValidNIK(input) {
  const nik = normalizeNIK(input);
  return nik.length === 16;
}

export function normalizeNPWP(input) {
  return String(input || '').replace(/\D/g, '').slice(0, 15);
}

export function formatNPWP(input) {
  const digits = normalizeNPWP(input);
  // 99.999.999.9-999.999
  return digits.replace(/(\d{2})(\d{3})(\d{3})(\d{1})(\d{3})(\d{3})/, '$1.$2.$3.$4-$5.$6');
}

export function isValidNPWP(input) {
  return normalizeNPWP(input).length === 15;
}

export function normalizeRekening(input) {
  return String(input || '').replace(/\D/g, '').slice(0, 20);
}

export function isValidRekening(input) {
  const r = normalizeRekening(input);
  return r.length >= 8 && r.length <= 20;
}

export function normalizeKK(input) {
  return String(input || '').replace(/\D/g, '').slice(0, 16);
}

export function isValidKK(input) {
  return normalizeKK(input).length === 16;
}

export function normalizeSIM(input) {
  return String(input || '').replace(/\D/g, '').slice(0, 12);
}

export function isValidSIM(input) {
  const sim = normalizeSIM(input);
  return sim.length >= 12 && sim.length <= 12;
}

export function normalizePassport(input) {
  return String(input || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase().slice(0, 9);
}

export function isValidPassport(input) {
  const p = normalizePassport(input);
  return p.length >= 6 && p.length <= 9;
}

export function normalizePostalCode(input) {
  return String(input || '').replace(/\D/g, '').slice(0, 5);
}

export function isValidPostalCode(input) {
  return normalizePostalCode(input).length === 5;
}

// ---------------- Persentase ----------------
export function parsePercent(input) {
  if (input === null || input === undefined || input === '') return null;
  const str = String(input).replace('%', '').trim();
  const num = toNumberSafe(str);
  if (num === null) return null;
  return num >= 1 ? num / 100 : num; // jika user tulis 10 => 0.1
}

export function formatPercent(value, { decimals = 2 } = {}) {
  const num = typeof value === 'number' ? value : parsePercent(value);
  if (num === null) return '';
  return (num * 100).toFixed(decimals).replace(/\.0+$/, '') + '%';
}

// Angka lokal toleran (thousand separator campur)
export function parseLocalizedNumber(input) {
  if (input === null || input === undefined || input === '') return null;
  const str = String(input).trim().replace(/\s+/g, '');
  // jika ada koma dan titik, asumsikan titik ribuan, koma desimal
  let normalized = str;
  const hasComma = str.includes(',');
  const hasDot = str.includes('.');
  if (hasComma && hasDot) {
    normalized = str.replace(/\./g, '').replace(',', '.');
  } else if (hasComma && !hasDot) {
    normalized = str.replace(',', '.');
  }
  const num = Number(normalized.replace(/[^0-9.-]/g, ''));
  return Number.isFinite(num) ? num : null;
}

// ---------------- Time/Datetime behavior ----------------
export function attachTimeInputBehavior(inputEl, { defaultValue = '' } = {}) {
  if (!inputEl) return;
  const toInput = (val) => {
    const [h, m] = String(val || '').split(':');
    if (!h || !m) return '';
    return `${h.padStart(2, '0')}:${m.padStart(2, '0')}`;
  };
  const fromInput = (val) => {
    const [h, m] = String(val || '').split(':');
    const hh = parseInt(h, 10);
    const mm = parseInt(m, 10);
    if (Number.isNaN(hh) || Number.isNaN(mm)) return null;
    if (hh < 0 || hh > 23 || mm < 0 || mm > 59) return null;
    return `${hh.toString().padStart(2, '0')}:${mm.toString().padStart(2, '0')}`;
  };

  inputEl.value = toInput(defaultValue) || '';

  inputEl.addEventListener('focus', () => {
    inputEl.value = toInput(inputEl.value);
  });

  inputEl.addEventListener('blur', () => {
    const val = fromInput(inputEl.value);
    inputEl.value = val || toInput(defaultValue) || '';
  });
}

export function attachDateTimeInputBehavior(dateEl, timeEl, { defaultDate = '', defaultTime = '' } = {}) {
  // dateEl memakai attachDateInputBehavior, timeEl memakai attachTimeInputBehavior
  attachDateInputBehavior(dateEl, { defaultValue: defaultDate });
  attachTimeInputBehavior(timeEl, { defaultValue: defaultTime });
}

// ---------------- Zona waktu & durasi ----------------
export function formatDuration(ms) {
  if (!Number.isFinite(ms)) return '';
  const totalSeconds = Math.floor(ms / 1000);
  const h = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
  const m = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
  const s = (totalSeconds % 60).toString().padStart(2, '0');
  return `${h}:${m}:${s}`;
}

export function convertToUTC(date, offsetMinutes) {
  const d = date instanceof Date ? date : new Date(date);
  if (isNaN(d.getTime()) || !Number.isFinite(offsetMinutes)) return null;
  return new Date(d.getTime() - offsetMinutes * 60000);
}

export function convertFromUTC(date, offsetMinutes) {
  const d = date instanceof Date ? date : new Date(date);
  if (isNaN(d.getTime()) || !Number.isFinite(offsetMinutes)) return null;
  return new Date(d.getTime() + offsetMinutes * 60000);
}

// ---------------- RT/RW ----------------
export function normalizeRTRW(input, length = 2) {
  const digits = String(input || '').replace(/\D/g, '');
  return digits ? digits.padStart(length, '0').slice(-length) : '';
}

// ---------------- Code generator ----------------
export function generateCode(prefix = 'KOP', randLength = 4) {
  const now = new Date();
  const yyyy = now.getFullYear();
  const mm = String(now.getMonth() + 1).padStart(2, '0');
  const dd = String(now.getDate()).padStart(2, '0');
  const rand = Math.random().toString(36).substring(2, 2 + randLength).toUpperCase();
  return `${prefix}-${yyyy}${mm}${dd}-${rand}`;
}

export function slugify(text = '') {
  return String(text)
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-');
}

export function shortId(length = 6) {
  return Math.random().toString(36).substring(2, 2 + length).toUpperCase();
}

// ---------------- Sanitize & utility ----------------
export function escapeHTML(str = '') {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

export function debounce(fn, delay = 300) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), delay);
  };
}

// Placeholder: fetch CSRF token dari meta/tag jika diset
export function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

// Input mask helper (opsional; gunakan dengan inputmask jika tersedia)
export function applyPhoneMask(inputEl) {
  if (!inputEl || typeof Inputmask === 'undefined') return;
  Inputmask({ mask: '+62999-9999-99999', greedy: false }).mask(inputEl);
}

// ---------------- File upload pre-check ----------------
export function validateFileMeta(file, { maxSizeMB = 5, allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'] } = {}) {
  if (!file) return { ok: false, error: 'File tidak ditemukan' };
  const sizeOk = file.size <= maxSizeMB * 1024 * 1024;
  const typeOk = allowedTypes.includes(file.type);
  const errors = [];
  if (!sizeOk) errors.push(`Ukuran maksimal ${maxSizeMB}MB`);
  if (!typeOk) errors.push(`Tipe harus ${allowedTypes.join(', ')}`);
  return { ok: sizeOk && typeOk, error: errors.join('; ') };
}

// ---------------- Nama & alamat ----------------
export function titleCaseName(text = '') {
  if (!text) return '';
  const parts = text.toLowerCase().split(/\s+/);
  const keepUpper = ['bin', 'binti', 'al', 'de', 'van'];
  return parts
    .map((w) => (keepUpper.includes(w) ? w : w.charAt(0).toUpperCase() + w.slice(1)))
    .join(' ');
}

export function titleCaseAddress(text = '') {
  if (!text) return '';
  const lowerWords = ['dan', 'di', 'ke', 'dari', 'rt', 'rw'];
  return text
    .toLowerCase()
    .split(/\s+/)
    .map((w) => (lowerWords.includes(w) ? w : w.charAt(0).toUpperCase() + w.slice(1)))
    .join(' ');
}

// ---------------- Virtual Account ----------------
export function normalizeVA(input) {
  return String(input || '').replace(/\D/g, '').slice(0, 25);
}

export function isValidVA(input) {
  const v = normalizeVA(input);
  return v.length >= 8 && v.length <= 25;
}

// ---------------- Angka/Nominal Input Behavior ----------------
export function attachNumberInputBehavior(inputEl, { defaultValue = 0, decimals = 0, mode = 'number' } = {}) {
  if (!inputEl) return;

  const formatter = mode === 'currency'
    ? (v) => formatRupiah(v, { minimumFractionDigits: decimals, maximumFractionDigits: decimals })
    : (v) => formatNumber(v, { minimumFractionDigits: decimals, maximumFractionDigits: decimals });

  const setDisplay = (val) => {
    inputEl.value = formatter(val);
  };

  // Init display
  setDisplay(defaultValue);

  inputEl.addEventListener('focus', () => {
    const num = toNumberSafe(inputEl.value);
    if (num === null || num === 0) {
      inputEl.value = '';
    } else {
      inputEl.value = num.toString().replace('.', decimals > 0 ? '.' : '.');
    }
  });

  inputEl.addEventListener('input', () => {
    const num = toNumberSafe(inputEl.value);
    if (num === null) {
      inputEl.value = '';
      return;
    }
    inputEl.value = formatter(num);
  });

  inputEl.addEventListener('blur', () => {
    const num = toNumberSafe(inputEl.value);
    if (num === null) {
      setDisplay(defaultValue);
      return;
    }
    setDisplay(num);
  });
}

// ---------------- Sentence Case untuk detail alamat ----------------
export function sentenceCase(text = '') {
  if (!text) return '';
  const trimmed = text.trim();
  return trimmed.charAt(0).toUpperCase() + trimmed.slice(1);
}

export function attachSentenceCaseBehavior(inputEl) {
  if (!inputEl) return;
  const apply = () => {
    inputEl.value = sentenceCase(inputEl.value);
  };
  inputEl.addEventListener('change', apply);
  inputEl.addEventListener('blur', apply);
}
