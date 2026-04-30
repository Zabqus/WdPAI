<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = 'Study Groups — SyncU';
    $extraCss = ['dashboard', 'groups'];
    include __DIR__ . '/partials/head.php';
    ?>
</head>
<body class="db-page">

<!-- ==================== TOP NAVBAR ==================== -->
<header class="db-navbar">
    <div class="db-navbar-inner">

        <div class="db-search-wrap">
            <svg class="db-search-icon" width="11" height="11" viewBox="0 0 11 11" fill="none" aria-hidden="true">
                <path d="M10 10L7.45 7.45M8.5 4.75C8.5 6.82 6.82 8.5 4.75 8.5C2.68 8.5 1 6.82 1 4.75C1 2.68 2.68 1 4.75 1C6.82 1 8.5 2.68 8.5 4.75Z"
                      stroke="#576162" stroke-opacity="0.6" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <label for="sg-search" class="sr-only">Search resources, notes</label>
            <input type="text" id="sg-search" class="db-search-input" placeholder="Search resources, notes...">
        </div>

        <nav class="db-nav-links">
            <a href="/dashboard" class="db-nav-link">Dashboard</a>
            <a href="/calendar"  class="db-nav-link">Calendar</a>
            <a href="/groups"    class="db-nav-link active">Study Groups</a>
            <?php if (Session::get('user_role') === 'admin'): ?>
                <a href="/admin" class="db-nav-link db-nav-link--admin">Admin</a>
            <?php endif; ?>
        </nav>

        <div class="db-navbar-right">
            <div class="db-nav-icons">
                <button class="db-icon-btn" title="Powiadomienia"><i class="fa-regular fa-bell"></i></button>
                <button class="db-icon-btn" title="Ustawienia"><i class="fa-regular fa-gear"></i></button>
            </div>
            <div class="db-identity">
                <div class="db-user-avatar"><?= strtoupper(substr($userName ?? 'AL', 0, 2)) ?></div>
                <span class="db-brand">SyncU</span>
            </div>
        </div>

    </div>
</header>

