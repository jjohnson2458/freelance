<?php
$activePage = $activePage ?? '';
$user = \Core\Auth::user();
?>
<nav id="sidebar" class="sidebar d-flex flex-column">
    <div class="sidebar-brand px-3 py-4">
        <a href="/dashboard" class="text-white text-decoration-none d-flex align-items-center">
            <i class="bi bi-briefcase-fill fs-4 me-2"></i>
            <span class="fs-5 fw-semibold">Freelance</span>
        </a>
    </div>
    <hr class="text-white-50 mx-3 my-0">
    <ul class="nav nav-pills flex-column px-2 py-3 flex-grow-1">
        <li class="nav-item mb-1">
            <a href="/dashboard" class="nav-link sidebar-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="/jobs" class="nav-link sidebar-link <?= $activePage === 'jobs' ? 'active' : '' ?>">
                <i class="bi bi-briefcase me-2"></i> Jobs
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="/proposals" class="nav-link sidebar-link <?= $activePage === 'proposals' ? 'active' : '' ?>">
                <i class="bi bi-file-text me-2"></i> Proposals
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="/resumes" class="nav-link sidebar-link <?= $activePage === 'resumes' ? 'active' : '' ?>">
                <i class="bi bi-file-person me-2"></i> Resumes
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="/talents" class="nav-link sidebar-link <?= $activePage === 'talents' ? 'active' : '' ?>">
                <i class="bi bi-stars me-2"></i> Talents
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="/rules" class="nav-link sidebar-link <?= $activePage === 'rules' ? 'active' : '' ?>">
                <i class="bi bi-list-check me-2"></i> Rules
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="/platforms" class="nav-link sidebar-link <?= $activePage === 'platforms' ? 'active' : '' ?>">
                <i class="bi bi-globe me-2"></i> Platforms
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="/calendar" class="nav-link sidebar-link <?= $activePage === 'calendar' ? 'active' : '' ?>">
                <i class="bi bi-calendar3 me-2"></i> Calendar
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="/guide" class="nav-link sidebar-link <?= $activePage === 'guide' ? 'active' : '' ?>">
                <i class="bi bi-question-circle me-2"></i> User Guide
            </a>
        </li>
    </ul>
    <div class="sidebar-footer px-3 py-3 border-top border-secondary">
        <?php if ($user): ?>
            <div class="d-flex align-items-center justify-content-between">
                <div class="text-white small">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= htmlspecialchars($user['name']) ?>
                </div>
                <a href="/logout" class="text-white-50 small text-decoration-none" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</nav>
