
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

function escape(mixed $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: species.php");
    exit;
}

// Get existing species
$stmt = $pdo->prepare("
    SELECT *
    FROM species
    WHERE id = ?
");
$stmt->execute([$id]);
$species = $stmt->fetch();

if (!$species) {
    header("Location: species.php");
    exit;
}

// Get families, statuses, and regions
$families = $pdo->query("
    SELECT * FROM family ORDER BY family_name
")->fetchAll();

$statuses = $pdo->query("
    SELECT * FROM conservation_status ORDER BY status_name
")->fetchAll();

$regions = $pdo->query("
    SELECT * FROM region ORDER BY region_name
")->fetchAll();

// Get existing local names
$localStmt = $pdo->prepare("
    SELECT name, language
    FROM local_name
    WHERE species_id = ?
");
$localStmt->execute([$id]);
$localNames = $localStmt->fetchAll();

// Get existing regions
$regionStmt = $pdo->prepare("
    SELECT region_id
    FROM species_region
    WHERE species_id = ?
");
$regionStmt->execute([$id]);

$selectedRegions = array_map(
    'intval',
    array_column($regionStmt->fetchAll(), 'region_id')
);

$errors = [];

// Process update
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $common_name = trim($_POST["common_name"] ?? "");
    $scientific_name = trim($_POST["scientific_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $family_id = filter_var($_POST["family_id"] ?? "", FILTER_VALIDATE_INT);
    $status_id = filter_var($_POST["status_id"] ?? "", FILTER_VALIDATE_INT);
    $venomous = $_POST["venomous_or_toxic"] ?? "No";
    $endemic = $_POST["is_endemic"] ?? "No";
    $image_url = trim($_POST["image_url"] ?? "");
    $image_credit = trim($_POST["image_credit"] ?? "");
    $source = trim($_POST["source"] ?? "");
    $local_names_text = trim($_POST["local_names"] ?? "");
    $postedRegions = $_POST["regions"] ?? [];

    if ($common_name === "") {
        $errors[] = "Common name is required.";
    }

    if ($scientific_name === "") {
        $errors[] = "Scientific name is required.";
    }

    if ($description === "") {
        $errors[] = "Description is required.";
    }

    if (!$family_id || !in_array($family_id, array_map('intval', array_column($families, 'id')), true)) {
        $errors[] = "Please select a valid family.";
    }

    if (!$status_id || !in_array($status_id, array_map('intval', array_column($statuses, 'id')), true)) {
        $errors[] = "Please select a valid conservation status.";
    }

    if (!in_array($venomous, ["Yes", "No"], true)) {
        $errors[] = "Invalid venomous or toxic selection.";
    }

    if (!in_array($endemic, ["Yes", "No"], true)) {
        $errors[] = "Invalid endemic selection.";
    }

    if ($source === "") {
        $errors[] = "Research source is required.";
    }

    if ($image_url !== "" && !filter_var($image_url, FILTER_VALIDATE_URL)) {
        $errors[] = "Please enter a valid image URL.";
    }

    if (!is_array($postedRegions)) {
        $postedRegions = [];
        $errors[] = "Invalid region selection.";
    }

    $validRegionIds = array_map('intval', array_column($regions, 'id'));

    foreach ($postedRegions as $regionId) {
        if (
            !filter_var($regionId, FILTER_VALIDATE_INT) ||
            !in_array((int)$regionId, $validRegionIds, true)
        ) {
            $errors[] = "One or more selected regions are invalid.";
            break;
        }
    }

    // Parse local names
    $parsedLocalNames = [];

    if ($local_names_text !== "") {
        $lines = preg_split("/\r\n|\n|\r/", $local_names_text);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === "") {
                continue;
            }

            $parts = explode("|", $line, 2);

            if (count($parts) !== 2 || trim($parts[0]) === "" || trim($parts[1]) === "") {
                $errors[] = "Use this format for local names: Name | Language.";
                break;
            }

            $parsedLocalNames[] = [
                "name" => trim($parts[0]),
                "language" => trim($parts[1])
            ];
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Update species
            $update = $pdo->prepare("
                UPDATE species SET
                    common_name = ?,
                    scientific_name = ?,
                    description = ?,
                    venomous_or_toxic = ?,
                    is_endemic = ?,
                    family_id = ?,
                    status_id = ?,
                    image_url = ?,
                    image_credit = ?,
                    source = ?
                WHERE id = ?
            ");

            $update->execute([
                $common_name,
                $scientific_name,
                $description,
                $venomous,
                $endemic,
                $family_id,
                $status_id,
                $image_url !== "" ? $image_url : null,
                $image_credit !== "" ? $image_credit : null,
                $source,
                $id
            ]);

            // Replace local names
            $deleteLocal = $pdo->prepare("
                DELETE FROM local_name WHERE species_id = ?
            ");
            $deleteLocal->execute([$id]);

            if (!empty($parsedLocalNames)) {
                $insertLocal = $pdo->prepare("
                    INSERT INTO local_name (species_id, name, language)
                    VALUES (?, ?, ?)
                ");

                foreach ($parsedLocalNames as $local) {
                    $insertLocal->execute([
                        $id,
                        $local["name"],
                        $local["language"]
                    ]);
                }
            }

            // Replace distribution regions
            $deleteRegions = $pdo->prepare("
                DELETE FROM species_region WHERE species_id = ?
            ");
            $deleteRegions->execute([$id]);

            if (!empty($postedRegions)) {
                $insertRegion = $pdo->prepare("
                    INSERT INTO species_region (species_id, region_id)
                    VALUES (?, ?)
                ");

                foreach (array_unique(array_map('intval', $postedRegions)) as $regionId) {
                    $insertRegion->execute([$id, $regionId]);
                }
            }

            $pdo->commit();

            header("Location: species.php?updated=1");
            exit;

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("PinoyDex edit species error: " . $e->getMessage());
            $errors[] = "Unable to update species. Please try again.";
        }
    }

    // Preserve submitted values after validation errors
    $species = array_merge($species, [
        'common_name' => $common_name,
        'scientific_name' => $scientific_name,
        'description' => $description,
        'family_id' => $family_id,
        'status_id' => $status_id,
        'venomous_or_toxic' => $venomous,
        'is_endemic' => $endemic,
        'image_url' => $image_url,
        'image_credit' => $image_credit,
        'source' => $source
    ]);

    $localNames = $parsedLocalNames;
    $selectedRegions = array_map('intval', $postedRegions);
} else {
    $local_names_text = "";

    foreach ($localNames as $local) {
        $local_names_text .= $local['name'] . " | " . $local['language'] . "\n";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Species | PinoyDex</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 15px;
            background: #eef6fa;
            font-family: Arial, sans-serif;
            color: #183b4e;
        }

        .container {
            max-width: 950px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        h1, h2 {
            color: #075985;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #087eaa;
            text-decoration: none;
            font-weight: bold;
        }

        .form-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #dce8ef;
        }

        .form-section h2 {
            font-size: 19px;
            margin-bottom: 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input, textarea, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 14px;
            background: white;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        .help {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #64748b;
        }

        .region-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .region-option {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 12px;
            border: 1px solid #dbe5ec;
            border-radius: 7px;
            background: #f8fbfd;
            font-weight: normal;
        }

        .region-option input {
            width: auto;
            margin: 0;
        }

        .error-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 7px;
            background: #fee2e2;
            color: #991b1b;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        button, .cancel-button {
            padding: 12px 22px;
            border: none;
            border-radius: 7px;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }

        button {
            background: #087eaa;
            color: white;
        }

        button:hover {
            background: #075985;
        }

        .cancel-button {
            background: #e2e8f0;
            color: #334155;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            .button-group {
                flex-direction: column;
            }

            .button-group button,
            .button-group a {
                text-align: center;
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <a href="species.php" class="back-link">
        &larr; Back to Manage Species
    </a>

    <h1>Edit Species</h1>
    <p>Update the species information below.</p>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Please correct the following:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= escape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-section">
            <h2>Basic Information</h2>

            <div class="form-group">
                <label>Common Name *</label>
                <input
                    type="text"
                    name="common_name"
                    value="<?= escape($species['common_name']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Scientific Name *</label>
                <input
                    type="text"
                    name="scientific_name"
                    value="<?= escape($species['scientific_name']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" required><?= escape($species['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Family *</label>
                <select name="family_id" required>
                    <option value="">Select Family</option>

                    <?php foreach ($families as $family): ?>
                        <option
                            value="<?= (int)$family['id'] ?>"
                            <?= (int)$species['family_id'] === (int)$family['id'] ? 'selected' : '' ?>
                        >
                            <?= escape($family['family_name']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div class="form-group">
                <label>Conservation Status *</label>
                <select name="status_id" required>
                    <option value="">Select Status</option>

                    <?php foreach ($statuses as $status): ?>
                        <option
                            value="<?= (int)$status['id'] ?>"
                            <?= (int)$species['status_id'] === (int)$status['id'] ? 'selected' : '' ?>
                        >
                            <?= escape($status['status_name']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div class="form-group">
                <label>Venomous or Toxic?</label>
                <select name="venomous_or_toxic">
                    <option value="No" <?= $species['venomous_or_toxic'] === 'No' ? 'selected' : '' ?>>No</option>
                    <option value="Yes" <?= $species['venomous_or_toxic'] === 'Yes' ? 'selected' : '' ?>>Yes</option>
                </select>
            </div>

            <div class="form-group">
                <label>Philippine Endemic?</label>
                <select name="is_endemic">
                    <option value="No" <?= $species['is_endemic'] === 'No' ? 'selected' : '' ?>>No</option>
                    <option value="Yes" <?= $species['is_endemic'] === 'Yes' ? 'selected' : '' ?>>Yes</option>
                </select>
            </div>
        </div>

        <div class="form-section">
            <h2>Image Information</h2>

            <div class="form-group">
                <label>Image URL</label>
                <input
                    type="url"
                    name="image_url"
                    value="<?= escape($species['image_url']) ?>"
                >
            </div>

            <div class="form-group">
                <label>Image Credit</label>
                <input
                    type="text"
                    name="image_credit"
                    value="<?= escape($species['image_credit']) ?>"
                >
            </div>
        </div>

        <div class="form-section">
            <h2>Local Names</h2>

            <div class="form-group">
                <label>Local Names</label>
                <textarea
                    name="local_names"
                    placeholder="Local name | Language"
                ><?= escape($local_names_text) ?></textarea>

                <span class="help">
                    Enter one name per line. Format: Local name | Language
                </span>
            </div>
        </div>

        <div class="form-section">
            <h2>Distribution Regions</h2>

            <div class="region-grid">

                <?php foreach ($regions as $region): ?>

                    <label class="region-option">

                        <input
                            type="checkbox"
                            name="regions[]"
                            value="<?= (int)$region['id'] ?>"
                            <?= in_array((int)$region['id'], $selectedRegions, true) ? 'checked' : '' ?>
                        >

                        <?= escape($region['region_name']) ?>

                    </label>

                <?php endforeach; ?>

            </div>
        </div>

        <div class="form-section">
            <h2>Research Reference</h2>

            <div class="form-group">
                <label>Research Source *</label>

                <textarea
                    name="source"
                    required
                ><?= escape($species['source']) ?></textarea>
            </div>
        </div>

        <div class="button-group">

            <button type="submit">
                Update Species
            </button>

            <a href="species.php" class="cancel-button">
                Cancel
            </a>

        </div>

    </form>

</div>

</body>
</html>
