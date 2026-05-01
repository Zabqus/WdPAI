/* =============================================
   SHARE PLANNER — api.js
   Fetch API helpers (placeholder for Phase 2)
   ============================================= */

const Api = (function () {

    const BASE = '';

    async function request(method, endpoint, data = null) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const opts = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(method !== 'GET' && csrfToken ? { 'X-CSRF-Token': csrfToken } : {}),
            },
        };
        if (data) opts.body = JSON.stringify(data);

        const res = await fetch(BASE + endpoint, opts);
        if (!res.ok) {
            const err = await res.json().catch(() => ({ message: res.statusText }));
            throw new Error(err.message || `HTTP ${res.status}`);
        }
        return res.json();
    }

    return {
        get:    (ep)       => request('GET',    ep),
        post:   (ep, data) => request('POST',   ep, data),
        put:    (ep, data) => request('PUT',    ep, data),
        delete: (ep)       => request('DELETE', ep),
    };

})();
