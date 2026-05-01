/* =============================================
   SYNCU — events.js
   Event CRUD via Fetch API
   ============================================= */

(function () {

    const TYPE_META = {
        exam:       { label: 'Egzamin',    color: '#a83836', badgeClass: 'ev-badge-type-exam' },
        colloquium: { label: 'Kolokwium',  color: '#1b6871', badgeClass: 'ev-badge-type-colloquium' },
        other:      { label: 'Inne',       color: '#416280', badgeClass: 'ev-badge-type-other' },
    };

    let events   = [];
    let courses  = [];
    let editingId = null;
    let selType   = 'exam';
    let toastTimer = null;

    // Active filters
    let filterType   = '';
    let filterCourse = '';
    let filterStatus = '';
    let filterSearch = '';

    // DOM refs
    const list         = document.getElementById('ev-list');
    const emptyEl      = document.getElementById('ev-empty');
    const overlay      = document.getElementById('ev-modal-overlay');
    const modalTitle   = document.getElementById('ev-modal-title');
    const form         = document.getElementById('ev-form');
    const inputId      = document.getElementById('ev-id');
    const inputTitle   = document.getElementById('ev-title');
    const selectCourse = document.getElementById('ev-course');
    const inputStart   = document.getElementById('ev-start');
    const inputEnd     = document.getElementById('ev-end');
    const inputDesc    = document.getElementById('ev-desc');
    const titleError   = document.getElementById('ev-title-error');
    const courseError  = document.getElementById('ev-course-error');
    const startError   = document.getElementById('ev-start-error');
    const endError     = document.getElementById('ev-end-error');
    const saveBtn      = document.getElementById('ev-btn-save');
    const toast        = document.getElementById('ev-toast');
    const courseFilter = document.getElementById('ev-course-filter');

    // ---- Bind events ----
    document.getElementById('ev-btn-new').addEventListener('click', () => openModal());
    document.getElementById('ev-btn-empty').addEventListener('click', () => openModal());
    document.getElementById('ev-modal-close').addEventListener('click', closeModal);
    document.getElementById('ev-btn-cancel').addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
    form.addEventListener('submit', handleSubmit);

    document.getElementById('ev-type-tabs').addEventListener('click', (e) => {
        const btn = e.target.closest('.ev-tab');
        if (!btn) return;
        document.querySelectorAll('.ev-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filterType = btn.dataset.type;
        renderList();
    });

    document.getElementById('ev-status-tabs').addEventListener('click', (e) => {
        const btn = e.target.closest('.ev-status-btn');
        if (!btn) return;
        document.querySelectorAll('.ev-status-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filterStatus = btn.dataset.status;
        renderList();
    });

    courseFilter.addEventListener('change', () => {
        filterCourse = courseFilter.value;
        renderList();
    });

    document.getElementById('ev-search').addEventListener('input', (e) => {
        filterSearch = e.target.value.toLowerCase();
        renderList();
    });

    document.getElementById('ev-type-picker').addEventListener('click', (e) => {
        const btn = e.target.closest('.ev-type-opt');
        if (!btn) return;
        selType = btn.dataset.value;
        document.querySelectorAll('.ev-type-opt').forEach(b => {
            b.classList.toggle('active', b.dataset.value === selType);
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('open')) closeModal();
    });

    // ---- Init ----
    loadAll();

    async function loadAll() {
        try {
            [courses, events] = await Promise.all([
                Api.get('/api/courses'),
                Api.get('/api/events'),
            ]);
            populateCourseDropdowns();
            renderList();
        } catch {
            showToast('Nie udało się załadować danych.', 'error');
        }
    }

    // ---- Dropdowns ----
    function populateCourseDropdowns() {
        // Filter bar
        courseFilter.innerHTML = '<option value="">Wszystkie przedmioty</option>';
        // Modal select
        selectCourse.innerHTML = '<option value="">— wybierz —</option>';

        courses.forEach(c => {
            const opt1 = new Option(c.name, c.id);
            const opt2 = new Option(c.name, c.id);
            courseFilter.appendChild(opt1);
            selectCourse.appendChild(opt2);
        });
    }

    // ---- Render ----
    function renderList() {
        let filtered = events.filter(e => {
            if (filterType   && e.type !== filterType) return false;
            if (filterCourse && String(e.course_id) !== filterCourse) return false;
            if (filterStatus === 'done'    &&  !e.is_done) return false;
            if (filterStatus === 'pending' &&   e.is_done) return false;
            if (filterSearch && !e.title.toLowerCase().includes(filterSearch)) return false;
            return true;
        });

        // Sort: upcoming first (asc), done events last
        filtered.sort((a, b) => {
            if (a.is_done !== b.is_done) return a.is_done ? 1 : -1;
            return new Date(a.start_at) - new Date(b.start_at);
        });

        list.innerHTML = '';

        if (filtered.length === 0) {
            emptyEl.hidden = false;
            return;
        }

        emptyEl.hidden = true;
        filtered.forEach(ev => {
            const card  = buildCard(ev);
            const panel = buildPanel(ev.id);
            list.appendChild(card);
            list.appendChild(panel);

            // Bind tasks toggle — TaskPanel manages panel.hidden internally
            const tasksBtn = card.querySelector('.ev-btn-tasks');
            const badgeEl  = card.querySelector('.ev-tasks-badge');
            tasksBtn.addEventListener('click', async () => {
                await TaskPanel.toggle(ev.id, panel, badgeEl);
                tasksBtn.classList.toggle('ev-btn-tasks--open', !panel.hidden);
            });
        });
    }

    function buildPanel(eventId) {
        const panel = document.createElement('div');
        panel.className = 'tk-panel';
        panel.hidden    = true;
        return panel;
    }

    function buildCard(ev) {
        const meta        = TYPE_META[ev.type] ?? TYPE_META.other;
        const startD      = new Date(ev.start_at);
        const day         = startD.getDate();
        const month       = startD.toLocaleDateString('pl-PL', { month: 'short' }).replace('.', '');
        const year        = startD.getFullYear();
        const timeStr     = buildTimeStr(ev);
        const course      = courses.find(c => c.id === ev.course_id);
        const courseColor = course?.color ?? '#6c63ff';

        const card = document.createElement('article');
        card.className  = 'ev-card' + (ev.is_done ? ' is-done' : '');
        card.dataset.id = ev.id;

        card.innerHTML = `
            <div class="ev-card-type-bar" style="background:${esc(meta.color)};"></div>
            <div class="ev-card-date-block">
                <span class="ev-card-day">${day}</span>
                <span class="ev-card-month">${esc(month.toUpperCase())}</span>
                <span class="ev-card-year">${year}</span>
            </div>
            <div class="ev-card-content">
                <div class="ev-card-top">
                    <h3 class="ev-card-title">${esc(ev.title)}</h3>
                    <span class="ev-badge ev-badge-course" style="background:${esc(courseColor)};">
                        ${esc(ev.course_name)}
                    </span>
                    <span class="ev-badge ${meta.badgeClass}">${meta.label}</span>
                </div>
                <div class="ev-card-time">
                    <i class="fa-regular fa-clock" aria-hidden="true"></i>
                    ${esc(timeStr)}
                </div>
                ${ev.description ? `<p class="ev-card-desc">${esc(ev.description)}</p>` : ''}
                <button class="ev-btn-tasks" title="Pokaż/ukryj zadania">
                    <i class="fa-regular fa-list-check" aria-hidden="true"></i>
                    Zadania
                    <span class="ev-tasks-badge" hidden></span>
                    <i class="fa-solid fa-chevron-down ev-tasks-chevron" aria-hidden="true"></i>
                </button>
            </div>
            <div class="ev-card-actions">
                <button class="ev-btn-toggle" title="${ev.is_done ? 'Oznacz jako nieukończone' : 'Oznacz jako ukończone'}" aria-pressed="${ev.is_done}">
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                </button>
                <button class="ev-btn-edit" title="Edytuj" aria-label="Edytuj ${esc(ev.title)}">
                    <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                </button>
                <button class="ev-btn-delete" title="Usuń" aria-label="Usuń ${esc(ev.title)}">
                    <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                </button>
            </div>
        `;

        card.querySelector('.ev-btn-toggle').addEventListener('click', () => toggleDone(ev));
        card.querySelector('.ev-btn-edit').addEventListener('click', () => openModal(ev));
        card.querySelector('.ev-btn-delete').addEventListener('click', () => deleteEvent(ev));

        return card;
    }

    function buildTimeStr(ev) {
        const start = formatTime(ev.start_at);
        if (!ev.end_at) return start;
        return `${start} – ${formatTime(ev.end_at)}`;
    }

    // ---- Modal ----
    function openModal(ev = null) {
        editingId = ev ? ev.id : null;

        modalTitle.textContent = ev ? 'Edytuj Wydarzenie' : 'Nowe Wydarzenie';
        inputId.value    = ev ? ev.id : '';
        inputTitle.value = ev ? ev.title : '';
        inputDesc.value  = ev ? (ev.description ?? '') : '';
        inputStart.value = ev ? toDatetimeLocal(ev.start_at) : '';
        inputEnd.value   = ev ? (ev.end_at ? toDatetimeLocal(ev.end_at) : '') : '';

        // Course
        selectCourse.value = ev ? ev.course_id : '';

        // Type picker
        selType = ev ? ev.type : 'exam';
        document.querySelectorAll('.ev-type-opt').forEach(b => {
            b.classList.toggle('active', b.dataset.value === selType);
        });

        // Clear errors
        [titleError, courseError, startError, endError].forEach(el => el.textContent = '');

        overlay.classList.add('open');
        requestAnimationFrame(() => inputTitle.focus());
    }

    function closeModal() {
        overlay.classList.remove('open');
        form.reset();
        editingId = null;
        [titleError, courseError, startError, endError].forEach(el => el.textContent = '');
        selType = 'exam';
        document.querySelectorAll('.ev-type-opt').forEach(b => {
            b.classList.toggle('active', b.dataset.value === 'exam');
        });
    }

    // ---- Submit ----
    async function handleSubmit(e) {
        e.preventDefault();
        [titleError, courseError, startError, endError].forEach(el => el.textContent = '');

        const title    = inputTitle.value.trim();
        const courseId = parseInt(selectCourse.value, 10);
        const startAt  = inputStart.value;
        const endAt    = inputEnd.value || null;
        const desc     = inputDesc.value.trim();

        let valid = true;

        if (!title) {
            titleError.textContent = 'Tytuł jest wymagany.';
            valid = false;
        }
        if (!courseId) {
            courseError.textContent = 'Wybierz przedmiot.';
            valid = false;
        }
        if (!startAt) {
            startError.textContent = 'Data rozpoczęcia jest wymagana.';
            valid = false;
        }
        if (endAt && new Date(endAt) <= new Date(startAt)) {
            endError.textContent = 'Data zakończenia musi być późniejsza.';
            valid = false;
        }
        if (!valid) return;

        saveBtn.disabled = true;

        const payload = {
            title,
            course_id:   courseId,
            type:        selType,
            start_at:    startAt,
            end_at:      endAt,
            description: desc,
        };

        try {
            if (editingId) {
                payload.id = editingId;
                await Api.post('/events/update', payload);
                showToast('Wydarzenie zaktualizowane.');
            } else {
                await Api.post('/events/create', payload);
                showToast('Wydarzenie dodane.');
            }
            closeModal();
            events = await Api.get('/api/events');
            renderList();
        } catch (err) {
            showToast(err.message || 'Wystąpił błąd.', 'error');
        } finally {
            saveBtn.disabled = false;
        }
    }

    // ---- Toggle Done ----
    async function toggleDone(ev) {
        const newState = !ev.is_done;
        try {
            await Api.post('/events/toggle', { id: ev.id, is_done: newState });
            ev.is_done = newState;
            renderList();
            showToast(newState ? 'Oznaczono jako ukończone.' : 'Oznaczono jako nieukończone.');
        } catch (err) {
            showToast(err.message || 'Błąd zmiany statusu.', 'error');
        }
    }

    // ---- Delete ----
    async function deleteEvent(ev) {
        if (!confirm(`Usuń "${ev.title}"?\n\nUsunięcie wydarzenia usunie też powiązane zadania i notatki.`)) return;

        try {
            await Api.post('/events/delete', { id: ev.id });
            showToast('Wydarzenie usunięte.');
            events = events.filter(e => e.id !== ev.id);
            renderList();
        } catch (err) {
            showToast(err.message || 'Błąd usuwania.', 'error');
        }
    }

    // ---- Toast ----
    function showToast(msg, type = 'success') {
        toast.textContent = msg;
        toast.className   = `ev-toast ev-toast--${type} show`;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
    }

    // ---- Date helpers ----
    function formatTime(isoStr) {
        if (!isoStr) return '';
        return new Date(isoStr).toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' });
    }

    function toDatetimeLocal(isoStr) {
        if (!isoStr) return '';
        const d   = new Date(isoStr);
        const pad = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    // ---- Escape HTML ----
    function esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

})();
