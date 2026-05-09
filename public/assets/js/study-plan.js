/* =============================================
   SYNCU — study-plan.js
   Daily task planner — Fetch API
   ============================================= */

(function () {
    'use strict';

    let currentDate = todayStr();
    let planItems   = [];
    let allEvents   = [];
    let taskCache   = {};   // event_id → Task[]
    let toastTimer  = null;

    // DOM
    const dateDisplay = document.getElementById('sp-date-display');
    const todayBtn    = document.getElementById('sp-today');
    const content     = document.getElementById('sp-content');
    const emptyEl     = document.getElementById('sp-empty');
    const addPanel    = document.getElementById('sp-add-panel');
    const addBtn      = document.getElementById('sp-add-btn');
    const selEvent    = document.getElementById('sp-sel-event');
    const selTask     = document.getElementById('sp-sel-task');
    const toast       = document.getElementById('sp-toast');

    // ── Bindings ───────────────────────────────────────────────

    document.getElementById('sp-prev').addEventListener('click', () => shiftDate(-1));
    document.getElementById('sp-next').addEventListener('click', () => shiftDate(+1));
    todayBtn.addEventListener('click', () => setDate(todayStr()));

    addBtn.addEventListener('click', openPanel);
    document.getElementById('sp-add-cancel').addEventListener('click', closePanel);
    document.getElementById('sp-add-save').addEventListener('click', handleAdd);
    selEvent.addEventListener('change', onEventChange);

    // ── Init ───────────────────────────────────────────────────

    async function init() {
        try {
            allEvents = await Api.get('/api/events');
            populateEventSelect();
        } catch {
            showToast('Nie udało się załadować wydarzeń.', 'error');
        }
        await loadPlan();
    }

    // ── Date helpers ───────────────────────────────────────────

    function todayStr() {
        return fmtDate(new Date());
    }

    function fmtDate(d) {
        const p = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
    }

    function shiftDate(delta) {
        const d = new Date(currentDate + 'T12:00:00');
        d.setDate(d.getDate() + delta);
        setDate(fmtDate(d));
    }

    function setDate(dateStr) {
        currentDate = dateStr;
        loadPlan();
    }

    function updateDateDisplay() {
        const d = new Date(currentDate + 'T12:00:00');
        dateDisplay.textContent = d.toLocaleDateString('pl-PL', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
        });
        todayBtn.style.visibility = (currentDate === todayStr()) ? 'hidden' : 'visible';
    }

    // ── Data ───────────────────────────────────────────────────

    async function loadPlan() {
        updateDateDisplay();
        content.innerHTML = '';
        try {
            planItems = await Api.get(`/api/study-plan?date=${currentDate}`);
        } catch {
            showToast('Nie udało się załadować planu.', 'error');
            planItems = [];
        }
        render();
    }

    // ── Render ─────────────────────────────────────────────────

    function render() {
        content.innerHTML = '';

        if (planItems.length === 0) {
            emptyEl.hidden = false;
            return;
        }
        emptyEl.hidden = true;

        // Group by event
        const groups = {};
        planItems.forEach(item => {
            const eid = item.event_id;
            if (!groups[eid]) {
                groups[eid] = {
                    event_id:    eid,
                    event_title: item.event_title,
                    event_type:  item.event_type,
                    days_until:  item.days_until,
                    course_name:  item.course_name,
                    course_color: item.course_color,
                    items: [],
                };
            }
            groups[eid].items.push(item);
        });

        Object.values(groups).forEach(g => content.appendChild(buildGroup(g)));
    }

    function buildGroup(group) {
        const typeLabel = { exam: 'Egzamin', colloquium: 'Kolokwium', other: 'Inne' }[group.event_type] ?? 'Inne';

        const days = group.days_until;
        const daysLabel = days === null ? '' :
                          days === 0   ? ' · Dziś!' :
                          days > 0     ? ` · za ${days} ${days === 1 ? 'dzień' : 'dni'}` :
                                         ` · ${Math.abs(days)} dni temu`;

        const done  = group.items.filter(i => i.task_done).length;
        const total = group.items.length;
        const pct   = total === 0 ? 0 : Math.round(done / total * 100);

        const wrap = document.createElement('div');
        wrap.className = 'sp-group';
        wrap.innerHTML = `
            <div class="sp-group-header" style="border-left-color:${esc(group.course_color)};">
                <div class="sp-group-info">
                    <span class="sp-group-title">${esc(group.event_title)}</span>
                    <span class="sp-group-meta">${esc(group.course_name)} &bull; ${typeLabel}${esc(daysLabel)}</span>
                </div>
                <div class="sp-group-pct">${done}/${total}</div>
            </div>
            <div class="sp-progress-wrap">
                <div class="sp-progress-bar">
                    <div class="sp-progress-fill" style="width:${pct}%"></div>
                </div>
                <span class="sp-pct-label">${pct}%</span>
            </div>
            <ul class="sp-task-list"></ul>
        `;

        const ul = wrap.querySelector('.sp-task-list');
        group.items.forEach(item => ul.appendChild(buildItem(item)));

        return wrap;
    }

    function buildItem(item) {
        const li = document.createElement('li');
        li.className = 'sp-item' + (item.task_done ? ' sp-item--done' : '');

        li.innerHTML = `
            <button class="sp-check" aria-pressed="${item.task_done}"
                    title="${item.task_done ? 'Odznacz' : 'Zaznacz jako ukończone'}">
                ${item.task_done ? '<i class="fa-solid fa-check" aria-hidden="true"></i>' : ''}
            </button>
            <span class="sp-item-title">${esc(item.task_title)}</span>
            <button class="sp-remove" title="Usuń z planu" aria-label="Usuń z planu">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        `;

        li.querySelector('.sp-check').addEventListener('click',  () => toggleTask(item, li));
        li.querySelector('.sp-remove').addEventListener('click', () => removeItem(item));

        return li;
    }

    // ── Actions ────────────────────────────────────────────────

    async function toggleTask(item, li) {
        const newDone = !item.task_done;
        try {
            await Api.post('/tasks/toggle', { id: item.task_id, is_done: newDone });
            item.task_done = newDone;
            li.className = 'sp-item' + (newDone ? ' sp-item--done' : '');
            const btn = li.querySelector('.sp-check');
            btn.setAttribute('aria-pressed', newDone);
            btn.innerHTML = newDone ? '<i class="fa-solid fa-check" aria-hidden="true"></i>' : '';
            // refresh progress bar
            const group = li.closest('.sp-group');
            if (group) refreshGroupProgress(group);
        } catch {
            showToast('Błąd zmiany statusu.', 'error');
        }
    }

    function refreshGroupProgress(groupEl) {
        const items = groupEl.querySelectorAll('.sp-item');
        const total = items.length;
        const done  = groupEl.querySelectorAll('.sp-item--done').length;
        const pct   = total === 0 ? 0 : Math.round(done / total * 100);
        groupEl.querySelector('.sp-progress-fill').style.width = pct + '%';
        groupEl.querySelector('.sp-pct-label').textContent     = pct + '%';
        groupEl.querySelector('.sp-group-pct').textContent     = `${done}/${total}`;
    }

    async function removeItem(item) {
        try {
            await Api.post('/study-plan/delete', { id: item.id });
            planItems = planItems.filter(p => p.id !== item.id);
            render();
            showToast('Usunięto z planu.');
        } catch (err) {
            showToast(err.message || 'Błąd usuwania.', 'error');
        }
    }

    // ── Add panel ──────────────────────────────────────────────

    function populateEventSelect() {
        selEvent.innerHTML = '<option value="">— wybierz wydarzenie —</option>';
        allEvents
            .filter(ev => !ev.is_done)
            .sort((a, b) => new Date(a.start_at) - new Date(b.start_at))
            .forEach(ev => selEvent.appendChild(new Option(ev.title, ev.id)));
    }

    async function onEventChange() {
        const eid = parseInt(selEvent.value, 10);
        selTask.innerHTML  = '<option value="">— wybierz zadanie —</option>';
        selTask.disabled   = !eid;
        if (!eid) return;

        if (!taskCache[eid]) {
            selTask.innerHTML = '<option value="">Ładowanie…</option>';
            try {
                taskCache[eid] = await Api.get(`/api/tasks?event_id=${eid}`);
            } catch {
                selTask.innerHTML = '<option value="">Błąd ładowania</option>';
                return;
            }
        }

        selTask.innerHTML = '<option value="">— wybierz zadanie —</option>';
        taskCache[eid].forEach(t => {
            const opt = new Option(t.title, t.id);
            if (t.is_done) opt.classList.add('sp-opt-done');
            selTask.appendChild(opt);
        });
        selTask.disabled = false;
    }

    function openPanel() {
        selEvent.value    = '';
        selTask.innerHTML = '<option value="">— najpierw wybierz wydarzenie —</option>';
        selTask.disabled  = true;
        addPanel.hidden   = false;
        addBtn.hidden     = true;
        selEvent.focus();
    }

    function closePanel() {
        addPanel.hidden = false;
        addPanel.hidden = true;
        addBtn.hidden   = false;
    }

    async function handleAdd() {
        const taskId = parseInt(selTask.value, 10);
        if (!taskId) {
            showToast('Wybierz zadanie.', 'error');
            return;
        }

        const saveBtn = document.getElementById('sp-add-save');
        saveBtn.disabled = true;

        try {
            await Api.post('/study-plan/create', {
                task_id:      taskId,
                planned_date: currentDate,
            });
            closePanel();
            await loadPlan();
            showToast('Dodano do planu.');
        } catch (err) {
            showToast(err.message || 'Błąd dodawania.', 'error');
        } finally {
            saveBtn.disabled = false;
        }
    }

    // ── Toast ──────────────────────────────────────────────────

    function showToast(msg, type = 'success') {
        toast.textContent = msg;
        toast.className   = `sp-toast sp-toast--${type} show`;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
    }

    // ── Utils ──────────────────────────────────────────────────

    function esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Boot ───────────────────────────────────────────────────

    init();

})();
