
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Protect dashboard from unauthorized access
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Dashboard statistics
$totalSpecies = (int) $pdo->query(
    "SELECT COUNT(*) FROM species"
)->fetchColumn();

$totalFamilies = (int) $pdo->query(
    "SELECT COUNT(*) FROM family"
)->fetchColumn();

$totalRegions = (int) $pdo->query(
    "SELECT COUNT(*) FROM region"
)->fetchColumn();

$totalEndemic = (int) $pdo->query(
    "SELECT COUNT(*) FROM species WHERE is_endemic = 'Yes'"
)->fetchColumn();

// Species collection target
$targetSpecies = 20;

$progress = min(
    100,
    ($totalSpecies / $targetSpecies) * 100
);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | PinoyDex</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f2f7fa;
            color: #19394d;
        }

        /* SIDEBAR */

        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 30px 20px;
            background: linear-gradient(180deg, #062b43, #073b5c);
            color: white;
            z-index: 100;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .logo {
            font-size: 29px;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -1px;
        }

        .logo span {
            color: #12b8b0;
        }

        .sidebar-subtitle {
            color: #91b7c9;
            font-size: 11px;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .menu-label {
            font-size: 11px;
            color: #8daebd;
            margin: 15px 0 12px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: bold;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 14px 15px;
            margin-bottom: 7px;
            border-radius: 8px;
            color: #d6e8ef;
            text-decoration: none;
            font-size: 14px;
            transition: all .3s ease;
        }

        .sidebar a .nav-icon {
            width: 23px;
            text-align: center;
            font-size: 17px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: linear-gradient(135deg, #087e9b, #12a8b0);
            color: white;
            transform: translateX(4px);
        }

        .sidebar-bottom {
            margin-top: auto;
            padding-top: 25px;
            border-top: 1px solid rgba(255,255,255,.12);
        }

        /* MAIN CONTENT */

        .main {
            margin-left: 260px;
            padding: 30px 38px;
            min-height: 100vh;
        }

        /* TOPBAR */

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .page-heading h1 {
            font-size: 28px;
            color: #073b5c;
            margin-bottom: 8px;
        }

        .page-heading p {
            color: #8195a1;
            font-size: 14px;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            background: white;
            border: 1px solid #e5edf1;
            border-radius: 10px;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #087e9b, #12b8b0);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 17px;
        }

        .admin-info strong {
            display: block;
            color: #073b5c;
            font-size: 13px;
        }

        .admin-info span {
            color: #8a9da7;
            font-size: 11px;
        }

        /* WELCOME BANNER */

        .welcome-banner {
            position: relative;
            overflow: hidden;
            min-height: 210px;
            padding: 38px;
            margin-bottom: 30px;
            border-radius: 15px;
            background:
                linear-gradient(
                    90deg,
                    rgba(3, 35, 55, .93),
                    rgba(3, 65, 85, .72)
                ),
                url("https://images.unsplash.com/photo-1546026423-cc4642628d2b?auto=format&fit=crop&w=1600&q=85");
            background-size: cover;
            background-position: center;
            color: white;
        }

        .welcome-banner h2 {
            font-size: 27px;
            margin-bottom: 12px;
            color: white;
        }

        .welcome-banner p {
            color: #d9eef3;
            font-size: 14px;
            line-height: 1.8;
            max-width: 580px;
        }

        .banner-label {
            display: inline-block;
            color: #8ef3e8;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 13px;
        }

        /* STATISTICS */

        .section-title {
            color: #073b5c;
            font-size: 19px;
            margin-bottom: 20px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            padding: 24px;
            background: white;
            border: 1px solid #e8eef2;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 40, 65, .035);
            transition: all .3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 40, 65, .09);
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .stat-icon {
            width: 47px;
            height: 47px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            font-size: 22px;
        }

        .icon-blue {
            background: #e5f3fa;
        }

        .icon-teal {
            background: #e0f8f5;
        }

        .icon-purple {
            background: #f0ebff;
        }

        .icon-orange {
            background: #fff1e5;
        }

        .stat-card h2 {
            color: #073b5c;
            font-size: 32px;
            margin-bottom: 7px;
        }

        .stat-card p {
            color: #8295a0;
            font-size: 13px;
        }

        .stat-description {
            color: #12a8a0;
            font-size: 11px;
            margin-top: 13px;
            font-weight: bold;
        }

        /* CONTENT PANELS */

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 22px;
            margin-top: 30px;
        }

        .content-card {
            background: white;
            padding: 27px;
            border-radius: 12px;
            border: 1px solid #e8eef2;
            box-shadow: 0 4px 15px rgba(0, 40, 65, .035);
        }

        .content-card h2 {
            color: #073b5c;
            font-size: 18px;
            margin-bottom: 12px;
        }

        .content-card p {
            color: #718795;
            font-size: 13px;
            line-height: 1.8;
        }

        /* PROGRESS BAR */

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 22px 0 12px;
            font-size: 13px;
            gap: 10px;
        }

        .progress-header strong {
            color: #073b5c;
        }

        .progress-track {
            width: 100%;
            height: 10px;
            background: #eaf1f5;
            border-radius: 20px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #087e9b, #12b8b0);
            border-radius: 20px;
            transition: width .5s ease;
        }

        /* QUICK LINKS */

        .quick-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 18px;
        }

        .quick-links a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px;
            background: #f4f9fb;
            border-radius: 8px;
            color: #31566b;
            text-decoration: none;
            font-size: 13px;
            transition: .3s ease;
        }

        .quick-links a:hover {
            background: #e3f5f7;
            color: #008eae;
            transform: translateX(4px);
        }

        /* FOOTER */

        .dashboard-footer {
            margin-top: 35px;
            padding: 20px 0;
            border-top: 1px solid #e2ebef;
            color: #91a3ad;
            font-size: 12px;
            text-align: center;
        }

        /* RESPONSIVE */

        @media (max-width: 1150px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 750px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding: 22px;
            }

            .sidebar-subtitle {
                margin-bottom: 20px;
            }

            .sidebar a {
                display: inline-flex;
                width: auto;
                margin-right: 5px;
                padding: 11px;
            }

            .sidebar-bottom {
                margin-top: 15px;
            }

            .main {
                margin-left: 0;
                padding: 22px 16px;
            }

            .welcome-banner {
                padding: 27px 22px;
            }

            .welcome-banner h2 {
                font-size: 23px;
            }
        }

        @media (max-width: 480px) {
            .stats {
                grid-template-columns: 1fr;
            }

            .topbar {
                align-items: flex-start;
            }

            .page-heading h1 {
                font-size: 24px;
            }

            .admin-profile {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="logo">
            Pinoy<span>Dex</span>
        </div>

        <div class="sidebar-subtitle">
            Reef Fish Database Administration
        </div>

        <p class="menu-label">Main Menu</p>

        <a href="dashboard.php" class="active">
            <span class="nav-icon">▦</span>
            Dashboard
        </a>

        <a href="species.php">
            <span class="nav-icon">🐠</span>
            Manage Species
        </a>

        <a href="families.php">
            <span class="nav-icon">🧬</span>
            Manage Families
        </a>

        <a href="regions.php">
            <span class="nav-icon">🌊</span>
            Manage Regions
        </a>

        <div class="sidebar-bottom">

            <p class="menu-label">Website</p>

            <a href="../index.php" target="_blank">
                <span class="nav-icon">🌐</span>
                View Website
            </a>

            <a href="logout.php">
                <span class="nav-icon">↪</span>
                Logout
            </a>

        </div>

    </aside>

    <!-- MAIN CONTENT -->

    <main class="main">

        <!-- TOPBAR -->

        <header class="topbar">

            <div class="page-heading">
                <h1>Dashboard Overview</h1>

                <p>
                    Monitor and manage your Philippine reef fish database.
                </p>
            </div>

            <div class="admin-profile">

                <div class="admin-avatar">
                    A
                </div>

                <div class="admin-info">
                    <strong>Admin</strong>
                    <span>Administrator</span>
                </div>

            </div>

        </header>

        <!-- WELCOME BANNER -->

        <section class="welcome-banner">

            <span class="banner-label">
                PHILIPPINE MARINE BIODIVERSITY
            </span>

            <h2>
                Welcome back, Admin!
            </h2>

            <p>
                Manage your reef fish species, classifications,
                conservation information, and geographic distribution
                through your PinoyDex administration dashboard.
            </p>

        </section>

        <!-- STATISTICS -->

        <h2 class="section-title">
            Database Statistics
        </h2>

        <section class="stats">

            <div class="stat-card">

                <div class="stat-top">
                    <div class="stat-icon icon-blue">🐠</div>
                </div>

                <h2>
                    <?= number_format($totalSpecies) ?>
                </h2>

                <p>Total Registered Species</p>

                <div class="stat-description">
                    SPECIES DATABASE
                </div>

            </div>

            <div class="stat-card">

                <div class="stat-top">
                    <div class="stat-icon icon-teal">🧬</div>
                </div>

                <h2>
                    <?= number_format($totalFamilies) ?>
                </h2>

                <p>Fish Families</p>

                <div class="stat-description">
                    CLASSIFICATIONS
                </div>

            </div>

            <div class="stat-card">

                <div class="stat-top">
                    <div class="stat-icon icon-purple">🌍</div>
                </div>

                <h2>
                    <?= number_format($totalRegions) ?>
                </h2>

                <p>Geographic Regions</p>

                <div class="stat-description">
                    DISTRIBUTION AREAS
                </div>

            </div>

            <div class="stat-card">

                <div class="stat-top">
                    <div class="stat-icon icon-orange">⭐</div>
                </div>

                <h2>
                    <?= number_format($totalEndemic) ?>
                </h2>

                <p>Philippine Endemic Species</p>

                <div class="stat-description">
                    ENDEMIC RECORDS
                </div>

            </div>

        </section>

        <!-- DASHBOARD DETAILS -->

        <section class="dashboard-grid">

            <!-- SPECIES PROGRESS -->

            <div class="content-card">

                <h2>Species Collection Progress</h2>

                <p>
                    Your PinoyDex project aims to document at least
                    20 Philippine reef fish species.
                </p>

                <div class="progress-header">

                    <strong>Species Collection</strong>

                    <span>
                        <?= $totalSpecies ?> / <?= $targetSpecies ?>
                    </span>

                </div>

                <div class="progress-track">

                    <div
                        class="progress-fill"
                        style="width: <?= $progress ?>%;"
                    ></div>

                </div>

                <p style="margin-top: 15px;">
                    <?= round($progress, 1) ?>% of your species collection target reached.
                </p>

            </div>

            <!-- QUICK ACTIONS -->

            <div class="content-card">

                <h2>Quick Actions</h2>

                <p>
                    Quickly access the main administration features.
                </p>

                <div class="quick-links">

                    <a href="species.php">
                        <span>🐠 Manage Species</span>
                        <span>→</span>
                    </a>

                    <a href="families.php">
                        <span>🧬 Manage Families</span>
                        <span>→</span>
                    </a>

                    <a href="regions.php">
                        <span>🌊 Manage Regions</span>
                        <span>→</span>
                    </a>

                    <a href="../index.php" target="_blank">
                        <span>🌐 Open Public Website</span>
                        <span>→</span>
                    </a>

                </div>

            </div>

        </section>

        <!-- FOOTER -->

        <footer class="dashboard-footer">

            &copy; <?= date('Y') ?> PinoyDex Admin Dashboard.
            Philippine Reef Fish Biodiversity Database.

        </footer>

    </main>

</body>
</html>
