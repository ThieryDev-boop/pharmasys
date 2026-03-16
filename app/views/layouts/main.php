<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaSys</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background-color: #f4f8fc; }

        /* Sidebar */
        .sidebar {
            height: 100vh;
            width: 240px;
            background: linear-gradient(180deg, #8B0000 0%, #7A0C0C 100%);
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            padding-top: 0;
            transition: width 0.2s;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.25); border-radius: 2px; }
        .sidebar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.45); }
        .sidebar-logo {
            padding: 20px 20px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            margin-bottom: 10px;
        }
        .sidebar-logo .logo-text {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: 1px;
        }
        .sidebar-logo .logo-sub {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.6);
            display: block;
        }
        .nav-section-title {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.4);
            padding: 12px 20px 4px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 9px 20px;
            border-radius: 0;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.15s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.15);
            border-left: 3px solid #fff;
        }
        .sidebar .nav-link i { font-size: 1rem; width: 20px; }

        /* Main content */
        .main-content {
            margin-left: 240px;
            min-height: 100vh;
        }
        .topbar {
            background-color: #8B0000;
            border-bottom: 1px solid #e0eaf5;
            padding: 12px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 99;
            height: 60px;
        }
        .topbar .page-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            margin: 0;
        }
        .user-badge {
            background: #f0f7ff;
            border: 1px solid #d0e5f7;
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 0.85rem;
            color: #2E75B6;
        }
        .role-badge {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 10px;
        }
        .page-body { padding: 25px; }
    </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->

<div class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-text">
            <img src="<?= BASE_URL ?>/assets/img/logo.jpeg" 
             alt="Logo PharmaSys" 
             class="img-fluid rounded mb-2"
             style="max-height: 60px; object-fit: contain;">
        </div>
        <span class="logo-sub">Santé et vie</span>
    </div>

    <nav>
        <!-- Tableau de bord -->
        <div class="nav-section-title">Principal</div>
        <a href="<?= BASE_URL ?>/dashboard" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false) ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>

        <!-- Stock -->
        <div class="nav-section-title">Stock</div>
        <a href="<?= BASE_URL ?>/medicaments" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/medicaments') !== false) ? 'active' : '' ?>">
            <i class="bi bi-capsule"></i> Medicaments
        </a>
        <a href="<?= BASE_URL ?>/lots" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/lots') !== false) ? 'active' : '' ?>">
            <i class="bi bi-boxes"></i> Lots
        </a>

        <!-- Ventes -->
        <div class="nav-section-title">Ventes</div>
        <a href="<?= BASE_URL ?>/ventes/create" class="nav-link">
            <i class="bi bi-cart-plus"></i> Nouvelle vente
        </a>
        <a href="<?= BASE_URL ?>/ventes" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/ventes') !== false && strpos($_SERVER['REQUEST_URI'], '/create') === false) ? 'active' : '' ?>">
            <i class="bi bi-receipt"></i> Historique ventes
        </a>
        <a href="<?= BASE_URL ?>/clients" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/clients') !== false) ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Clients
        </a>

        <!-- Fournisseurs -->
        <div class="nav-section-title">Approvisionnement</div>
        <a href="<?= BASE_URL ?>/fournisseurs" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/fournisseurs') !== false) ? 'active' : '' ?>">
            <i class="bi bi-truck"></i> Fournisseurs
        </a>
        <a href="<?= BASE_URL ?>/commandes" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/commandes') !== false) ? 'active' : '' ?>">
            <i class="bi bi-clipboard-check"></i> Commandes
        </a>

        <!-- Rapports -->
        <div class="nav-section-title">Rapports</div>
        <a href="<?= BASE_URL ?>/rapports" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/rapports') !== false) ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-line"></i> Rapports & Exports
        </a>

        <?php if (Auth::hasRole('administrateur')): ?>
        <!-- Admin -->
        <div class="nav-section-title">Administration</div>
        <a href="<?= BASE_URL ?>/admin/utilisateurs" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin') !== false) ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i> Utilisateurs
        </a>
        <a href="<?= BASE_URL ?>/admin/sauvegardes" class="nav-link">
            <i class="bi bi-cloud-arrow-down"></i> Sauvegardes
        </a>
        <?php endif; ?>
    </nav>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

    <!-- Topbar -->
    <div class="topbar">
        <h1 class="page-title">
            <i class="bi bi-capsule-pill text-white me-2"></i>CLINIQUE COEUR ET VIE
        </h1>
        <div class="d-flex align-items-center gap-3">
            <!-- Badge role -->
            <?php
            $roleColors = [
                'administrateur' => '#fff',
                'pharmacien'     => 'primary',
                'caissier'       => 'success'
            ];
            $role = Auth::userRole();
            $color = $roleColors[$role] ?? 'secondary';
            ?>
            <span class="badge bg-<?= $color ?> role-badge text-capitalize">
                <?= htmlspecialchars(ucfirst($role)) ?>
            </span>

            <!-- Nom utilisateur -->
            <a href="<?= BASE_URL ?>/profil" class="user-badge text-decoration-none">
                <i class="bi bi-person-circle me-1"></i>
                <?= htmlspecialchars(Auth::userNom()) ?>
            </a>

            <!-- Deconnexion -->
            <a href="<?= BASE_URL ?>/auth/logout" class="btn btn-sm btn-outline-danger" title="Deconnexion">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Corps de la page -->
    <div class="page-body">
        <?= $content ?>
    </div>

</div>

</body>
</html>
