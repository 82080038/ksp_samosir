// Validator ringan untuk form frontend.
export const validators = {
  required: (v) => v !== undefined && v !== null && String(v).trim() !== '',
  email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v || '').trim()),
  numeric: (v) => /^-?\d+(\.\d+)?$/.test(String(v || '').trim()),
  length: (v, min = 0, max = Infinity) => {
    const len = String(v || '').length;
    return len >= min && len <= max;
  },
};

export function validate(fields) {
  // fields: { fieldName: { value, label, rules: [ ['required'], ['email'], ['length', 3, 50] ] } }
  const errors = {};
  Object.entries(fields).forEach(([field, config]) => {
    const { value, label = field, rules = [] } = config;
    for (const rule of rules) {
      const [name, ...args] = Array.isArray(rule) ? rule : [rule];
      const fn = validators[name];
      if (!fn) continue;
      const ok = fn(value, ...args);
      if (!ok) {
        errors[field] = `${label} tidak valid`;
        break;
      }
    }
  });
  return errors;
}

export function showErrors(formEl, errors = {}) {
  // Asumsi input pakai name attribute
  Object.entries(errors).forEach(([field, msg]) => {
    const input = formEl.querySelector(`[name="${field}"]`);
    if (!input) return;
    input.classList.add('is-invalid');
    let feedback = input.parentElement?.querySelector('.invalid-feedback');
    if (!feedback) {
      feedback = document.createElement('div');
      feedback.className = 'invalid-feedback';
      input.parentElement.appendChild(feedback);
    }
    feedback.textContent = msg;
  });
}

export function clearErrors(formEl) {
  formEl.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
  formEl.querySelectorAll('.invalid-feedback').forEach((el) => (el.textContent = ''));
}
