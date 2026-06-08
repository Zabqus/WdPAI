/* =============================================
   SYNCU — dashboard.js
   Dashboard dynamic content (shared notes)
   ============================================= */

(function () {

    var grid = document.getElementById('db-shared-grid');
    if (!grid) return;

    Api.get('/api/shares/notes').then(function (notes) {
        grid.innerHTML = '';

        if (!notes || notes.length === 0) {
            grid.innerHTML = '<p class="db-shared-empty">Nikt nie udostępnił Ci jeszcze żadnych notatek.</p>';
            return;
        }

        var colors = ['#1b6871', '#416280', '#3f575b', '#7c9eb5', '#a9b4b5'];

        notes.slice(0, 4).forEach(function (note) {
            var initials = (note.owner_name || '?').slice(0, 2).toUpperCase();
            var color    = colors[note.owner_id % colors.length];
            var date     = new Date(note.created_at).toLocaleDateString('pl-PL', { day: '2-digit', month: 'short' });

            var card = document.createElement('div');
            card.className = 'db-note-card';
            card.innerHTML =
                '<div class="db-note-type">' +
                    '<i class="fa-regular fa-note-sticky" style="color:#1b6871;font-size:15px;"></i>' +
                    '<span class="db-note-type-label">NOTATKA &bull; ' + date + '</span>' +
                '</div>' +
                '<div class="db-note-title">' + esc(note.note_title) + '</div>' +
                '<div class="db-note-author">' +
                    '<div class="db-author-avatar" style="background:' + color + ';">' + initials + '</div>' +
                    '<span>Udostępnione przez ' + esc(note.owner_name) + '</span>' +
                '</div>';
            grid.appendChild(card);
        });
    }).catch(function () {
        grid.innerHTML = '<p class="db-shared-empty">Nie udało się załadować notatek.</p>';
    });

    function esc(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

})();
