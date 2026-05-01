/* =============================================
   SYNCU — tasks.js
   Task checklist panel (per-event, inline)
   ============================================= */

const TaskPanel = (function () {

    // Cache: eventId → Task[]
    const cache = {};

    // ---- Public API ----

    async function toggle(eventId, panelEl, badgeEl) {
        if (panelEl.hidden) {
            panelEl.hidden = false;
            if (!cache[eventId]) {
                await load(eventId, panelEl, badgeEl);
            } else {
                render(eventId, panelEl, badgeEl);
            }
        } else {
            panelEl.hidden = true;
        }
    }

    // Force-refresh a panel (e.g. after event reload)
    async function refresh(eventId, panelEl, badgeEl) {
        delete cache[eventId];
        if (!panelEl.hidden) {
            await load(eventId, panelEl, badgeEl);
        }
        updateBadge(eventId, badgeEl);
    }

    // ---- Load from API ----

    async function load(eventId, panelEl, badgeEl) {
        panelEl.innerHTML = '<div class="tk-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>';
        try {
            const tasks = await Api.get(`/api/tasks?event_id=${eventId}`);
            cache[eventId] = tasks;
            render(eventId, panelEl, badgeEl);
        } catch {
            panelEl.innerHTML = '<p class="tk-error">Nie udało się załadować zadań.</p>';
        }
    }

    // ---- Render panel ----

    function render(eventId, panelEl, badgeEl) {
        const tasks = cache[eventId] ?? [];
        updateBadge(eventId, badgeEl);

        const done  = tasks.filter(t => t.is_done).length;
        const total = tasks.length;
        const pct   = total === 0 ? 0 : Math.round(done / total * 100);

        panelEl.innerHTML = `
            <div class="tk-inner">
                <div class="tk-header">
                    <span class="tk-header-title">
                        <i class="fa-regular fa-list-check" aria-hidden="true"></i>
                        Zadania
                    </span>
                    <span class="tk-header-count">${done} / ${total} ukończono</span>
                </div>
                <div class="tk-progress-wrap">
                    <div class="tk-progress-bar">
                        <div class="tk-progress-fill" style="width:${pct}%"></div>
                    </div>
                    <span class="tk-pct">${pct}%</span>
                </div>
                <ul class="tk-list" id="tk-list-${eventId}"></ul>
                <div class="tk-add-row">
                    <input class="tk-add-input" id="tk-input-${eventId}"
                           type="text" maxlength="255"
                           placeholder="Nowe zadanie..." autocomplete="off">
                    <button class="tk-add-btn" data-event-id="${eventId}">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        Dodaj
                    </button>
                </div>
            </div>
        `;

        renderList(eventId, panelEl, badgeEl);
        bindAddRow(eventId, panelEl, badgeEl);
    }

    function renderList(eventId, panelEl, badgeEl) {
        const listEl = panelEl.querySelector(`#tk-list-${eventId}`);
        if (!listEl) return;

        const tasks = cache[eventId] ?? [];
        listEl.innerHTML = '';

        if (tasks.length === 0) {
            listEl.innerHTML = '<li class="tk-empty-hint">Brak zadań – dodaj pierwsze poniżej.</li>';
            return;
        }

        tasks.forEach((task, idx) => {
            const li = buildItem(task, idx, tasks.length, eventId, panelEl, badgeEl);
            listEl.appendChild(li);
        });
    }

    function buildItem(task, idx, total, eventId, panelEl, badgeEl) {
        const li = document.createElement('li');
        li.className = 'tk-item' + (task.is_done ? ' tk-done' : '');
        li.dataset.taskId = task.id;

        li.innerHTML = `
            <button class="tk-check" aria-pressed="${task.is_done}" title="${task.is_done ? 'Odznacz' : 'Zaznacz'}">
                ${task.is_done ? '<i class="fa-solid fa-check" aria-hidden="true"></i>' : ''}
            </button>
            <span class="tk-item-title" title="Kliknij dwukrotnie by edytować">${esc(task.title)}</span>
            <div class="tk-item-actions">
                <button class="tk-btn-move tk-btn-up" ${idx === 0 ? 'disabled' : ''} title="Przenieś wyżej" aria-label="Przenieś wyżej">
                    <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
                </button>
                <button class="tk-btn-move tk-btn-down" ${idx === total - 1 ? 'disabled' : ''} title="Przenieś niżej" aria-label="Przenieś niżej">
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>
                <button class="tk-btn-del" title="Usuń zadanie" aria-label="Usuń zadanie">
                    <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                </button>
            </div>
        `;

        // Toggle done
        li.querySelector('.tk-check').addEventListener('click', () => handleToggle(task, eventId, panelEl, badgeEl));

        // Inline title edit on double-click
        const titleEl = li.querySelector('.tk-item-title');
        titleEl.addEventListener('dblclick', () => startEdit(titleEl, task, eventId, panelEl, badgeEl));

        // Move up
        const btnUp = li.querySelector('.tk-btn-up');
        if (!btnUp.disabled) {
            btnUp.addEventListener('click', () => handleMove(eventId, idx, idx - 1, panelEl, badgeEl));
        }

        // Move down
        const btnDown = li.querySelector('.tk-btn-down');
        if (!btnDown.disabled) {
            btnDown.addEventListener('click', () => handleMove(eventId, idx, idx + 1, panelEl, badgeEl));
        }

        // Delete
        li.querySelector('.tk-btn-del').addEventListener('click', () => handleDelete(task, eventId, panelEl, badgeEl));

        return li;
    }

    // ---- Inline Edit ----

    function startEdit(titleEl, task, eventId, panelEl, badgeEl) {
        if (titleEl.querySelector('input')) return; // already editing

        const prev = task.title;
        titleEl.innerHTML = '';

        const input = document.createElement('input');
        input.className = 'tk-edit-input';
        input.type      = 'text';
        input.value     = prev;
        input.maxLength = 255;
        titleEl.appendChild(input);
        input.focus();
        input.select();

        const commit = async () => {
            const val = input.value.trim();
            if (val === '' || val === prev) {
                titleEl.textContent = prev;
                return;
            }
            try {
                await Api.post('/tasks/update', { id: task.id, title: val });
                task.title = val;
                titleEl.textContent = val;
            } catch {
                titleEl.textContent = prev;
            }
        };

        input.addEventListener('blur', commit);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter')  { e.preventDefault(); input.blur(); }
            if (e.key === 'Escape') { input.value = prev; input.blur(); }
        });
    }

    // ---- Handlers ----

    async function handleToggle(task, eventId, panelEl, badgeEl) {
        const newState = !task.is_done;
        try {
            await Api.post('/tasks/toggle', { id: task.id, is_done: newState });
            task.is_done = newState;
            render(eventId, panelEl, badgeEl);
        } catch { /* silent */ }
    }

    async function handleMove(eventId, fromIdx, toIdx, panelEl, badgeEl) {
        const tasks = cache[eventId];
        if (!tasks || toIdx < 0 || toIdx >= tasks.length) return;

        // Swap in local array
        [tasks[fromIdx], tasks[toIdx]] = [tasks[toIdx], tasks[fromIdx]];

        // Optimistic render
        renderList(eventId, panelEl, badgeEl);

        // Persist
        try {
            await Api.post('/tasks/reorder', {
                event_id: eventId,
                order:    tasks.map(t => t.id),
            });
        } catch {
            // Swap back on failure
            [tasks[fromIdx], tasks[toIdx]] = [tasks[toIdx], tasks[fromIdx]];
            renderList(eventId, panelEl, badgeEl);
        }
    }

    async function handleDelete(task, eventId, panelEl, badgeEl) {
        if (!confirm(`Usuń zadanie "${task.title}"?`)) return;
        try {
            await Api.post('/tasks/delete', { id: task.id });
            cache[eventId] = (cache[eventId] ?? []).filter(t => t.id !== task.id);
            render(eventId, panelEl, badgeEl);
        } catch { /* silent */ }
    }

    // ---- Add row ----

    function bindAddRow(eventId, panelEl, badgeEl) {
        const input = panelEl.querySelector(`#tk-input-${eventId}`);
        const btn   = panelEl.querySelector('.tk-add-btn');
        if (!input || !btn) return;

        const submit = async () => {
            const title = input.value.trim();
            if (!title) { input.focus(); return; }
            btn.disabled  = true;
            input.disabled = true;
            try {
                const task = await Api.post('/tasks/create', { event_id: eventId, title });
                if (!cache[eventId]) cache[eventId] = [];
                cache[eventId].push(task);
                input.value = '';
                render(eventId, panelEl, badgeEl);
            } catch { /* silent */ } finally {
                btn.disabled   = false;
                input.disabled = false;
                input.focus();
            }
        };

        btn.addEventListener('click', submit);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); submit(); }
        });
    }

    // ---- Badge ----

    function updateBadge(eventId, badgeEl) {
        if (!badgeEl) return;
        const tasks = cache[eventId] ?? [];
        const total = tasks.length;
        const done  = tasks.filter(t => t.is_done).length;
        badgeEl.textContent = total > 0 ? `${done}/${total}` : '';
        badgeEl.hidden      = total === 0;
    }

    // ---- Utils ----

    function esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    return { toggle, refresh };

})();
