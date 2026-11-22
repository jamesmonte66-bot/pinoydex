
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Protect page
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
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

$error = '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($csrfToken, $token)
    ) {
        $error = 'Invalid request. Please try again.';
    } else {

        try {

            // CREATE FAMILY
            if ($action === 'add') {

                $familyName = $_POST['family_name'] ?? '';
                $familyName = is_string($familyName)
                    ? trim($familyName)
                    : '';

                if ($familyName === '') {
                    $error = 'Please enter a family name.';
                } else {

                    $stmt = $pdo->prepare("
                        INSERT INTO family (family_name)
                        VALUES (:family_name)
                    ");

                    $stmt->execute([
                        'family_name' => $familyName
                    ]);

                    header("Location: families.php?added=1");
                    exit;
                }
            }

            // UPDATE FAMILY
            elseif ($action === 'update') {

                $familyId = filter_var(
                    $_POST['family_id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                $familyName = $_POST['family_name'] ?? '';
                $familyName = is_string($familyName)
                    ? trim($familyName)
                    : '';

                if (!$familyId || $familyId < 1) {
                    $error = 'Invalid family selected.';
                } elseif ($familyName === '') {
                    $error = 'Please enter a family name.';
                } else {

                    $check = $pdo->prepare("
                        SELECT id
                        FROM family
                        WHERE id = :id
                    ");

                    $check->execute([
                        'id' => $familyId
                    ]);

                    if (!$check->fetch()) {
                        $error = 'Family not found.';
                    } else {

                        $stmt = $pdo->prepare("
                            UPDATE family
                            SET family_name = :family_name
                            WHERE id = :id
                        ");

                        $stmt->execute([
                            'family_name' => $familyName,
                            'id' => $familyId
                        ]);

                        header("Location: families.php?updated=1");
                        exit;
                    }
                }
            }

            // DELETE FAMILY
            elseif ($action === 'delete') {

                $familyId = filter_var(
                    $_POST['family_id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$familyId || $familyId < 1) {
                    $error = 'Invalid family selected.';
                } else {

                    // Check if family has assigned species
                    $check = $pdo->prepare("
                        SELECT COUNT(*)
                        FROM species
                        WHERE family_id = :family_id
                    ");

                    $check->execute([
                        'family_id' => $familyId
                    ]);

                    $assignedSpecies = (int)$check->fetchColumn();

                    if ($assignedSpecies > 0) {

                        $error = 'Cannot delete this family because '
                               . $assignedSpecies
                               . ' species are assigned to it. '
                               . 'Reassign or remove those species first.';

                    } else {

                        $stmt = $pdo->prepare("
                            DELETE FROM family
                            WHERE id = :id
                        ");

                        $stmt->execute([
                            'id' => $familyId
                        ]);

                        if ($stmt->rowCount() > 0) {
                            header("Location: families.php?deleted=1");
                            exit;
                        } else {
                            $error = 'Family not found.';
                        }
                    }
                }
            }

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {
                $error = 'This family already exists or cannot be deleted.';
            } else {

                error_log(
                    'PinoyDex family CRUD error: ' . $e->getMessage()
                );

                $error = 'Unable to complete the operation. Please try again.';
            }
        }
    }
}

// Get family being edited
$editFamily = null;

if (isset($_GET['edit'])) {

    $editId = filter_var(
        $_GET['edit'],
        FILTER_VALIDATE_INT
    );

    if ($editId && $editId > 0) {

        $stmt = $pdo->prepare("
            SELECT id, family_name
            FROM family
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $editId
        ]);

        $editFamily = $stmt->fetch();

        if (!$editFamily) {
            $error = 'The selected family was not found.';
        }
    }
}

// Get families and species counts
$stmt = $pdo->query("
    SELECT
        f.id,
        f.family_name,
        COUNT(s.id) AS species_count
    FROM family f
    LEFT JOIN species s
        ON f.id = s.family_id
    GROUP BY f.id, f.family_name
    ORDER BY f.family_name ASC
");

$families = $stmt->fetchAll();

$totalFamilies = count($families);

$totalFamilySpecies = array_sum(
    array_map(
        static fn($family) => (int)$family['species_count'],
        $families
    )
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Families | PinoyDex</title>

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

        /* SUMMARY */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .summary-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 23px 25px;
            background: white;
            border: 1px solid #e8eef2;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 40, 65, .035);
            transition: all .3s ease;
        }

        .summary-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 40, 65, .08);
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
            background: #e0f8f5;
            font-size: 25px;
        }

        .summary-icon.blue {
            background: #e5f3fa;
        }

        /* CONTENT GRID */

        .content-grid {
            display: grid;
            grid-template-columns: minmax(280px, .85fr) minmax(0, 1.5fr);
            gap: 22px;
            align-items: start;
        }

        .content-card {
            background: white;
            padding: 27px;
            border-radius: 12px;
            border: 1px solid #e8eef2;
            box-shadow: 0 4px 15px rgba(0, 40, 65, .035);
            min-width: 0;
        }

        .content-card h2 {
            color: #073b5c;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .card-description {
            color: #8295a0;
            font-size: 13px;
            line-height: 1.7;
            margin-bottom: 22px;
        }

        /* FORM */

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            color: #31566b;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 9px;
        }

        .form-group input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #dce6eb;
            border-radius: 8px;
            font-size: 14px;
            color: #19394d;
            outline: none;
            transition: .3s ease;
        }

        .form-group input:focus {
            border-color: #12a8b0;
            box-shadow: 0 0 0 3px rgba(18, 168, 176, .12);
        }

        .form-group input::placeholder {
            color: #a2b2bb;
        }

        .submit-button {
            width: 100%;
            padding: 13px 18px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #087e9b, #12a8b0);
            color: white;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            transition: all .3s ease;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(8, 126, 155, .2);
        }

        .cancel-button {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border-radius: 8px;
            background: #edf2f5;
            color: #526b7a;
            text-decoration: none;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            transition: .2s;
        }

        .cancel-button:hover {
            background: #dce6eb;
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

        /* TABLE */

        .table-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .table-heading h2 {
            margin-bottom: 0;
        }

        .record-count {
            background: #e0f8f5;
            color: #087e83;
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 590px;
        }

        th,
        td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #e8eef2;
            font-size: 13px;
        }

        th {
            background: #edf7fa;
            color: #073b5c;
            white-space: nowrap;
            font-size: 11px;
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

        .family-id {
            color: #8295a0;
            font-weight: bold;
        }

        .family-name {
            color: #073b5c;
            font-weight: bold;
        }

        .species-count {
            display: inline-block;
            min-width: 30px;
            padding: 6px 10px;
            text-align: center;
            background: #e5f3fa;
            color: #087e9b;
            border-radius: 20px;
            font-weight: bold;
            font-size: 11px;
        }

        /* ACTION BUTTONS */

        .action-buttons {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .action-button {
            display: inline-block;
            padding: 8px 11px;
            border: none;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: .2s;
        }

        .edit-button {
            background: #fff3d6;
            color: #946200;
        }

        .edit-button:hover {
            background: #ffe5a3;
        }

        .delete-button {
            background: #fee2e2;
            color: #991b1b;
        }

        .delete-button:hover {
            background: #fecaca;
        }

        .action-buttons form {
            margin: 0;
        }

        /* EMPTY STATE */

        .empty {
            text-align: center;
            padding: 35px 15px;
            color: #8295a0;
            font-size: 13px;
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
            .content-grid {
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

            .page-banner {
                padding: 27px 22px;
            }

            .page-banner h2 {
                font-size: 22px;
            }

            .content-card {
                padding: 22px;
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

            .summary-grid {
                grid-template-columns: 1fr;
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

        <a href="species.php">
            <span class="nav-icon">🐠</span>
            Manage Species
        </a>

        <a href="families.php" class="active">
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

                <h1>Manage Families</h1>

                <p>
                    Organize and manage reef fish family classifications.
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

            <h2>Family Management</h2>

            <p>
                Manage the scientific family classifications
                of Philippine reef fish species and monitor
                how many species belong to each family.
            </p>

        </section>

        <!-- NOTIFICATIONS -->

        <?php if (isset($_GET['added'])): ?>

            <div class="notice">
                ✓ Family successfully added!
            </div>

        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>

            <div class="notice">
                ✓ Family successfully updated!
            </div>

        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>

            <div class="notice">
                ✓ Family successfully deleted!
            </div>

        <?php endif; ?>

        <?php if ($error !== ''): ?>

            <div class="notice error-notice">
                <?= escape($error) ?>
            </div>

        <?php endif; ?>

        <!-- SUMMARY CARDS -->

        <section class="summary-grid">

            <div class="summary-card">

                <div class="summary-info">

                    <p>Total Registered Families</p>

                    <strong>
                        <?= number_format($totalFamilies) ?>
                    </strong>

                </div>

                <div class="summary-icon">
                    🧬
                </div>

            </div>

            <div class="summary-card">

                <div class="summary-info">

                    <p>Species Assigned to Families</p>

                    <strong>
                        <?= number_format($totalFamilySpecies) ?>
                    </strong>

                </div>

                <div class="summary-icon blue">
                    🐠
                </div>

            </div>

        </section>

        <!-- MAIN CONTENT GRID -->

        <section class="content-grid">

            <!-- ADD / EDIT FAMILY FORM -->

            <div class="content-card">

                <h2>
                    <?= $editFamily ? 'Edit Family' : 'Add New Family' ?>
                </h2>

                <p class="card-description">

                    <?= $editFamily
                        ? 'Update the selected scientific family name.'
                        : 'Enter a scientific family name to add a new classification to your database.' ?>

                </p>

                <form method="POST" action="families.php<?= $editFamily ? '?edit=' . (int)$editFamily['id'] : '' ?>">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= escape($csrfToken) ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="<?= $editFamily ? 'update' : 'add' ?>"
                    >

                    <?php if ($editFamily): ?>

                        <input
                            type="hidden"
                            name="family_id"
                            value="<?= (int)$editFamily['id'] ?>"
                        >

                    <?php endif; ?>

                    <div class="form-group">

                        <label for="family_name">
                            Family Name
                        </label>

                        <input
                            type="text"
                            id="family_name"
                            name="family_name"
                            placeholder="Example: Acanthuridae"
                            maxlength="150"
                            value="<?= escape($editFamily['family_name'] ?? '') ?>"
                            required
                        >

                    </div>

                    <button type="submit" class="submit-button">

                        <?= $editFamily
                            ? 'Save Changes'
                            : '+ Add Family' ?>

                    </button>

                    <?php if ($editFamily): ?>

                        <a href="families.php" class="cancel-button">
                            Cancel Editing
                        </a>

                    <?php endif; ?>

                </form>

            </div>

            <!-- FAMILY RECORDS -->

            <div class="content-card">

                <div class="table-heading">

                    <h2>Family Records</h2>

                    <span class="record-count">
                        <?= number_format($totalFamilies) ?> RECORDS
                    </span>

                </div>

                <p class="card-description">
                    View, edit, and manage registered fish families
                    and their corresponding species counts.
                </p>

                <div class="table-container">

                    <?php if (!empty($families)): ?>

                        <table>

                            <thead>

                                <tr>
                                    <th>ID</th>
                                    <th>Family Name</th>
                                    <th>Species Count</th>
                                    <th>Actions</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($families as $family): ?>

                                    <tr>

                                        <td class="family-id">
                                            <?= (int)$family['id'] ?>
                                        </td>

                                        <td class="family-name">
                                            <?= escape($family['family_name']) ?>
                                        </td>

                                        <td>

                                            <span class="species-count">
                                                <?= (int)$family['species_count'] ?>
                                            </span>

                                        </td>

                                        <td>

                                            <div class="action-buttons">

                                                <!-- EDIT -->

                                                <a
                                                    href="families.php?edit=<?= (int)$family['id'] ?>"
                                                    class="action-button edit-button"
                                                >
                                                    Edit
                                                </a>

                                                <!-- DELETE -->

                                                <form
                                                    method="POST"
                                                    action="families.php"
                                                    onsubmit="return confirm('Are you sure you want to delete this family? Families with assigned species cannot be deleted.');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="csrf_token"
                                                        value="<?= escape($csrfToken) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="delete"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="family_id"
                                                        value="<?= (int)$family['id'] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="action-button delete-button"
                                                    >
                                                        Delete
                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    <?php else: ?>

                        <div class="empty">
                            No families found. Add your first family using the form.
                        </div>

                    <?php endif; ?>

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
