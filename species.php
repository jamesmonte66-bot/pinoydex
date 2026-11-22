
<?php
require_once __DIR__ . '/config/database.php';

$search = trim($_GET['search'] ?? '');
$family = $_GET['family'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT
            s.id,
            s.scientific_name,
            s.common_name,
            s.description,
            s.image_url,
            f.family_name,
            cs.status_name
        FROM species s
        INNER JOIN family f ON s.family_id = f.id
        INNER JOIN conservation_status cs ON s.status_id = cs.id
        WHERE 1=1";

$params = [];

if ($search !== '') {
    $sql .= " AND (s.common_name LIKE :search
              OR s.scientific_name LIKE :search
              OR EXISTS (
                  SELECT 1 FROM local_name ln
                  WHERE ln.species_id = s.id
                  AND ln.name LIKE :local_search
              ))";

    $params['search'] = "%$search%";
    $params['local_search'] = "%$search%";
}

if ($family !== '') {
    $sql .= " AND s.family_id = :family";
    $params['family'] = $family;
}

if ($status !== '') {
    $sql .= " AND s.status_id = :status";
    $params['status'] = $status;
}

$sql .= " ORDER BY s.common_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$species = $stmt->fetchAll();

$families = $pdo->query(
    "SELECT id, family_name FROM family ORDER BY family_name"
)->fetchAll();

$statuses = $pdo->query(
    "SELECT id, status_name FROM conservation_status ORDER BY status_name"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Species | PinoyDex</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .directory {
            max-width: 1150px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .directory h1 {
            color: #073b5c;
            margin-bottom: 10px;
        }

        .filters {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin: 30px 0;
        }

        .filters input,
        .filters select {
            padding: 13px;
            border: 1px solid #cbdde6;
            border-radius: 6px;
            font-size: 14px;
        }

        .filters input {
            flex: 1;
            min-width: 200px;
        }

        .filters button {
            padding: 13px 22px;
            border: none;
            border-radius: 6px;
            background: #008eae;
            color: white;
            cursor: pointer;
        }

        .species-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
        }

        .species-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,.07);
        }

        .species-card img,
        .image-placeholder {
            width: 100%;
            height: 190px;
            object-fit: cover;
        }

        .image-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dceff5;
            color: #477083;
        }

        .species-info {
            padding: 20px;
        }

        .species-info h3 {
            color: #073b5c;
            margin-bottom: 7px;
        }

        .scientific {
            color: #718795;
            font-style: italic;
            margin-bottom: 12px;
        }

        .species-info p {
            margin-bottom: 9px;
            font-size: 14px;
        }

        .details-link {
            display: inline-block;
            margin-top: 12px;
            color: #008eae;
            text-decoration: none;
            font-weight: bold;
        }

        .empty {
            padding: 40px;
            text-align: center;
            background: white;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">Pinoy<span>Dex</span></a>

    <nav>
        <a href="index.php">Home</a>
    <a href="species.php">Explore Species</a>
    <a href="statistics.php">Statistics</a>
    <a href="about.php">About</a>
    <a href="admin/login.php">Admin</a>
    </nav>
</header>

<main class="directory">
    <h1>Explore Philippine Reef Fish</h1>
    <p>Discover reef fish species found in Philippine waters.</p>

    <form method="GET" class="filters">
        <input
            type="search"
            name="search"
            placeholder="Search common, scientific, or local name..."
            value="<?= htmlspecialchars($search) ?>"
        >

        <select name="family">
            <option value="">All Families</option>
            <?php foreach ($families as $f): ?>
                <option value="<?= $f['id'] ?>"
                    <?= $family == $f['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['family_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status">
            <option value="">All Conservation Statuses</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= $s['id'] ?>"
                    <?= $status == $s['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['status_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Search</button>
    </form>

    <p style="margin-bottom:20px;">
        <strong><?= count($species) ?></strong> species found
    </p>

    <div class="species-grid">
        <?php if (count($species) > 0): ?>

            <?php foreach ($species as $fish): ?>
                <article class="species-card">

                    <?php if (!empty($fish['image_url'])): ?>
                        <img
                            src="<?= htmlspecialchars($fish['image_url']) ?>"
                            alt="<?= htmlspecialchars($fish['common_name']) ?>"
                        >
                    <?php else: ?>
                        <div class="image-placeholder">
                            Image not available
                        </div>
                    <?php endif; ?>

                    <div class="species-info">
                        <h3><?= htmlspecialchars($fish['common_name']) ?></h3>

                        <p class="scientific">
                            <?= htmlspecialchars($fish['scientific_name']) ?>
                        </p>

                        <p>
                            <strong>Family:</strong>
                            <?= htmlspecialchars($fish['family_name']) ?>
                        </p>

                        <p>
                            <strong>Status:</strong>
                            <?= htmlspecialchars($fish['status_name']) ?>
                        </p>

                        <a class="details-link"
                           href="species-detail.php?id=<?= $fish['id'] ?>">
                            View Details →
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>

        <?php else: ?>
            <div class="empty">
                <h3>No species found yet.</h3>
                <p>Try another search or add species records through the admin page.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> PinoyDex. Philippine Reef Fish Database.</p>
</footer>

</body>
</html>
