<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

$pageTitle = $pageTitle ?? 'Dashboard';
$currentUser = [
    'nama'   => htmlspecialchars($_SESSION['nama'] ?? 'Admin', ENT_QUOTES, 'UTF-8'),
    'role'   => htmlspecialchars($_SESSION['role'] ?? 'admin', ENT_QUOTES, 'UTF-8'),
    'inisial'=> strtoupper(mb_substr($_SESSION['nama'] ?? 'A', 0, 1, 'UTF-8')),
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — MTs Al-Ihsan</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>assets/img/favicon.ico">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Icons & Framework -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<!-- OVERLAY (mobile sidebar backdrop) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<?php include_once __DIR__ . '/sidebar.php'; ?>

<!-- MAIN WRAPPER -->
<div class="main-wrapper" id="mainWrapper">

    <!-- TOP HEADER -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="breadcrumb-title d-none d-sm-block">
                <span class="page-label"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>

        <div class="topbar-right">
            <!-- Notification Bell -->
            <div class="topbar-btn-wrap" id="notifWrap">
                <button class="topbar-icon-btn" id="notifToggle" aria-label="Notifikasi">
                    <i class="far fa-bell"></i>
                    <span class="badge-dot" id="notifBadge"></span>
                </button>
                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <span>Notifikasi</span>
                        <button class="notif-clear" id="notifClear">Tandai semua dibaca</button>
                    </div>
                    <div class="notif-body" id="notifList">
                        <p class="notif-empty">Tidak ada notifikasi baru.</p>
                    </div>
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="user-menu" id="userMenu">
                <button class="user-trigger" id="userTrigger" aria-expanded="false">
                    <div class="user-avatar"><?= $currentUser['inisial'] ?></div>
                    <div class="user-info d-none d-md-block">
                        <span class="user-name"><?= $currentUser['nama'] ?></span>
                        <span class="user-role"><?= ucfirst($currentUser['role']) ?></span>
                    </div>
                    <i class="fas fa-chevron-down user-caret d-none d-md-inline"></i>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-info">
                        <div class="user-avatar lg"><?= $currentUser['inisial'] ?></div>
                        <div>
                            <p class="mb-0 fw-semibold"><?= $currentUser['nama'] ?></p>
                            <small class="text-muted"><?= ucfirst($currentUser['role']) ?></small>
                        </div>
                    </div>
                    <div class="user-dropdown-divider"></div>
                    <a href="<?= BASE_URL ?>modules/pengaturan/profile.php" class="user-dropdown-item">
                        <i class="fas fa-user-circle"></i> Profil Saya
                    </a>
                    <a href="<?= BASE_URL ?>modules/pengaturan/" class="user-dropdown-item">
                        <i class="fas fa-cog"></i> Pengaturan
                    </a>
                    <div class="user-dropdown-divider"></div>
                    <a href="<?= BASE_URL ?>logout.php" class="user-dropdown-item danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- PAGE CONTENT -->
    <main class="page-content">
        <!-- Flash Messages -->
        <?php 
        $flash = getFlash();
        if ($flash) {
            echo renderFlashMessage($flash);
        }
        ?>