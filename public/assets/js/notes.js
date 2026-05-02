/* =============================================
   SYNCU — notes.js
   Notes CRUD via Fetch API
   ============================================= */

(function () {

    let notes   = [];
    let courses = [];
    let events  = [];
    let editingId  = null;
    let toastTimer = null;

    let filterCourse = '';
    let filterEvent  = '';
    let filterSearch = '';

    // DOM refs
    const list          = document.getElementById('nt-list');
    const emptyEl       = document.getElementById('nt-empty');
    const overlay       = document.getElementById('nt-modal-overlay');
    const modalTitle    = document.getElementById('nt-modal-title');
    const form          = document.getElementById('nt-form');
    const inputId       = document.getElementById('nt-id');
    const inputTitle    = document.getElementById('nt-note-title');
    const inputContent  = document.getElementById('nt-content');
    const selectCourse  = document.getElementById('nt-modal-course');
    const selectEvent   = document.getElementById('nt-modal-event');
    const titleError    = document.getElementById('nt-title-error');
    const saveBtn       = document.getElementById('nt-btn-save');
    const toast         = document.getElementById('nt-toast');
    const courseFilter  = document.getElementById('nt-course-filter');
    const eventFilter   = document.getElementById('nt-event-filter');
    const searchInput   = document.getElementById('nt-search');

    // ---- Bind events ----
    document.getElementById('nt-btn-new').addEventListener('click', () => openModal());
    document.getElementById('nt-btn-empty').addEventListener('click', () => openModal());
    document.getElementById('nt-modal-close').addEventListener('click', closeModal);
    document.getElementById('nt-btn-cancel').addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
    form.addEventListener('submit', handleSubmit);

    courseFilter.addEventListener('change', () => {
        filterCourse = courseFilter.value;
        filterEvent  = '';
        eventFilter.value = '';
        populateEventFilter();
        renderList();
    });

    eventFilter.addEventListener('change', () => {
        filterEvent = eventFilter.value;
        renderList();
    });

    searchInput.addEventListener('input', (e) => {
        filterSearch = e.target.value.toLowerCase();
        renderList();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('open')) closeModal();
    });

    // ---- Init ----
    loadAll();

    async function loadAll() {
        try {
            [notes, courses, events] = await Promise.all([
                Api.get('/api/notes'),
                Api.get('/api/courses'),
                Api.get('/api/events'),
            ]);
            populateDropdowns();
            renderList();
        } catch {
            showToast('Nie udało się załadować danych.', 'error');
        }
    }

    // ---- Dropdowns ---- */
    function populateDropdowns() {
        // Filter bar — courses
        courseFilter.innerHTML = '<option value="">Wszystkie przedmioty</option>';
        courses.forEach(c => courseFilter.appendChild(new Option(c.name, c.id)));

        // Filter bar — events (all initially)
        populateEventFilter();

        // Modal — courses
        selectCourse.innerHTML = '<option value="0">— brak —</option>';
        courses.forEach(c => selectCourse.appendChild(new Option(c.name, c.id)));

        // Modal — events (all initially, updated on course change)
        populateModalEvents();
    }

    function populateEventFilter() {
        const selected = eventFilter.value;
        eventFilter.innerHTML = '<option value="">Wszystkie wydarzenia</option>';
        const filtered = filterCourse
            ? events.filter(e => String(e.course_id) === filterCourse)
            : events;
        filtered.forEach(e => eventFilter.appendChild(new Option(e.title, e.id)));
        eventFilter.value = selected;
    }

    function populateModalEvents(courseId = null) {
        const selected = selectEvent.value;
        selectEvent.innerHTML = '<option value="0">— brak —</option>';
        const filtered = courseId
            ? events.filter(e => e.course_id === courseId)
            : events;
        filtered.forEach(e => selectEvent.appendChild(new Option(e.title, e.id)));
        selectEvent.value = selected;
    }

    selectCourse.addEventListener('change', () => {
        const cid = parseInt(selectCourse.value, 10) || null;
        populateModalEvents(cid);
    });

    // ---- Render ----
    function renderList() {
        let filtered = notes.filter(n => {
            if (filterCourse && String(n.course_id) !== filterCourse) return false;
            if (filterEvent  && String(n.event_id)  !== filterEvent)  return false;
            if (filterSearch && !n.title.toLowerCase().includes(filterSearch)) return false;
            return true;
        });

        list.innerHTML = '';

        if (filtered.length === 0) {
            emptyEl.hidden = false;
            return;
        }

        emptyEl.hidden = true;
        filtered.forEach(n => list.appendChild(buildCard(n)));
    }

    function buildCard(note) {
        const course = courses.find(c => c.id === note.course_id);
        const event  = events.find(e => e.id === note.event_id);
        const date   = new Date(note.created_at).toLocaleDateString('pl-PL', {
            day: '2-digit', month: 'short', year: 'numeric',
        });

        const card = document.createElement('article');
        card.className  = 'nt-card';
        card.dataset.id = note.id;

        const metaHtml = [
            course ? `<span class="nt-badge nt-badge-course"><i class="fa-regular fa-book-open" aria-hidden="true"></i>${esc(course.name)}</span>` : '',
            event  ? `<span class="nt-badge nt-badge-event"><i class="fa-regular fa-calendar" aria-hidden="true"></i>${esc(event.title)}</span>`   : '',
        ].join('');

        card.innerHTML = `
            <div class="nt-card-actions">
                <button class="nt-btn-icon" title="Edytuj" aria-label="Edytuj ${esc(note.title)}">
                    <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                </button>
                <button class="nt-btn-icon nt-btn-icon--del" title="Usuń" aria-label="Usuń ${esc(note.title)}">
                    <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                </button>
            </div>
            <h3 class="nt-card-title">${esc(note.title)}</h3>
            ${note.content ? `<p class="nt-card-content">${esc(note.content)}</p>` : ''}
            ${metaHtml ? `<div class="nt-card-meta">${metaHtml}</div>` : ''}
            <span class="nt-card-date">${date}</span>
        `;

        card.querySelector('.nt-btn-icon:not(.nt-btn-icon--del)').addEventListener('click', () => openModal(note));
        card.querySelector('.nt-btn-icon--del').addEventListener('click', () => deleteNote(note));

        return card;
    }

    // ---- Modal ----
    function openModal(note = null) {
        editingId = note ? note.id : null;

        modalTitle.textContent  = note ? 'Edytuj Notatkę' : 'Nowa Notatka';
        inputId.value           = note ? note.id : '';
        inputTitle.value        = note ? note.title : '';
        inputContent.value      = note ? (note.content ?? '') : '';

        // Pre-select course in modal
        selectCourse.value = note?.course_id ?? 0;
        const courseId = parseInt(selectCourse.value, 10) || null;
        populateModalEvents(courseId);
        selectEvent.value  = note?.event_id  ?? 0;

        titleError.textContent = '';
        overlay.classList.add('open');
        requestAnimationFrame(() => inputTitle.focus());
    }

    function closeModal() {
        overlay.classList.remove('open');
        form.reset();
        editingId = null;
        titleError.textContent = '';
    }

    // ---- Submit ----
    async function handleSubmit(e) {
        e.preventDefault();
        titleError.textContent = '';

        const title    = inputTitle.value.trim();
        const content  = inputContent.value.trim() || null;
        const courseId = parseInt(selectCourse.value, 10) || null;
        const eventId  = parseInt(selectEvent.value, 10)  || null;

        if (!title) {
            titleError.textContent = 'Tytuł jest wymagany.';
            return;
        }

        saveBtn.disabled = true;

        const payload = { title, content, course_id: courseId ?? 0, event_id: eventId ?? 0 };

        try {
            if (editingId) {
                payload.id = editingId;
                const updated = await Api.post('/notes/update', payload);
                notes = notes.map(n => n.id === editingId ? updated : n);
                showToast('Notatka zaktualizowana.');
            } else {
                const created = await Api.post('/notes/create', payload);
                notes.unshift(created);
                showToast('Notatka dodana.');
            }
            closeModal();
            renderList();
        } catch (err) {
            showToast(err.message || 'Wystąpił błąd.', 'error');
        } finally {
            saveBtn.disabled = false;
        }
    }

    // ---- Delete ----
    async function deleteNote(note) {
        if (!confirm(`Usuń notatkę "${note.title}"?`)) return;
        try {
            await Api.post('/notes/delete', { id: note.id });
            notes = notes.filter(n => n.id !== note.id);
            renderList();
            showToast('Notatka usunięta.');
        } catch (err) {
            showToast(err.message || 'Błąd usuwania.', 'error');
        }
    }

    // ---- Toast ----
    function showToast(msg, type = 'success') {
        toast.textContent = msg;
        toast.className   = `nt-toast nt-toast--${type} show`;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
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
