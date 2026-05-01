/* =============================================
   SYNCU — courses.js
   Course CRUD via Fetch API
   ============================================= */

(function () {

    const COLORS = [
        '#6c63ff', '#1b6871', '#416280', '#27ae60',
        '#e67e22', '#e74c3c', '#8e44ad', '#2980b9',
        '#16a085', '#3f575b',
    ];

    let courses     = [];
    let editingId   = null;
    let selColor    = COLORS[0];
    let toastTimer  = null;

    // DOM refs
    const grid          = document.getElementById('cr-grid');
    const emptyEl       = document.getElementById('cr-empty');
    const overlay       = document.getElementById('cr-modal-overlay');
    const modalTitle    = document.getElementById('cr-modal-title');
    const form          = document.getElementById('cr-form');
    const inputId       = document.getElementById('cr-id');
    const inputName     = document.getElementById('cr-name');
    const inputDesc     = document.getElementById('cr-desc');
    const nameError     = document.getElementById('cr-name-error');
    const swatchesEl    = document.getElementById('cr-swatches');
    const saveBtn       = document.getElementById('cr-btn-save');
    const toast         = document.getElementById('cr-toast');

    // ---- Bind events ----
    document.getElementById('cr-btn-new').addEventListener('click', () => openModal());
    document.getElementById('cr-btn-empty').addEventListener('click', () => openModal());
    document.getElementById('cr-modal-close').addEventListener('click', closeModal);
    document.getElementById('cr-btn-cancel').addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
    form.addEventListener('submit', handleSubmit);
    document.getElementById('cr-search').addEventListener('input', filterCards);

    // Keyboard: Escape closes modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('open')) closeModal();
    });

    buildSwatches();
    loadCourses();

    // ---- Load ----
    async function loadCourses() {
        try {
            courses = await Api.get('/api/courses');
            renderGrid();
        } catch {
            showToast('Nie udało się załadować danych.', 'error');
        }
    }

    // ---- Render ----
    function renderGrid() {
        grid.innerHTML = '';

        if (courses.length === 0) {
            emptyEl.hidden = false;
            return;
        }

        emptyEl.hidden = true;
        courses.forEach(c => grid.appendChild(buildCard(c)));
    }

    function buildCard(c) {
        const card = document.createElement('article');
        card.className   = 'cr-card';
        card.dataset.id   = c.id;
        card.dataset.name = c.name.toLowerCase();

        const date    = new Date(c.created_at);
        const dateStr = date.toLocaleDateString('pl-PL', { year: 'numeric', month: 'short', day: 'numeric' });

        card.innerHTML = `
            <div class="cr-card-stripe" style="background:${esc(c.color)};"></div>
            <div class="cr-card-body">
                <div class="cr-card-top">
                    <div class="cr-card-dot" style="background:${esc(c.color)};"></div>
                    <h3 class="cr-card-name">${esc(c.name)}</h3>
                </div>
                <p class="cr-card-desc">${c.description ? esc(c.description) : '<em>Brak opisu</em>'}</p>
                <div class="cr-card-footer">
                    <span class="cr-card-date">
                        <i class="fa-regular fa-calendar-plus" aria-hidden="true"></i>
                        ${dateStr}
                    </span>
                    <div class="cr-card-actions">
                        <button class="cr-btn-edit" title="Edytuj" aria-label="Edytuj ${esc(c.name)}">
                            <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                        </button>
                        <button class="cr-btn-delete" title="Usuń" aria-label="Usuń ${esc(c.name)}">
                            <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        card.querySelector('.cr-btn-edit').addEventListener('click', () => openModal(c));
        card.querySelector('.cr-btn-delete').addEventListener('click', () => deleteCourse(c));

        return card;
    }

    // ---- Search filter ----
    function filterCards() {
        const q = document.getElementById('cr-search').value.toLowerCase();
        document.querySelectorAll('.cr-card').forEach(card => {
            card.hidden = !card.dataset.name.includes(q);
        });
    }

    // ---- Modal ----
    function buildSwatches() {
        swatchesEl.innerHTML = '';
        COLORS.forEach(color => {
            const btn = document.createElement('button');
            btn.type            = 'button';
            btn.className       = 'cr-swatch';
            btn.style.background = color;
            btn.style.color      = color;   // used by box-shadow currentColor trick
            btn.title           = color;
            btn.dataset.color   = color;
            btn.setAttribute('aria-pressed', color === selColor ? 'true' : 'false');
            btn.addEventListener('click', () => selectColor(color));
            swatchesEl.appendChild(btn);
        });
        markSelected(selColor);
    }

    function selectColor(color) {
        selColor = color;
        markSelected(color);
    }

    function markSelected(color) {
        swatchesEl.querySelectorAll('.cr-swatch').forEach(s => {
            const active = s.dataset.color === color;
            s.classList.toggle('selected', active);
            s.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function openModal(course = null) {
        editingId = course ? course.id : null;

        modalTitle.textContent = course ? 'Edytuj Przedmiot' : 'Nowy Przedmiot';
        inputId.value    = course ? course.id : '';
        inputName.value  = course ? course.name : '';
        inputDesc.value  = course ? (course.description ?? '') : '';
        nameError.textContent = '';

        const initColor = course ? course.color : COLORS[0];
        if (!COLORS.includes(initColor)) {
            selColor = initColor;
            markSelected('__none__');
        } else {
            selectColor(initColor);
        }

        overlay.classList.add('open');
        requestAnimationFrame(() => inputName.focus());
    }

    function closeModal() {
        overlay.classList.remove('open');
        form.reset();
        editingId = null;
        nameError.textContent = '';
        selectColor(COLORS[0]);
    }

    // ---- Submit ----
    async function handleSubmit(e) {
        e.preventDefault();
        nameError.textContent = '';

        const name  = inputName.value.trim();
        const desc  = inputDesc.value.trim();

        if (!name) {
            nameError.textContent = 'Nazwa jest wymagana.';
            inputName.focus();
            return;
        }

        saveBtn.disabled = true;

        try {
            if (editingId) {
                await Api.post('/courses/update', { id: editingId, name, description: desc, color: selColor });
                showToast('Przedmiot zaktualizowany.');
            } else {
                await Api.post('/courses/create', { name, description: desc, color: selColor });
                showToast('Przedmiot dodany.');
            }
            closeModal();
            await loadCourses();
        } catch (err) {
            showToast(err.message || 'Wystąpił błąd.', 'error');
        } finally {
            saveBtn.disabled = false;
        }
    }

    // ---- Delete ----
    async function deleteCourse(course) {
        const confirmed = confirm(
            `Usuń "${course.name}"?\n\nUsunięcie przedmiotu usunie też wszystkie powiązane wydarzenia i zadania.`
        );
        if (!confirmed) return;

        try {
            await Api.post('/courses/delete', { id: course.id });
            showToast('Przedmiot usunięty.');
            await loadCourses();
        } catch (err) {
            showToast(err.message || 'Błąd usuwania.', 'error');
        }
    }

    // ---- Toast ----
    function showToast(msg, type = 'success') {
        toast.textContent = msg;
        toast.className   = `cr-toast cr-toast--${type} show`;
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
