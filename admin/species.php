
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Protect page from unauthorized access
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Escape output
function escape(mixed $value): string
{
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Retrieve species records
$stmt = $pdo->query("
    SELECT
        s.id,
        s.common_name,
        s.scientific_name,
        s.is_endemic,
        f.family_name,
        cs.status_name
    FROM species s
    INNER JOIN family f ON s.family_id = f.id
    INNER JOIN conservation_status cs ON s.status_id = cs.id
    ORDER BY s.common_name ASC, s.id ASC
");

$speciesList = $stmt->fetchAll();
$totalSpecies = count($speciesList);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Species | PinoyDex</title>

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
            line-height: 1.6;
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

        /* PAGE BANNER */

        .page-banner {
            position: relative;
            overflow: hidden;
            padding: 30px 35px;
            margin-bottom: 25px;
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

        .banner-label {
            display: inline-block;
            color: #8ef3e8;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 12px;
        }

        .page-banner h2 {
            font-size: 25px;
            color: white;
            margin-bottom: 10px;
        }

        .page-banner p {
            color: #d9eef3;
            font-size: 14px;
            line-height: 1.8;
            max-width: 650px;
        }

        /* SUMMARY CARD */

        .summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 23px 25px;
            background: white;
            border: 1px solid #e8eef2;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 40, 65, .035);
            margin-bottom: 25px;
        }

        .summary-info p {
            color: #8295a0;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .summary-info strong {
            display: block;
            color: #073b5c;
            font-size: 30px;
        }

        .summary-icon {
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #e5f3fa;
            font-size: 25px;
        }

        /* TABLE SECTION */

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 18px;
        }

        .section-heading h2 {
            color: #073b5c;
            font-size: 19px;
        }

        .add-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #087e9b, #12a8b0);
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            transition: all .3s ease;
        }

        .add-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(8, 126, 155, .2);
        }

        .table-container {
            background: white;
            padding: 22px;
            border-radius: 12px;
            overflow-x: auto;
            border: 1px solid #e8eef2;
            box-shadow: 0 4px 15px rgba(0, 40, 65, .035);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        th,
        td {
            padding: 15px 13px;
            border-bottom: 1px solid #e8eef2;
            text-align: left;
            vertical-align: middle;
            font-size: 13px;
        }

        th {
            background: #edf7fa;
            color: #073b5c;
            white-space: nowrap;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        tbody tr {
            transition: background .2s ease;
        }

        tbody tr:nth-child(even) {
            background: #fafcfd;
        }

        tbody tr:hover {
            background: #eff8fa;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .number-column {
            width: 65px;
            text-align: center;
            color: #8295a0;
        }

        .common-name {
            color: #073b5c;
            font-weight: bold;
        }

        .scientific-name {
            color: #526d7b;
        }

        /* STATUS LABELS */

        .status-label {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #eaf3f8;
            color: #31566b;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        .endemic-yes {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
            font-size: 11px;
            font-weight: bold;
        }

        .endemic-no {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 11px;
            font-weight: bold;
        }

        /* ACTION BUTTONS */

        .actions {
            white-space: nowrap;
        }

        .action-link,
        .delete-button {
            display: inline-block;
            padding: 8px 11px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            margin: 2px;
            transition: all .2s ease;
        }

        .edit {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .view {
            background: #dcfce7;
            color: #166534;
        }

        .delete-button {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-link:hover,
        .delete-button:hover {
            opacity: .8;
            transform: translateY(-1px);
        }

        /* NOTIFICATIONS */

        .notice {
            padding: 14px 17px;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13px;
        }

        .error-notice {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fecaca;
        }

        /* EMPTY STATE */

        .empty {
            text-align: center;
            padding: 55px 20px;
            color: #8295a0;
        }

        .empty-icon {
            font-size: 42px;
            margin-bottom: 15px;
        }

        .empty h3 {
            color: #073b5c;
            font-size: 18px;
            margin-bottom: 8px;
        }

        .empty p {
            font-size: 13px;
            line-height: 1.7;
            margin-bottom: 20px;
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
            .main {
                padding: 25px;
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

            .page-banner {
                padding: 27px 22px;
            }

            .page-banner h2 {
                font-size: 22px;
            }

            .table-container {
                padding: 14px;
            }
        }

        @media (max-width: 480px) {
            .topbar {
                align-items: flex-start;
            }

            .page-heading h1 {
                font-size: 24px;
            }

            .admin-profile {
                width: 100%;
            }

            .summary {
                padding: 18px;
            }

            .section-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .add-button {
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

        <a href="dashboard.php">
            <span class="nav-icon">▦</span>
            Dashboard
        </a>

        <a href="species.php" class="active">
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

                <h1>Manage Species</h1>

                <p>
                    Add, update, view, and manage your reef fish records.
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

        <!-- PAGE BANNER -->

        <section class="page-banner">

            <span class="banner-label">
                PHILIPPINE MARINE BIODIVERSITY
            </span>

            <h2>Species Management</h2>

            <p>
                Organize and maintain your Philippine reef fish
                biodiversity records, including scientific names,
                classifications, conservation status, and endemic information.
            </p>

        </section>

        <!-- NOTIFICATIONS -->

        <?php if (isset($_GET['added'])): ?>

            <div class="notice">
                Species successfully added!
            </div>

        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>

            <div class="notice">
                Species successfully updated!
            </div>

        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>

            <div class="notice">
                Species successfully deleted!
            </div>

        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>

            <div class="notice error-notice">
                The requested action could not be completed.
            </div>

        <?php endif; ?>

        <!-- SUMMARY -->

        <section class="summary">

            <div class="summary-info">

                <p>Total Species Records</p>

                <strong>
                    <?= number_format($totalSpecies) ?>
                </strong>

            </div>

            <div class="summary-icon">
                🐠
            </div>

        </section>

        <!-- SPECIES TABLE -->

        <section>

            <div class="section-heading">

                <h2>Species Records</h2>

                <a href="add-species.php" class="add-button">
                    <span>+</span>
                    Add New Species
                </a>

            </div>

            <div class="table-container">

                <?php if (!empty($speciesList)): ?>

                    <table>

                        <thead>

                            <tr>
                                <th class="number-column">No.</th>
                                <th>Common Name</th>
                                <th>Scientific Name</th>
                                <th>Family</th>
                                <th>Conservation Status</th>
                                <th>Endemic</th>
                                <th>Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php
                            $displayNumber = 1;

                            foreach ($speciesList as $species):
                            ?>

                                <tr>

                                    <td class="number-column">
                                        <?= $displayNumber++ ?>
                                    </td>

                                    <td class="common-name">
                                        <?= escape($species['common_name']) ?>
                                    </td>

                                    <td class="scientific-name">
                                        <em>
                                            <?= escape($species['scientific_name']) ?>
                                        </em>
                                    </td>

                                    <td>
                                        <?= escape($species['family_name']) ?>
                                    </td>

                                    <td>
                                        <span class="status-label">
                                            <?= escape($species['status_name']) ?>
                                        </span>
                                    </td>

                                    <td>

                                        <?php if ($species['is_endemic'] === 'Yes'): ?>

                                            <span class="endemic-yes">
                                                Yes
                                            </span>

                                        <?php else: ?>

                                            <span class="endemic-no">
                                                No
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td class="actions">

                                        <a
                                            href="edit-species.php?id=<?= (int)$species['id'] ?>"
                                            class="action-link edit"
                                        >
                                            Edit
                                        </a>

                                        <a
                                            href="../species-detail.php?id=<?= (int)$species['id'] ?>"
                                            class="action-link view"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            View
                                        </a>

                                        <form
                                            method="POST"
                                            action="delete-species.php"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this species? This action cannot be undone.');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int)$species['id'] ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= escape($_SESSION['csrf_token']) ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="delete-button"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php else: ?>

                    <div class="empty">

                        <div class="empty-icon">
                            🐠
                        </div>

                        <h3>No Species Found</h3>

                        <p>
                            Your species database is currently empty.
                            Start building your collection by adding your first reef fish species.
                        </p>

                        <a href="add-species.php" class="add-button">
                            + Add Your First Species
                        </a>

                    </div>

                <?php endif; ?>

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
