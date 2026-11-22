
<?php

require_once __DIR__ . '/config/database.php';

// Total species
$totalSpecies = (int)$pdo->query("
    SELECT COUNT(*) FROM species
")->fetchColumn();

// Endemic species
$totalEndemic = (int)$pdo->query("
    SELECT COUNT(*)
    FROM species
    WHERE is_endemic = 'Yes'
")->fetchColumn();

// Non-endemic species
$totalNonEndemic = (int)$pdo->query("
    SELECT COUNT(*)
    FROM species
    WHERE is_endemic = 'No'
")->fetchColumn();

// Total families
$totalFamilies = (int)$pdo->query("
    SELECT COUNT(*) FROM family
")->fetchColumn();

// Total regions
$totalRegions = (int)$pdo->query("
    SELECT COUNT(*) FROM region
")->fetchColumn();

// Species by conservation status
$statusStmt = $pdo->query("
    SELECT
        cs.status_name,
        COUNT(s.id) AS total
    FROM conservation_status cs
    LEFT JOIN species s ON cs.id = s.status_id
    GROUP BY cs.id, cs.status_name
    ORDER BY cs.status_name ASC
");

$statusStats = $statusStmt->fetchAll();

// Species by family
$familyStmt = $pdo->query("
    SELECT
        f.family_name,
        COUNT(s.id) AS total
    FROM family f
    LEFT JOIN species s ON f.id = s.family_id
    GROUP BY f.id, f.family_name
    HAVING COUNT(s.id) > 0
    ORDER BY total DESC, f.family_name ASC
");

$familyStats = $familyStmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Species Statistics | PinoyDex</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        body {
            background: #f3f8fb;
            color: #17384b;
        }

        .statistics-hero {
            background: linear-gradient(
                135deg,
                #062b43,
                #087e9b
            );
            color: white;
            text-align: center;
            padding: 65px 20px;
        }

        .statistics-hero h1 {
            font-size: 38px;
            margin-bottom: 12px;
        }

        .statistics-hero p {
            color: #d9f0f5;
            line-height: 1.8;
        }

        .statistics-container {
            max-width: 1150px;
            margin: 45px auto;
            padding: 0 25px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(190px, 1fr)
            );
            gap: 22px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: white;
            padding: 28px;
            border-radius: 15px;
            border: 1px solid #e3edf2;
            border-top: 4px solid #12b8b0;
            box-shadow: 0 8px 25px rgba(6, 43, 67, 0.06);
            transition: 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 30px rgba(6, 43, 67, 0.11);
        }

        .stat-card h3 {
            color: #718795;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .stat-number {
            color: #087e9b;
            font-size: 36px;
            font-weight: 800;
        }

        .statistics-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            border: 1px solid #e3edf2;
            box-shadow: 0 8px 25px rgba(6, 43, 67, 0.05);
            margin-bottom: 28px;
        }

        .statistics-section h2 {
            color: #062b43;
            font-size: 22px;
            margin-bottom: 20px;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #edf2f5;
        }

        .stat-row:last-child {
            border-bottom: none;
        }

        .stat-row span:first-child {
            color: #405c6d;
            font-weight: 500;
        }

        .stat-value {
            color: #087e9b;
            font-weight: 800;
            white-space: nowrap;
        }

        .empty {
            color: #718795;
            font-style: italic;
        }

        .statistics-footer {
            text-align: center;
            padding: 30px 20px;
            background: #062b43;
            color: #d6e8ef;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .statistics-hero {
                padding: 45px 20px;
            }

            .statistics-hero h1 {
                font-size: 29px;
            }

            .statistics-container {
                padding: 0 15px;
                margin: 30px auto;
            }

            .statistics-section {
                padding: 22px;
            }

            .stat-card {
                padding: 23px;
            }
        }
    </style>
</head>

<body>

<!-- NAVIGATION BAR -->

<header class="navbar">

    <a href="index.php" class="logo">
        Pinoy<span>Dex</span>
    </a>

    <nav>
        <a href="index.php">Home</a>
        <a href="species.php">Explore Species</a>
        <a href="statistics.php" class="active">Statistics</a>
        <a href="about.php">About</a>
        <a href="admin/login.php" class="admin-link">Admin</a>
    </nav>

</header>

<!-- HERO -->

<section class="statistics-hero">

    <h1>Species Statistics</h1>

    <p>
        Explore the biodiversity records currently stored in PinoyDex.
    </p>

</section>

<!-- STATISTICS CONTENT -->

<main class="statistics-container">

    <div class="stats-grid">

        <div class="stat-card">
            <h3>Total Recorded Species</h3>
            <div class="stat-number">
                <?= $totalSpecies ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>Philippine Endemic Species</h3>
            <div class="stat-number">
                <?= $totalEndemic ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>Non-Endemic Species</h3>
            <div class="stat-number">
                <?= $totalNonEndemic ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>Recorded Fish Families</h3>
            <div class="stat-number">
                <?= $totalFamilies ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>Recorded Regions</h3>
            <div class="stat-number">
                <?= $totalRegions ?>
            </div>
        </div>

    </div>

    <!-- CONSERVATION STATUS -->

    <section class="statistics-section">

        <h2>Species by Conservation Status</h2>

        <?php if (!empty($statusStats)): ?>

            <?php foreach ($statusStats as $status): ?>

                <div class="stat-row">

                    <span>
                        <?= htmlspecialchars(
                            $status['status_name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>

                    <span class="stat-value">
                        <?= (int)$status['total'] ?>
                    </span>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p class="empty">
                No conservation records available.
            </p>

        <?php endif; ?>

    </section>

    <!-- SPECIES BY FAMILY -->

    <section class="statistics-section">

        <h2>Species by Fish Family</h2>

        <?php if (!empty($familyStats)): ?>

            <?php foreach ($familyStats as $family): ?>

                <div class="stat-row">

                    <span>
                        <?= htmlspecialchars(
                            $family['family_name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>

                    <span class="stat-value">
                        <?= (int)$family['total'] ?> species
                    </span>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p class="empty">
                No species have been recorded yet.
            </p>

        <?php endif; ?>

    </section>

</main>

<!-- FOOTER -->

<footer class="statistics-footer">

    &copy; <?= date('Y') ?> PinoyDex -
    Philippine Reef Fish Biodiversity Database

</footer>

</body>
</html>