<!-- ==================== MAIN CANVAS ==================== -->
<main class="sg-canvas">

    <!-- Header -->
    <div class="sg-header">
        <h1 class="sg-title">Shared Notes Library</h1>
        <p class="sg-subtitle">Access curated study resources, collaborative lecture notes, and research materials from your academic circles.</p>
    </div>

    <!-- Filter & Action Bar -->
    <div class="sg-filterbar">
        <div class="sg-tabs">
            <button class="sg-tab active">All Resources</button>
            <button class="sg-tab">Public Groups</button>
            <button class="sg-tab">Private Library</button>
        </div>
        <div class="sg-actions">
            <button class="sg-btn-filter">
                <svg width="15" height="10" viewBox="0 0 15 10" fill="none" aria-hidden="true">
                    <path d="M1 1h13M3 5h9M5 9h5" stroke="#476063" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                Filter
            </button>
            <button class="sg-btn-upload">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                    <path d="M6 1v10M1 6h10" stroke="#eafcff" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                Upload Notes
            </button>
        </div>
    </div>

    <!-- Bento Grid -->
    <div class="sg-bento">

        <!-- ===== ROW 1 ===== -->

        <!-- Featured card (col 1-8) — image placeholder, user adds bg later -->
        <div class="sg-featured-card">
            <div class="sg-featured-overlay">
                <div class="sg-featured-tags">
                    <span class="sg-tag sg-tag-teal">Featured Study</span>
                    <span class="sg-tag sg-tag-glass">Advanced Physics</span>
                </div>
                <h2 class="sg-featured-title">Quantum Mechanics: Final Review Series</h2>
                <p class="sg-featured-desc">Complete breakdown of particle-wave duality, Schrödinger's equation, and quantum entanglement with solved problem sets&hellip;</p>
                <div class="sg-featured-footer">
                    <div class="sg-featured-author">
                        <div class="sg-author-avatar">AT</div>
                        <span>Dr. Aris Thorne &amp; 4 others</span>
                    </div>
                    <button class="sg-open-btn">Open Library</button>
                </div>
            </div>
        </div>

        <!-- Subject card — Macroeconomics (col 9-12) -->
        <div class="sg-subject-card">
            <div class="sg-subject-top">
                <div class="sg-subject-icon-wrap sg-icon-blue">
                    <i class="fa-solid fa-chart-line" style="font-size:13px;color:#2e506d;"></i>
                </div>
                <h3 class="sg-subject-title">Macroeconomics 101</h3>
                <p class="sg-subject-desc">Comprehensive summaries of fiscal policies and global trade dynamics. Updated yesterday.</p>
            </div>
            <div class="sg-subject-footer">
                <span class="sg-subject-meta">12 Files &bull; 1.4 GB</span>
                <i class="fa-solid fa-arrow-right" style="font-size:12px;color:#2e506d;"></i>
            </div>
        </div>

        <!-- ===== ROW 2 — Note cards ===== -->

        <!-- Organic Chemistry II (col 1-4) -->
        <div class="sg-note-card sg-nc-1">
            <div class="sg-note-top">
                <div class="sg-note-icon-wrap" style="background:#e7eff0;">
                    <i class="fa-regular fa-file-lines" style="font-size:14px;color:#576162;"></i>
                </div>
                <span class="sg-badge sg-badge-private">PRIVATE</span>
            </div>
            <h4 class="sg-note-title">Organic Chemistry II</h4>
            <p class="sg-note-desc">Detailed reaction mechanisms for aromatic compounds. Includes color-coded diagrams for electron flow.</p>
            <div class="sg-note-footer">
                <div class="sg-avatar-stack">
                    <div class="sg-mini-av" style="background:#e2e8f0;"></div>
                    <div class="sg-mini-av" style="background:#cbd5e1;margin-left:-8px;"></div>
                </div>
                <span class="sg-note-meta">+2 collaborators</span>
            </div>
        </div>

        <!-- Modern Architecture History (col 5-8) -->
        <div class="sg-note-card sg-nc-2">
            <div class="sg-note-top">
                <div class="sg-note-icon-wrap" style="background:rgba(169,238,248,0.4);">
                    <i class="fa-regular fa-image" style="font-size:14px;color:#1b6871;"></i>
                </div>
                <span class="sg-badge sg-badge-public">PUBLIC</span>
            </div>
            <h4 class="sg-note-title">Modern Architecture History</h4>
            <p class="sg-note-desc">Photo archive and lecture transcripts from the &lsquo;Bauhaus to Brutalism&rsquo; module. Highly visual content.</p>
            <div class="sg-note-footer-row">
                <span class="sg-note-meta">456 Views</span>
                <i class="fa-regular fa-bookmark" style="font-size:14px;color:#576162;cursor:pointer;"></i>
            </div>
        </div>

        <!-- Data Structures & Algos (col 9-12) -->
        <div class="sg-note-card sg-nc-3">
            <div class="sg-note-top">
                <div class="sg-note-icon-wrap" style="background:rgba(205,231,235,0.4);">
                    <i class="fa-solid fa-code" style="font-size:13px;color:#3d5659;"></i>
                </div>
                <span class="sg-badge sg-badge-draft">DRAFT</span>
            </div>
            <h4 class="sg-note-title">Data Structures &amp; Algos</h4>
            <p class="sg-note-desc">Personal notes on Big O notation, graph traversal algorithms, and hash map implementations in Python.</p>
            <div class="sg-note-footer-row">
                <div class="sg-progress-track">
                    <div class="sg-progress-fill" style="width:75%;"></div>
                </div>
                <span class="sg-note-meta">75% complete</span>
            </div>
        </div>

        <!-- ===== ROW 3 — Research Hub ===== -->
        <div class="sg-hub-card">
            <!-- image placeholder — user replaces with <img> or background-image -->
            <div class="sg-hub-img" aria-hidden="true"></div>
            <div class="sg-hub-content">
                <h3 class="sg-hub-title">Inter-University Research Hub</h3>
                <p class="sg-hub-desc">Join the massive open library curated by students from over 50 universities. Access peer-reviewed study guides and previous exam banks globally.</p>
                <div class="sg-hub-actions">
                    <button class="sg-btn-join">Join Global Hub</button>
                    <button class="sg-btn-browse">Browse Institutions</button>
                </div>
            </div>
        </div>

        <!-- ===== ROW 4 — Bottom cards ===== -->

        <!-- Recent Contributions (col 1-6) -->
        <div class="sg-bottom-card sg-contributions">
            <div class="sg-bottom-header">
                <h3 class="sg-bottom-title">Recent Contributions</h3>
                <a href="#" class="sg-view-all">VIEW ALL</a>
            </div>
            <div class="sg-file-list">
                <div class="sg-file-item">
                    <div class="sg-file-icon-wrap">
                        <i class="fa-regular fa-file-pdf" style="font-size:16px;color:#576162;"></i>
                    </div>
                    <div class="sg-file-info">
                        <div class="sg-file-name">Neurobiology_Lab_Report.pdf</div>
                        <div class="sg-file-meta">Shared by Sarah Jenkins &bull; 2h ago</div>
                    </div>
                    <button class="sg-file-menu" aria-label="Options">
                        <span></span><span></span><span></span>
                    </button>
                </div>
                <div class="sg-file-item">
                    <div class="sg-file-icon-wrap">
                        <i class="fa-regular fa-file-word" style="font-size:16px;color:#576162;"></i>
                    </div>
                    <div class="sg-file-info">
                        <div class="sg-file-name">Ethics_In_AI_Summary.docx</div>
                        <div class="sg-file-meta">Shared by Marcus Chen &bull; 5h ago</div>
                    </div>
                    <button class="sg-file-menu" aria-label="Options">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Trending Subjects (col 7-12) -->
        <div class="sg-bottom-card sg-trending">
            <div class="sg-bottom-header">
                <h3 class="sg-bottom-title">Trending Subjects</h3>
                <button class="sg-refresh-btn" aria-label="Refresh">
                    <i class="fa-solid fa-rotate" style="font-size:12px;color:#576162;"></i>
                </button>
            </div>
            <div class="sg-tag-cloud">
                <span class="sg-topic-tag">Machine Learning</span>
                <span class="sg-topic-tag">Psychology 101</span>
                <span class="sg-topic-tag">Political Theory</span>
                <span class="sg-topic-tag">Game Dev</span>
                <span class="sg-topic-tag">French B2</span>
                <span class="sg-topic-tag">Calculus III</span>
            </div>
        </div>

    </div><!-- /sg-bento -->

</main>

<!-- FAB -->
<button class="db-fab" title="Upload notes">
    <i class="fa-solid fa-plus"></i>
</button>

<script src="/public/assets/js/main.js" defer></script>
</body>
</html>
