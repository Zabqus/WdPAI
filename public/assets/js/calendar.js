/* =============================================
   SYNCU — calendar.js
   Dynamic event loading via Fetch API
   ============================================= */

(function () {
    'use strict';

    let year, month;
    let events = [];

    // ── Bootstrap ───────────────────────────────────────────────

    function init() {
        const cells = document.getElementById('cal-cells');
        if (!cells) return;

        year  = parseInt(cells.dataset.year,  10);
        month = parseInt(cells.dataset.month, 10);

        bindSearch();
        loadEvents();
    }

    // ── Data ────────────────────────────────────────────────────

    async function loadEvents() {
        const pad = String(month).padStart(2, '0');

        try {
            const res = await fetch(`/api/events?month=${year}-${pad}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error(res.status);
            events = await res.json();
        } catch (_) {
            events = [];
        }

        render();
    }

    // ── Rendering ───────────────────────────────────────────────

    function render() {
        renderChips();
        renderSidebar();
        updateFocusCard();
    }

    function renderChips() {
        // Clear chip lists that JS controls
        document.querySelectorAll('[data-date] .cal-chip-list').forEach(list => {
            list.innerHTML = '';
        });

        // Group events by UTC day
        const byDay = {};
        events.forEach(ev => {
            const d = utcDayOfMonth(ev.start_at);
            if (d !== null) {
                (byDay[d] = byDay[d] || []).push(ev);
            }
        });

        document.querySelectorAll('[data-date]').forEach(cell => {
            const day  = parseInt(cell.dataset.date.split('-')[2], 10);
            const evs  = byDay[day] || [];
            const list = cell.querySelector('.cal-chip-list');
            if (!list) return;

            const visible  = evs.slice(0, 2);
            const overflow = evs.length - visible.length;

            visible.forEach(ev => list.appendChild(buildChip(ev)));

            if (overflow > 0) {
                const more = document.createElement('span');
                more.className = 'cal-chip cal-chip--more';
                more.textContent = `+${overflow} więcej`;
                list.appendChild(more);
            }
        });
    }

    function buildChip(ev) {
        const chip = document.createElement('span');
        chip.className = `cal-chip cal-chip--${typeClass(ev.type)}`;
        chip.style.cssText = `border-left:2px solid ${ev.course_color};padding-left:10px`;
        chip.textContent = ev.title;
        chip.title       = `${ev.title} — ${ev.course_name}`;
        return chip;
    }

    function renderSidebar() {
        const list = document.getElementById('cal-event-list');
        if (!list) return;

        const now = new Date();
        const upcoming = events
            .filter(ev => new Date(ev.start_at) >= now && !ev.is_done)
            .sort((a, b) => new Date(a.start_at) - new Date(b.start_at))
            .slice(0, 4);

        list.innerHTML = '';

        if (upcoming.length === 0) {
            list.innerHTML = '<p class="cal-no-events">Brak nadchodzących wydarzeń w tym miesiącu.</p>';
            return;
        }

        upcoming.forEach(ev => {
            const date      = new Date(ev.start_at);
            const dateLabel = date.toLocaleDateString('pl-PL', { month: 'short', day: 'numeric' }).toUpperCase();
            const timeLabel = date.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' });

            const item = document.createElement('div');
            item.className = 'cal-event-item';
            item.style.borderLeftColor = ev.course_color;
            item.innerHTML = `
                <div class="cal-event-top">
                    <span class="cal-event-type" style="color:${esc(ev.course_color)}">${typeLabel(ev.type)}</span>
                    <span class="cal-event-date">${esc(dateLabel)}</span>
                </div>
                <div class="cal-event-name">${esc(ev.title)}</div>
                <div class="cal-event-meta">${esc(ev.course_name)} &bull; ${esc(timeLabel)}</div>
            `;
            list.appendChild(item);
        });
    }

    function updateFocusCard() {
        const desc = document.getElementById('cal-focus-desc');
        if (!desc) return;

        if (events.length === 0) {
            desc.textContent = 'Brak wydarzeń w tym miesiącu.';
            return;
        }

        const exams  = events.filter(ev => ev.type === 'exam').length;
        const colloqs = events.filter(ev => ev.type === 'colloquium').length;
        const others = events.length - exams - colloqs;

        const parts = [];
        if (exams)   parts.push(`${exams} egzamin${exams === 1 ? '' : 'ów'}`);
        if (colloqs) parts.push(`${colloqs} kolokwi${colloqs === 1 ? 'um' : 'ów'}`);
        if (others)  parts.push(`${others} inne`);

        desc.innerHTML = parts.join(', ') + '<br>zaplanowane w tym miesiącu.';
    }

    // ── Search ──────────────────────────────────────────────────

    function bindSearch() {
        document.getElementById('cal-search')?.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.cal-chip:not(.cal-chip--more)').forEach(chip => {
                chip.style.display = (q === '' || chip.title.toLowerCase().includes(q)) ? '' : 'none';
            });
        });
    }

    // ── Helpers ─────────────────────────────────────────────────

    /** Returns the UTC day-of-month if the timestamp belongs to the viewed month, else null. */
    function utcDayOfMonth(isoString) {
        const d = new Date(isoString);
        if (isNaN(d.getTime())) return null;
        if (d.getUTCFullYear() !== year || d.getUTCMonth() + 1 !== month) return null;
        return d.getUTCDate();
    }

    function typeClass(type) {
        return type === 'exam' ? 'exam' : type === 'colloquium' ? 'colloq' : 'other';
    }

    function typeLabel(type) {
        return type === 'exam' ? 'Egzamin' : type === 'colloquium' ? 'Kolokwium' : 'Inne';
    }

    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    document.addEventListener('DOMContentLoaded', init);
})();
