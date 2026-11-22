
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

function escape(mixed $value): string
{
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$error = '';

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

            // CREATE REGION
            if ($action === 'add') {

                $regionName = trim($_POST['region_name'] ?? '');

                if ($regionName === '') {
                    $error = 'Please enter a region name.';
                } else {

                    $stmt = $pdo->prepare("
                        INSERT INTO region (region_name)
                        VALUES (:region_name)
                    ");

                    $stmt->execute([
                        'region_name' => $regionName
                    ]);

                    header("Location: regions.php?added=1");
                    exit;
                }
            }

            // UPDATE REGION
            elseif ($action === 'update') {

                $regionId = filter_var(
                    $_POST['region_id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                $regionName = trim($_POST['region_name'] ?? '');

                if (!$regionId || $regionId < 1) {
                    $error = 'Invalid region selected.';
                } elseif ($regionName === '') {
                    $error = 'Please enter a region name.';
                } else {

                    $check = $pdo->prepare("
                        SELECT id FROM region WHERE id = :id
                    ");

                    $check->execute(['id' => $regionId]);

                    if (!$check->fetch()) {
                        $error = 'Region not found.';
                    } else {

                        $stmt = $pdo->prepare("
                            UPDATE region
                            SET region_name = :region_name
                            WHERE id = :id
                        ");

                        $stmt->execute([
                            'region_name' => $regionName,
                            'id' => $regionId
                        ]);

                        header("Location: regions.php?updated=1");
                        exit;
                    }
                }
            }

            // DELETE REGION
            elseif ($action === 'delete') {

                $regionId = filter_var(
                    $_POST['region_id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$regionId || $regionId < 1) {
                    $error = 'Invalid region selected.';
                } else {

                    $stmt = $pdo->prepare("
                        DELETE FROM region
                        WHERE id = :id
                    ");

                    $stmt->execute([
                        'id' => $regionId
                    ]);

                    if ($stmt->rowCount() > 0) {
                        header("Location: regions.php?deleted=1");
                        exit;
                    } else {
                        $error = 'Region not found.';
                    }
                }
            }

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {
                $error = 'This region already exists.';
            } else {
                error_log(
                    'PinoyDex region CRUD error: ' . $e->getMessage()
                );

                $error = 'Unable to complete the operation.';
            }
        }
    }
}

// Get region being edited
$editRegion = null;

if (isset($_GET['edit'])) {

    $editId = filter_var(
        $_GET['edit'],
        FILTER_VALIDATE_INT
    );

    if ($editId && $editId > 0) {

        $stmt = $pdo->prepare("
            SELECT id, region_name
            FROM region
            WHERE id = :id
        ");

        $stmt->execute(['id' => $editId]);

        $editRegion = $stmt->fetch();

        if (!$editRegion) {
            $error = 'The selected region was not found.';
        }
    }
}

// Get all regions and species counts
$stmt = $pdo->query("
    SELECT
        r.id,
        r.region_name,
        COUNT(DISTINCT sr.species_id) AS species_count
    FROM region r
    LEFT JOIN species_region sr
        ON r.id = sr.region_id
    GROUP BY r.id, r.region_name
    ORDER BY r.region_name ASC
");

$regions = $stmt->fetchAll();

$totalRegions = count($regions);

$totalAssignedSpecies = 0;

foreach ($regions as $region) {
    $totalAssignedSpecies += (int)$region['species_count'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Regions | PinoyDex Admin</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f1f5f9;
    color: #1e293b;
}

/* SIDEBAR */

.sidebar {
    width: 255px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: #082f49;
    color: white;
    padding: 25px 17px;
    display: flex;
    flex-direction: column;
    z-index: 100;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 38px;
}

.logo {
    width: 42px;
    height: 42px;
    background: #0891b2;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 23px;
}

.sidebar-brand h2 {
    font-size: 21px;
}

.sidebar-label {
    color: #94a3b8;
    font-size: 11px;
    font-weight: bold;
    letter-spacing: 1.5px;
    padding: 0 12px;
    margin-bottom: 13px;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #cbd5e1;
    text-decoration: none;
    padding: 13px 14px;
    margin-bottom: 7px;
    border-radius: 9px;
    font-size: 14px;
    transition: 0.25s;
}

.sidebar a:hover {
    background: #164e63;
    color: white;
    transform: translateX(4px);
}

.sidebar a.active {
    background: #0891b2;
    color: white;
    font-weight: bold;
}

.sidebar-bottom {
    margin-top: auto;
    border-top: 1px solid rgba(255,255,255,0.12);
    padding-top: 15px;
}

/* MAIN */

.main {
    margin-left: 255px;
    padding: 28px 32px;
    min-height: 100vh;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.breadcrumb {
    color: #64748b;
    font-size: 13px;
}

.breadcrumb span {
    color: #0891b2;
    font-weight: bold;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 11px;
}

.admin-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #0891b2;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 17px;
}

.admin-info strong {
    display: block;
    font-size: 14px;
}

.admin-info small {
    color: #64748b;
    font-size: 12px;
}

/* PAGE HEADING */

.page-heading {
    margin-bottom: 23px;
}

.page-heading h1 {
    font-size: 27px;
    color: #0f172a;
    margin-bottom: 7px;
}

.page-heading p {
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
}

/* BANNER */

.banner {
    min-height: 185px;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 25px;
    color: white;
    display: flex;
    align-items: center;
    background:
        linear-gradient(
            90deg,
            rgba(8, 47, 73, 0.96),
            rgba(8, 47, 73, 0.65)
        ),
        url('https://images.unsplash.com/photo-1546026423-cc4642628d2b?auto=format&fit=crop&w=1600&q=85')
        center 45% / cover;
}

.banner-content {
    max-width: 600px;
}

.banner h2 {
    font-size: 25px;
    margin-bottom: 10px;
}

.banner p {
    color: #e0f2fe;
    font-size: 14px;
    line-height: 1.7;
}

/* STATISTICS */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    border-radius: 13px;
    padding: 22px;
    display: flex;
    align-items: center;
    gap: 17px;
    box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
    transition: 0.25s;
}

.stat-card:hover {
    transform: translateY(-4px);
}

.stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: #e0f2fe;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-info h3 {
    font-size: 25px;
    margin-bottom: 5px;
}

.stat-info p {
    color: #64748b;
    font-size: 13px;
}

/* CONTENT */

.content-grid {
    display: grid;
    grid-template-columns: 340px minmax(0, 1fr);
    gap: 22px;
    align-items: start;
}

.card {
    background: white;
    border-radius: 13px;
    padding: 23px;
    box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
}

.card-header {
    margin-bottom: 20px;
}

.card-header h2 {
    color: #0f172a;
    font-size: 18px;
    margin-bottom: 6px;
}

.card-header p {
    color: #64748b;
    font-size: 13px;
    line-height: 1.5;
}

/* FORM */

.form-group {
    margin-bottom: 17px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: bold;
    color: #334155;
    margin-bottom: 8px;
}

.form-group input {
    width: 100%;
    padding: 13px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    outline: none;
    font-size: 14px;
}

.form-group input:focus {
    border-color: #0891b2;
    box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.12);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 14px;
    border: none;
    border-radius: 7px;
    text-decoration: none;
    font-size: 12px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.2s;
}

.btn-primary {
    width: 100%;
    padding: 13px;
    background: #0891b2;
    color: white;
    font-size: 14px;
}

.btn-primary:hover {
    background: #0e7490;
}

.btn-edit {
    background: #fef3c7;
    color: #92400e;
}

.btn-edit:hover {
    background: #fde68a;
}

.btn-delete {
    background: #fee2e2;
    color: #991b1b;
}

.btn-delete:hover {
    background: #fecaca;
}

.btn-cancel {
    background: #e2e8f0;
    color: #334155;
    margin-top: 10px;
}

.btn-cancel:hover {
    background: #cbd5e1;
}

/* NOTICES */

.notice {
    padding: 14px 17px;
    border-radius: 9px;
    margin-bottom: 20px;
    font-size: 14px;
}

.notice.success {
    background: #dcfce7;
    color: #166534;
}

.notice.error {
    background: #fee2e2;
    color: #991b1b;
}

/* TABLE */

.table-card {
    min-width: 0;
}

.table-wrapper {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 590px;
}

thead {
    background: #f0f9ff;
}

th {
    color: #075985;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 15px 13px;
    text-align: left;
    white-space: nowrap;
}

td {
    padding: 14px 13px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 13px;
    color: #334155;
}

tbody tr:hover {
    background: #f8fafc;
}

.region-name {
    font-weight: bold;
    color: #0f172a;
}

.count-badge {
    display: inline-block;
    background: #e0f2fe;
    color: #0369a1;
    padding: 6px 11px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.actions {
    display: flex;
    gap: 7px;
    align-items: center;
}

.actions form {
    display: inline;
}

.empty-state {
    text-align: center;
    padding: 30px;
    color: #64748b;
}

/* RESPONSIVE */

@media (max-width: 1100px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {

    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        padding: 15px;
    }

    .sidebar-brand {
        margin-bottom: 20px;
    }

    .sidebar-label {
        display: none;
    }

    .sidebar a {
        display: inline-flex;
        margin: 3px;
        padding: 10px;
        font-size: 12px;
    }

    .sidebar-bottom {
        margin-top: 10px;
    }

    .main {
        margin-left: 0;
        padding: 20px 15px;
    }

    .banner {
        min-height: 165px;
        padding: 22px;
    }

    .banner h2 {
        font-size: 21px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {

    .admin-info {
        display: none;
    }

    .page-heading h1 {
        font-size: 23px;
    }

    .card {
        padding: 18px;
    }
}

</style>

</head>

<body>

<!-- SIDEBAR -->

<aside class="sidebar">

    <div class="sidebar-brand">
        <div class="logo">🐠</div>
        <h2>PinoyDex</h2>
    </div>

    <div class="sidebar-label">MAIN MENU</div>

    <a href="dashboard.php">
        <span>▦</span>
        Dashboard
    </a>

    <a href="species.php">
        <span>🐟</span>
        Manage Species
    </a>

    <a href="families.php">
        <span>🧬</span>
        Manage Families
    </a>

    <a href="regions.php" class="active">
        <span>🌏</span>
        Manage Regions
    </a>

    <div class="sidebar-bottom">

        <a href="../index.php" target="_blank">
            <span>🌐</span>
            View Website
        </a>

        <a href="logout.php">
            <span>↪</span>
            Logout
        </a>

    </div>

</aside>

<!-- MAIN CONTENT -->

<main class="main">

    <div class="topbar">

        <div class="breadcrumb">
            Admin / <span>Manage Regions</span>
        </div>

        <div class="admin-profile">

            <div class="admin-avatar">A</div>

            <div class="admin-info">
                <strong>Admin</strong>
                <small>Administrator</small>
            </div>

        </div>

    </div>

    <div class="page-heading">

        <h1>Manage Regions</h1>

        <p>
            Organize and manage the Philippine regions where reef fish
            species are recorded.
        </p>

    </div>

    <section class="banner">

        <div class="banner-content">

            <h2>Philippine Marine Regions</h2>

            <p>
                Maintain regional distribution records and organize
                biodiversity information across the Philippines.
            </p>

        </div>

    </section>

    <!-- NOTIFICATIONS -->

    <?php if (isset($_GET['added'])): ?>
        <div class="notice success">
            ✓ Region successfully added!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice success">
            ✓ Region successfully updated!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice success">
            ✓ Region successfully deleted!
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="notice error">
            <?= escape($error) ?>
        </div>
    <?php endif; ?>

    <!-- STATISTICS -->

    <section class="stats-grid">

        <div class="stat-card">
            <div class="stat-icon">🌏</div>
            <div class="stat-info">
                <h3><?= $totalRegions ?></h3>
                <p>Total Regions</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🐠</div>
            <div class="stat-info">
                <h3><?= $totalAssignedSpecies ?></h3>
                <p>Total Regional Species Assignments</p>
            </div>
        </div>

    </section>

    <!-- CRUD CONTENT -->

    <section class="content-grid">

        <!-- ADD / EDIT FORM -->

        <div class="card">

            <div class="card-header">

                <h2>
                    <?= $editRegion ? 'Edit Region' : 'Add New Region' ?>
                </h2>

                <p>
                    <?= $editRegion
                        ? 'Update the selected region information.'
                        : 'Enter a region name to add it to PinoyDex.' ?>
                </p>

            </div>

            <form method="POST" action="regions.php<?= $editRegion ? '?edit=' . (int)$editRegion['id'] : '' ?>">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escape($csrfToken) ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="<?= $editRegion ? 'update' : 'add' ?>"
                >

                <?php if ($editRegion): ?>

                    <input
                        type="hidden"
                        name="region_id"
                        value="<?= (int)$editRegion['id'] ?>"
                    >

                <?php endif; ?>

                <div class="form-group">

                    <label for="region_name">
                        Region Name
                    </label>

                    <input
                        type="text"
                        id="region_name"
                        name="region_name"
                        placeholder="Example: Luzon"
                        maxlength="150"
                        value="<?= escape($editRegion['region_name'] ?? '') ?>"
                        required
                    >

                </div>

                <button type="submit" class="btn btn-primary">
                    <?= $editRegion ? 'Save Changes' : '+ Add Region' ?>
                </button>

                <?php if ($editRegion): ?>

                    <a href="regions.php" class="btn btn-cancel">
                        Cancel Editing
                    </a>

                <?php endif; ?>

            </form>

        </div>

        <!-- REGION TABLE -->

        <div class="card table-card">

            <div class="card-header">

                <h2>Region Records</h2>

                <p>
                    View, edit, and delete registered regions.
                </p>

            </div>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>No.</th>
                            <th>Region Name</th>
                            <th>Species Count</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (!empty($regions)): ?>

                            <?php $displayNumber = 1; ?>

                            <?php foreach ($regions as $region): ?>

                                <tr>

                                    <td>
                                        <?= $displayNumber++ ?>
                                    </td>

                                    <td class="region-name">
                                        <?= escape($region['region_name']) ?>
                                    </td>

                                    <td>
                                        <span class="count-badge">
                                            <?= (int)$region['species_count'] ?>
                                            Species
                                        </span>
                                    </td>

                                    <td>

                                        <div class="actions">

                                            <!-- EDIT -->

                                            <a
                                                href="regions.php?edit=<?= (int)$region['id'] ?>"
                                                class="btn btn-edit"
                                            >
                                                Edit
                                            </a>

                                            <!-- DELETE -->

                                            <form
                                                method="POST"
                                                action="regions.php"
                                                onsubmit="return confirm('Are you sure you want to delete this region? Its species-region associations will also be removed.');"
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
                                                    name="region_id"
                                                    value="<?= (int)$region['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-delete"
                                                >
                                                    Delete
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="4" class="empty-state">
                                    No regions found. Add your first region.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </section>

</main>

</body>
</html>
