// Fetch wrapper sederhana dengan default JSON dan error handling konsisten.
export async function apiRequest(url, options = {}) {
  const {
    method = 'GET',
    headers = {},
    body,
    responseType = 'json',
    timeout = 15000,
  } = options;

  const controller = new AbortController();
  const id = setTimeout(() => controller.abort(), timeout);

  const mergedHeaders = {
    'Accept': 'application/json',
    ...(body && !(body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}),
    ...headers,
  };

  const resp = await fetch(url, {
    method,
    headers: mergedHeaders,
    body: body && !(body instanceof FormData) ? JSON.stringify(body) : body,
    signal: controller.signal,
    credentials: 'same-origin',
  }).finally(() => clearTimeout(id));

  let parsed;
  if (responseType === 'json') {
    parsed = await resp.json().catch(() => ({ success: false, error: { message: 'Invalid JSON' } }));
  } else {
    parsed = await resp.text();
  }

  if (!resp.ok || (parsed && parsed.success === false)) {
    const message = parsed?.error?.message || `Request failed (${resp.status})`;
    throw new Error(message);
  }

  return parsed?.data ?? parsed;
}

// Helper untuk GET dengan query params sederhana
export function buildQuery(url, params = {}) {
  const qs = new URLSearchParams();
  Object.entries(params).forEach(([k, v]) => {
    if (v !== undefined && v !== null && v !== '') qs.append(k, v);
  });
  const glue = url.includes('?') ? '&' : '?';
  const query = qs.toString();
  return query ? `${url}${glue}${query}` : url;
}
