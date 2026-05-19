/* =============================================
   SYNCU — calendar.js
   Month / Week / Day views
   ============================================= */

(function () {
    'use strict';

    const HOUR_START = 7;
    const HOUR_END   = 22;
    const HOUR_PX    = 56;

    let view       = 'month';
    let curDate    = new Date();
    let evCache    = {};
    let phpYear, phpMonth;
    let phpTitle   = '';
    let phpSubtitle = '';

    // ─────────────────────────────────────────
    // Bootstrap
    // ─────────────────────────────────────────
    function init() {
        const cells = document.getElementById('cal-cells');
        if (!cells) return;

        phpYear  = +cells.dataset.year;
        phpMonth = +cells.dataset.month;

        phpTitle    = document.getElementById('cal-nav-title')?.textContent  || '';
        phpSubtitle = document.getElementById('cal-nav-subtitle')?.textContent || '';

        const now = new Date();
        curDate = (now.getFullYear() === phpYear && now.getMonth() + 1 === phpMonth)
            ? now
            : new Date(phpYear, phpMonth - 1, 1);

        bindToggle();
        bindNav();
        bindSearch();
        fetchEvents(phpYear, phpMonth).then(renderAll);
    }

    // ─────────────────────────────────────────
    // Data
    // ─────────────────────────────────────────
    async function fetchEvents(year, month) {
        const key = mk(year, month);
        if (evCache[key] !== undefined) return;
        try {
            const r = await fetch(`/api/events?month=${year}-${String(month).padStart(2,'0')}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            evCache[key] = r.ok ? await r.json() : [];
        } catch (_) {
            evCache[key] = [];
        }
    }

    async function ensureMonths(keys) {
        await Promise.all(
            keys.filter(k => evCache[k] === undefined).map(k => {
                const [y, m] = k.split('-').map(Number);
                return fetchEvents(y, m);
            })
        );
    }

    function requiredMonths() {
        if (view === 'week') {
            return [...new Set(weekDays(curDate).map(d => mk(d.getFullYear(), d.getMonth() + 1)))];
        }
        return [mk(curDate.getFullYear(), curDate.getMonth() + 1)];
    }

    // ─────────────────────────────────────────
    // Render dispatcher
    // ─────────────────────────────────────────
    function renderAll() {
        if (view === 'month') {
            const evs = evCache[mk(phpYear, phpMonth)] || [];
            renderChips(evs);
            renderSidebar(evs);
            updateFocusCard(evs);
        } else {
            renderTimeView();
        }
    }

    // ─────────────────────────────────────────
    // Month view
    // ─────────────────────────────────────────
    function renderChips(events) {
        document.querySelectorAll('[data-date] .cal-chip-list').forEach(l => l.innerHTML = '');

        const byDay = {};
        events.forEach(ev => {
            const d = new Date(ev.start_at);
            if (d.getFullYear() === phpYear && d.getMonth() + 1 === phpMonth) {
                const day = d.getDate();
                (byDay[day] = byDay[day] || []).push(ev);
            }
        });

        document.querySelectorAll('[data-date]').forEach(cell => {
            const day  = +cell.dataset.date.split('-')[2];
            const evs  = byDay[day] || [];
            const list = cell.querySelector('.cal-chip-list');
            if (!list) return;

            evs.slice(0, 2).forEach(ev => list.appendChild(makeChip(ev)));
            const over = evs.length - Math.min(evs.length, 2);
            if (over > 0) {
                const m = document.createElement('span');
                m.className   = 'cal-chip cal-chip--more';
                m.textContent = `+${over} więcej`;
                list.appendChild(m);
            }
        });
    }

    function makeChip(ev) {
        const c = document.createElement('span');
        c.className     = `cal-chip cal-chip--${typeClass(ev.type)}`;
        c.style.cssText = `border-left:2px solid ${ev.course_color};padding-left:10px`;
        c.textContent   = ev.title;
        c.title         = `${ev.title} — ${ev.course_name}`;
        return c;
    }

    function updateFocusCard(events) {
        const desc = document.getElementById('cal-focus-desc');
        if (!desc) return;
        if (!events.length) { desc.textContent = 'Brak wydarzeń w tym miesiącu.'; return; }

        const exams   = events.filter(e => e.type === 'exam').length;
        const colloqs = events.filter(e => e.type === 'colloquium').length;
        const others  = events.length - exams - colloqs;
        const parts   = [];
        if (exams)   parts.push(`${exams} egzamin${exams === 1 ? '' : 'ów'}`);
        if (colloqs) parts.push(`${colloqs} kolokwi${colloqs === 1 ? 'um' : 'ów'}`);
        if (others)  parts.push(`${others} inne`);
        desc.innerHTML = parts.join(', ') + '<br>zaplanowane w tym miesiącu.';
    }

    // ─────────────────────────────────────────
    // Time view (Week / Day)
    // ─────────────────────────────────────────
    function renderTimeView() {
        const container = document.getElementById('cal-time-view');
        if (!container) return;

        const days     = view === 'week' ? weekDays(curDate) : [new Date(curDate)];
        const cols     = days.length;
        const now      = new Date();
        const todayStr = fmtDate(now);
        const nowMins  = now.getHours() * 60 + now.getMinutes();
        const hours    = [];
        for (let h = HOUR_START; h < HOUR_END; h++) hours.push(h);
        const totalH = (HOUR_END - HOUR_START) * HOUR_PX;

        // ── Header ──
        let html = `<div class="cal-tv-head" style="--cols:${cols}">
            <div class="cal-tv-spacer"></div>`;
        days.forEach(d => {
            const isToday = fmtDate(d) === todayStr;
            const name    = d.toLocaleDateString('pl-PL', { weekday: 'short' });
            html += `
            <div class="cal-tv-head-day${isToday ? ' cal-tv-head-day--today' : ''}">
                <span class="cal-tv-day-name">${esc(name)}</span>
                <span class="cal-tv-day-num${isToday ? ' cal-tv-day-num--today' : ''}">${d.getDate()}</span>
            </div>`;
        });
        html += '</div>';

        // ── Body ──
        html += `<div class="cal-tv-body" id="cal-tv-body" style="--cols:${cols}">`;
        html += '<div class="cal-tv-times">';
        hours.forEach(h => {
            html += `<div class="cal-tv-ts" style="height:${HOUR_PX}px">
                <span class="cal-tv-tl">${String(h).padStart(2,'0')}:00</span>
            </div>`;
        });
        html += '</div>';

        days.forEach((d, idx) => {
            const isToday = fmtDate(d) === todayStr;
            html += `<div class="cal-tv-col${isToday ? ' cal-tv-col--today' : ''}" data-col="${idx}" style="height:${totalH}px">`;
            hours.forEach(() => { html += `<div class="cal-tv-hr" style="height:${HOUR_PX}px"></div>`; });
            html += '</div>';
        });

        html += '</div>'; // body
        container.innerHTML = html;

        // ── Events ──
        const evs   = eventsForDays(days);
        const byDay = {};
        days.forEach((d, i) => {
            const ds = fmtDate(d);
            byDay[i] = evs.filter(ev => fmtDate(new Date(ev.start_at)) === ds);
        });
        days.forEach((_, idx) => {
            const col = container.querySelector(`[data-col="${idx}"]`);
            if (!col) return;
            (byDay[idx] || []).forEach(ev => {
                const el = makeEventBlock(ev);
                if (el) col.appendChild(el);
            });
        });

        // ── Now-line ──
        const todayIdx = days.findIndex(d => fmtDate(d) === todayStr);
        if (todayIdx >= 0 && nowMins >= HOUR_START * 60 && nowMins < HOUR_END * 60) {
            const top = ((nowMins - HOUR_START * 60) / 60) * HOUR_PX;
            const col = container.querySelector(`[data-col="${todayIdx}"]`);
            if (col) {
                const line = document.createElement('div');
                line.className = 'cal-tv-nowline';
                line.style.top = `${top}px`;
                col.appendChild(line);
            }

            // Scroll to current time (center it)
            const body = document.getElementById('cal-tv-body');
            if (body) body.scrollTop = Math.max(0, top - 160);
        }

        // ── Sidebar ──
        renderSidebar(evCache[mk(phpYear, phpMonth)] || []);
        updateNavTitle(days);
    }

    function makeEventBlock(ev) {
        const start = new Date(ev.start_at);
        const end   = ev.end_at ? new Date(ev.end_at) : new Date(start.getTime() + 3600000);
        const sMin  = start.getHours() * 60 + start.getMinutes();
        const eMin  = end.getHours()   * 60 + end.getMinutes();
        const gS    = HOUR_START * 60;
        const gE    = HOUR_END   * 60;

        if (eMin <= gS || sMin >= gE) return null;

        const top  = (Math.max(sMin, gS) - gS) / 60 * HOUR_PX;
        const h    = Math.max((Math.min(eMin, gE) - Math.max(sMin, gS)) / 60 * HOUR_PX, 22);
        const clr  = ev.course_color || '#1b6871';
        const time = start.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' });

        const el = document.createElement('div');
        el.className     = 'cal-tv-event';
        el.title         = `${ev.title} — ${ev.course_name}`;
        el.style.cssText = `top:${top}px;height:${h}px;border-left-color:${clr};background:${hexRgba(clr, .12)};color:${clr}`;
        el.innerHTML     = `<span class="cal-tv-ev-title">${esc(ev.title)}</span>`
            + (h > 36 ? `<span class="cal-tv-ev-time">${esc(time)}</span>` : '');
        return el;
    }

    function eventsForDays(days) {
        const strs = new Set(days.map(fmtDate));
        return Object.values(evCache).flat().filter(ev =>
            strs.has(fmtDate(new Date(ev.start_at)))
        );
    }

    function updateNavTitle(days) {
        const titleEl    = document.getElementById('cal-nav-title');
        const subtitleEl = document.getElementById('cal-nav-subtitle');
        if (!titleEl) return;

        if (view === 'day') {
            const d = days[0];
            titleEl.textContent    = d.toLocaleDateString('pl-PL', { day: 'numeric', month: 'long', year: 'numeric' });
            if (subtitleEl) subtitleEl.textContent = d.toLocaleDateString('pl-PL', { weekday: 'long' });
        } else {
            const f = days[0], l = days[6];
            if (f.getMonth() === l.getMonth()) {
                titleEl.textContent = `${f.getDate()}–${l.getDate()} ${f.toLocaleDateString('pl-PL', { month: 'long', year: 'numeric' })}`;
            } else {
                titleEl.textContent = `${f.toLocaleDateString('pl-PL', { day: 'numeric', month: 'short' })} – ${l.toLocaleDateString('pl-PL', { day: 'numeric', month: 'short', year: 'numeric' })}`;
            }
            if (subtitleEl) subtitleEl.textContent = 'Widok tygodniowy';
        }
    }

    // ─────────────────────────────────────────
    // Sidebar
    // ─────────────────────────────────────────
    function renderSidebar(events) {
        const list = document.getElementById('cal-event-list');
        if (!list) return;

        const now      = new Date();
        const upcoming = events
            .filter(ev => new Date(ev.start_at) >= now && !ev.is_done)
            .sort((a, b) => new Date(a.start_at) - new Date(b.start_at))
            .slice(0, 4);

        list.innerHTML = '';
        if (!upcoming.length) {
            list.innerHTML = '<p class="cal-no-events">Brak nadchodzących wydarzeń.</p>';
            return;
        }

        upcoming.forEach(ev => {
            const d    = new Date(ev.start_at);
            const dLbl = d.toLocaleDateString('pl-PL', { month: 'short', day: 'numeric' }).toUpperCase();
            const tLbl = d.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' });
            const item = document.createElement('div');
            item.className             = 'cal-event-item';
            item.style.borderLeftColor = ev.course_color;
            item.innerHTML = `
                <div class="cal-event-top">
                    <span class="cal-event-type" style="color:${esc(ev.course_color)}">${typeLabel(ev.type)}</span>
                    <span class="cal-event-date">${esc(dLbl)}</span>
                </div>
                <div class="cal-event-name">${esc(ev.title)}</div>
                <div class="cal-event-meta">${esc(ev.course_name)} &bull; ${esc(tLbl)}</div>`;
            list.appendChild(item);
        });
    }

    // ─────────────────────────────────────────
    // Toggle / Navigation
    // ─────────────────────────────────────────
    function bindToggle() {
        ['month', 'week', 'day'].forEach(v => {
            document.getElementById(`cal-toggle-${v}`)?.addEventListener('click', () => setView(v));
        });
    }

    function setView(newView) {
        if (view === newView) return;
        view = newView;

        ['month', 'week', 'day'].forEach(v => {
            document.getElementById(`cal-toggle-${v}`)?.classList.toggle('active', v === newView);
        });

        const mv = document.getElementById('cal-month-view');
        const tv = document.getElementById('cal-time-view');
        if (mv) mv.hidden = newView !== 'month';
        if (tv) tv.hidden = newView === 'month';

        if (newView === 'month') {
            const titleEl    = document.getElementById('cal-nav-title');
            const subtitleEl = document.getElementById('cal-nav-subtitle');
            if (titleEl)    titleEl.textContent    = phpTitle;
            if (subtitleEl) subtitleEl.textContent = phpSubtitle;
            renderAll();
        } else {
            ensureMonths(requiredMonths()).then(renderAll);
        }
    }

    function bindNav() {
        document.getElementById('cal-nav-prev')?.addEventListener('click', e => {
            if (view === 'month') return;
            e.preventDefault();
            navigate(-1);
        });
        document.getElementById('cal-nav-next')?.addEventListener('click', e => {
            if (view === 'month') return;
            e.preventDefault();
            navigate(1);
        });
    }

    async function navigate(delta) {
        const d = new Date(curDate);
        if (view === 'day') {
            d.setDate(d.getDate() + delta);
        } else {
            d.setDate(d.getDate() + delta * 7);
        }
        curDate = d;

        await ensureMonths(requiredMonths());
        renderAll();
    }

    // ─────────────────────────────────────────
    // Search
    // ─────────────────────────────────────────
    function bindSearch() {
        document.getElementById('cal-search')?.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.cal-chip:not(.cal-chip--more)').forEach(c => {
                c.style.display = (!q || c.title.toLowerCase().includes(q)) ? '' : 'none';
            });
            document.querySelectorAll('.cal-tv-event').forEach(c => {
                c.style.opacity = (!q || c.title.toLowerCase().includes(q)) ? '1' : '0.2';
            });
        });
    }

    // ─────────────────────────────────────────
    // Utilities
    // ─────────────────────────────────────────
    function weekDays(date) {
        const d   = new Date(date);
        const dow = d.getDay() || 7;
        d.setDate(d.getDate() - dow + 1);
        return Array.from({ length: 7 }, (_, i) => {
            const x = new Date(d);
            x.setDate(d.getDate() + i);
            return x;
        });
    }

    function mk(y, m)   { return `${y}-${String(m).padStart(2,'0')}`; }
    function fmtDate(d) { return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }

    function hexRgba(hex, a) {
        const r = parseInt(hex.slice(1,3), 16),
              g = parseInt(hex.slice(3,5), 16),
              b = parseInt(hex.slice(5,7), 16);
        return isNaN(r) ? `rgba(27,104,113,${a})` : `rgba(${r},${g},${b},${a})`;
    }

    function typeClass(t) { return t === 'exam' ? 'exam' : t === 'colloquium' ? 'colloq' : 'other'; }
    function typeLabel(t) { return t === 'exam' ? 'Egzamin' : t === 'colloquium' ? 'Kolokwium' : 'Inne'; }

    function esc(s) {
        return String(s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    document.addEventListener('DOMContentLoaded', init);
})();
