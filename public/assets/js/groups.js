/* =============================================
   SYNCU — groups.js
   Shared Notes Library page
   ============================================= */

(function () {

    let myNotes    = [];
    let sharedNotes = [];
    let toastTimer = null;

    // State for the share modal
    let activeNoteId = null;

    // DOM refs
    const myList      = document.getElementById('sg-my-list');
    const myEmpty     = document.getElementById('sg-my-empty');
    const myCount     = document.getElementById('sg-my-count');
    const sharedList  = document.getElementById('sg-shared-list');
    const sharedEmpty = document.getElementById('sg-shared-empty');
    const sharedCount = document.getElementById('sg-shared-count');
    const overlay     = document.getElementById('sg-modal-overlay');
    const modalClose  = document.getElementById('sg-modal-close');
    const sharesList  = document.getElementById('sg-shares-list');
    const emailInput  = document.getElementById('sg-share-email');
    const accessSel   = document.getElementById('sg-share-access');
    const shareBtn    = document.getElementById('sg-share-btn');
    const shareError  = document.getElementById('sg-share-error');
    const toast       = document.getElementById('sg-toast');

    // ---- Bind UI ----
    modalClose.addEventListener('click', closeShareModal);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeShareModal(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !overlay.hidden) closeShareModal();
    });
    shareBtn.addEventListener('click', handleGrant);
    emailInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); handleGrant(); }
    });

    // ---- Init ----
    loadAll();

    async function loadAll() {
        try {
            [myNotes, sharedNotes] = await Promise.all([
                Api.get('/api/notes'),
                Api.get('/api/shares/notes'),
            ]);
        } catch {
            showToast('Nie udało się załadować danych.', 'error');
            myList.innerHTML    = '';
            sharedList.innerHTML = '';
            return;
        }

        renderMyNotes();
        renderSharedNotes();
    }

    // =============================================
    //  Render: My Notes
    // =============================================

    function renderMyNotes() {
        myList.innerHTML = '';

        if (myNotes.length === 0) {
            myEmpty.hidden = false;
            return;
        }

        myEmpty.hidden = false; // keep hidden
        myEmpty.hidden = true;
        myCount.textContent = myNotes.length;
        myCount.hidden = false;

        myNotes.forEach(note => myList.appendChild(buildMyCard(note)));
    }

    function buildMyCard(note) {
        const date = new Date(note.created_at).toLocaleDateString('pl-PL', {
            day: '2-digit', month: 'short', year: 'numeric',
        });

        const art = document.createElement('article');
        art.className  = 'sg-card';
        art.dataset.id = note.id;

        const badgesHtml = buildBadges(note);

        art.innerHTML = `
            <div class="sg-card-top">
                <h3 class="sg-card-title">${esc(note.title)}</h3>
                <div class="sg-card-actions">
                    <button class="sg-card-btn sg-card-btn--share" title="Udostępnij" aria-label="Udostępnij ${esc(note.title)}">
                        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            ${note.content ? `<p class="sg-card-content">${esc(note.content)}</p>` : ''}
            ${badgesHtml ? `<div class="sg-card-meta">${badgesHtml}</div>` : ''}
            <div class="sg-card-footer">
                <span class="sg-card-date">${date}</span>
            </div>
        `;

        art.querySelector('.sg-card-btn--share').addEventListener('click', () => openShareModal(note));

        return art;
    }

    // =============================================
    //  Render: Shared With Me
    // =============================================

    function renderSharedNotes() {
        sharedList.innerHTML = '';

        if (sharedNotes.length === 0) {
            sharedEmpty.hidden = false;
            return;
        }

        sharedEmpty.hidden = true;
        sharedCount.textContent = sharedNotes.length;
        sharedCount.hidden = false;

        sharedNotes.forEach(note => sharedList.appendChild(buildSharedCard(note)));
    }

    function buildSharedCard(note) {
        const date = new Date(note.created_at).toLocaleDateString('pl-PL', {
            day: '2-digit', month: 'short', year: 'numeric',
        });

        const initials = (note.owner_name || '?').slice(0, 2).toUpperCase();
        const accessLabel = note.access === 'edit' ? 'Edycja' : 'Odczyt';
        const accessClass = note.access === 'edit' ? 'sg-card-badge--access-edit' : 'sg-card-badge--access-read';

        const art = document.createElement('article');
        art.className  = 'sg-card sg-card--shared';
        art.dataset.id = note.note_id;

        art.innerHTML = `
            <div class="sg-card-top">
                <h3 class="sg-card-title">${esc(note.note_title)}</h3>
                <div class="sg-card-actions">
                    <span class="sg-card-badge ${accessClass}" title="Poziom dostępu">
                        ${accessLabel}
                    </span>
                </div>
            </div>
            ${note.content ? `<p class="sg-card-content">${esc(note.content)}</p>` : ''}
            <div class="sg-card-owner">
                <div class="sg-card-owner-avatar">${esc(initials)}</div>
                <span>${esc(note.owner_name)}</span>
            </div>
            <div class="sg-card-footer">
                <span class="sg-card-date">${date}</span>
            </div>
        `;

        return art;
    }

    // =============================================
    //  Share Modal — open / close
    // =============================================

    function openShareModal(note) {
        activeNoteId = note.id;
        document.getElementById('sg-modal-title').textContent = `Udostępnij: ${note.title}`;
        emailInput.value = '';
        shareError.textContent = '';
        overlay.hidden = false;
        loadShareMembers();
        requestAnimationFrame(() => emailInput.focus());
    }

    function closeShareModal() {
        overlay.hidden = true;
        activeNoteId = null;
        shareError.textContent = '';
    }

    // =============================================
    //  Share Modal — load current members
    // =============================================

    async function loadShareMembers() {
        sharesList.innerHTML = '<span class="sg-shares-loading">Ładowanie&hellip;</span>';
        try {
            const members = await Api.get(`/api/shares/notes?note_id=${activeNoteId}`);
            renderShareMembers(members);
        } catch {
            sharesList.innerHTML = '<span class="sg-shares-loading">Błąd ładowania.</span>';
        }
    }

    function renderShareMembers(members) {
        sharesList.innerHTML = '';

        if (!members || members.length === 0) {
            sharesList.innerHTML = '<span class="sg-shares-empty">Notatka nie jest jeszcze udostępniona.</span>';
            return;
        }

        members.forEach(m => {
            const initials = (m.username || '?').slice(0, 2).toUpperCase();
            const accessClass = m.access === 'edit' ? 'sg-member-access--edit' : 'sg-member-access--read';
            const accessLabel = m.access === 'edit' ? 'Edycja' : 'Odczyt';

            const row = document.createElement('div');
            row.className = 'sg-share-member';
            row.innerHTML = `
                <div class="sg-member-avatar">${esc(initials)}</div>
                <div class="sg-member-info">
                    <div class="sg-member-name">${esc(m.username)}</div>
                    <div class="sg-member-email">${esc(m.email)}</div>
                </div>
                <span class="sg-member-access ${accessClass}">${accessLabel}</span>
                <button class="sg-member-revoke" title="Cofnij dostęp" aria-label="Cofnij dostęp dla ${esc(m.username)}">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            `;

            row.querySelector('.sg-member-revoke').addEventListener('click', () => handleRevoke(m.user_id));
            sharesList.appendChild(row);
        });
    }

    // =============================================
    //  Share Modal — grant access
    // =============================================

    async function handleGrant() {
        const email  = emailInput.value.trim();
        const access = accessSel.value;

        shareError.textContent = '';

        if (!email) {
            shareError.textContent = 'Podaj adres e-mail.';
            emailInput.focus();
            return;
        }

        shareBtn.disabled = true;
        try {
            await Api.post('/shares/note/grant', { note_id: activeNoteId, email, access });
            emailInput.value = '';
            showToast('Dostęp został przyznany.');
            loadShareMembers();
        } catch (err) {
            shareError.textContent = err.message || 'Nie udało się udostępnić notatki.';
        } finally {
            shareBtn.disabled = false;
        }
    }

    // =============================================
    //  Share Modal — revoke access
    // =============================================

    async function handleRevoke(recipientUserId) {
        try {
            await Api.post('/shares/note/revoke', { note_id: activeNoteId, user_id: recipientUserId });
            showToast('Dostęp został cofnięty.');
            loadShareMembers();
        } catch (err) {
            showToast(err.message || 'Błąd cofania dostępu.', 'error');
        }
    }

    // =============================================
    //  Helpers
    // =============================================

    function buildBadges(note) {
        const parts = [];
        if (note.course_id) {
            parts.push(`<span class="sg-card-badge sg-card-badge--course">
                <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                Przedmiot
            </span>`);
        }
        if (note.event_id) {
            parts.push(`<span class="sg-card-badge sg-card-badge--event">
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                Wydarzenie
            </span>`);
        }
        return parts.join('');
    }

    function showToast(msg, type = 'success') {
        toast.textContent = msg;
        toast.className   = `sg-toast sg-toast--${type} show`;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
    }

    function esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

})();
