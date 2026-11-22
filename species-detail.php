
<?php

require_once __DIR__ . '/config/database.php';

// Validate species ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: species.php");
    exit;
}

// Escape output safely
function escape(mixed $value): string
{
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Get species information
$stmt = $pdo->prepare("
    SELECT
        s.*,
        f.family_name,
        cs.status_name
    FROM species s
    INNER JOIN family f ON s.family_id = f.id
    INNER JOIN conservation_status cs ON s.status_id = cs.id
    WHERE s.id = :id
    LIMIT 1
");

$stmt->execute(['id' => $id]);
$species = $stmt->fetch();

if (!$species) {
    header("Location: species.php");
    exit;
}

// Get local names
$localStmt = $pdo->prepare("
    SELECT name, language
    FROM local_name
    WHERE species_id = :id
    ORDER BY language ASC
");

$localStmt->execute(['id' => $id]);
$localNames = $localStmt->fetchAll();

// Get distribution regions
$regionStmt = $pdo->prepare("
    SELECT r.region_name
    FROM species_region sr
    INNER JOIN region r
        ON sr.region_id = r.id
    WHERE sr.species_id = :id
    ORDER BY r.region_name ASC
");

$regionStmt->execute(['id' => $id]);
$distributionRegions = $regionStmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= escape($species['common_name']) ?> | PinoyDex
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef6fa;
            font-family: Arial, sans-serif;
            color: #183b4e;
        }

        header {
            background: #075985;
            color: white;
            padding: 20px 7%;
        }

        header h2 {
            margin: 0;
        }

        nav {
            margin-top: 12px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 1050px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #087eaa;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .detail-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .species-image {
            width: 100%;
            max-height: 480px;
            object-fit: cover;
            display: block;
            background: #dbeafe;
        }

        .image-placeholder {
            height: 260px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dbeafe;
            color: #075985;
            font-size: 18px;
        }

        .image-credit {
            padding: 10px 25px;
            background: #f8fbfd;
            color: #64748b;
            font-size: 12px;
        }

        .content {
            padding: 30px;
        }

        h1 {
            color: #075985;
            margin: 0 0 8px;
            font-size: 32px;
        }

        .scientific-name {
            color: #64748b;
            font-size: 18px;
            font-style: italic;
            margin-bottom: 25px;
        }

        .section {
            margin-top: 30px;
            padding-top: 22px;
            border-top: 1px solid #dce8ef;
        }

        .section h2 {
            color: #075985;
            font-size: 20px;
            margin: 0 0 15px;
        }

        .description {
            line-height: 1.8;
            color: #475569;
            white-space: pre-line;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(220px, 1fr)
            );
            gap: 15px;
        }

        .info-item {
            background: #f1f8fc;
            padding: 16px;
            border-radius: 8px;
        }

        .info-item strong {
            display: block;
            color: #075985;
            margin-bottom: 7px;
        }

        .tag {
            display: inline-block;
            background: #dbeafe;
            color: #075985;
            padding: 8px 13px;
            border-radius: 20px;
            margin: 4px;
            font-size: 13px;
        }

        .empty-text {
            color: #64748b;
            font-style: italic;
        }

        .source {
            color: #475569;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #64748b;
        }

        @media (max-width: 600px) {
            h1 {
                font-size: 25px;
            }

            .content {
                padding: 20px;
            }

            header {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<header>

    <h2>PinoyDex</h2>

    <nav>
        <a href="index.php">Home</a>
        <a href="species.php">Explore Species</a>
        <a href="statistics.php">Statistics</a>
        <a href="about.php">About</a>
        <a href="admin/login.php">Admin</a>
    </nav>

</header>

<div class="container">

    <a href="species.php" class="back-link">
        &larr; Back to Species
    </a>

    <div class="detail-card">

        <!-- Species Image -->
        <?php if (!empty($species['image_url'])): ?>

            <img
                src="<?= escape($species['image_url']) ?>"
                alt="<?= escape($species['common_name']) ?>"
                class="species-image"
            >

        <?php else: ?>

            <div class="image-placeholder">
                No image available
            </div>

        <?php endif; ?>

        <!-- Image Credit -->
        <?php if (!empty($species['image_credit'])): ?>

            <div class="image-credit">
                Image Credit:
                <?= escape($species['image_credit']) ?>
            </div>

        <?php endif; ?>

        <div class="content">

            <!-- Species Name -->
            <h1>
                <?= escape($species['common_name']) ?>
            </h1>

            <div class="scientific-name">
                <?= escape($species['scientific_name']) ?>
            </div>

            <!-- Description -->
            <div class="section">

                <h2>Species Description</h2>

                <div class="description">
                    <?= escape($species['description']) ?>
                </div>

            </div>

            <!-- Species Information -->
            <div class="section">

                <h2>Species Information</h2>

                <div class="info-grid">

                    <div class="info-item">
                        <strong>Family</strong>
                        <?= escape($species['family_name']) ?>
                    </div>

                    <div class="info-item">
                        <strong>Conservation Status</strong>
                        <?= escape($species['status_name']) ?>
                    </div>

                    <div class="info-item">
                        <strong>Venomous or Toxic</strong>
                        <?= escape($species['venomous_or_toxic']) ?>
                    </div>

                    <div class="info-item">
                        <strong>Philippine Endemic</strong>
                        <?= escape($species['is_endemic']) ?>
                    </div>

                </div>

            </div>

            <!-- Local Names -->
            <div class="section">

                <h2>Local Names</h2>

                <?php if (!empty($localNames)): ?>

                    <div class="info-grid">

                        <?php foreach ($localNames as $local): ?>

                            <div class="info-item">

                                <strong>
                                    <?= escape($local['language']) ?>
                                </strong>

                                <?= escape($local['name']) ?>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <p class="empty-text">
                        No local names have been recorded.
                    </p>

                <?php endif; ?>

            </div>

            <!-- Distribution Regions -->
            <div class="section">

                <h2>Distribution in the Philippines</h2>

                <?php if (!empty($distributionRegions)): ?>

                    <?php foreach ($distributionRegions as $region): ?>

                        <span class="tag">
                            <?= escape($region['region_name']) ?>
                        </span>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p class="empty-text">
                        No distribution regions have been recorded.
                    </p>

                <?php endif; ?>

            </div>

            <!-- Research Source -->
            <div class="section">

                <h2>Research Source</h2>

                <div class="source">
                    <?= nl2br(escape($species['source'])) ?>
                </div>

            </div>

        </div>

    </div>

</div>

<footer>
    &copy; <?= date('Y') ?> PinoyDex -
    Philippine Reef Fish Biodiversity Database
</footer>

</body>
</html>
