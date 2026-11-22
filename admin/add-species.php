
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$errors = [];
$success = "";

// Get families, conservation statuses, and regions
$families = $pdo->query("
    SELECT id, family_name
    FROM family
    ORDER BY family_name ASC
")->fetchAll();

$statuses = $pdo->query("
    SELECT id, status_name
    FROM conservation_status
    ORDER BY status_name ASC
")->fetchAll();

$regions = $pdo->query("
    SELECT id, region_name
    FROM region
    ORDER BY region_name ASC
")->fetchAll();

// Process form submission
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
    $selected_regions = $_POST["regions"] ?? [];

    // Validate required fields
    if ($common_name === "") {
        $errors[] = "Common name is required.";
    }

    if ($scientific_name === "") {
        $errors[] = "Scientific name is required.";
    }

    if ($description === "") {
        $errors[] = "Description is required.";
    }

    if (!$family_id) {
        $errors[] = "Please select a family.";
    }

    if (!$status_id) {
        $errors[] = "Please select a conservation status.";
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

    // Validate image URL if supplied
    if ($image_url !== "" && !filter_var($image_url, FILTER_VALIDATE_URL)) {
        $errors[] = "Please enter a valid image URL.";
    }

    // Validate selected family and status
    $familyExists = false;
    foreach ($families as $family) {
        if ((int)$family["id"] === (int)$family_id) {
            $familyExists = true;
            break;
        }
    }

    $statusExists = false;
    foreach ($statuses as $status) {
        if ((int)$status["id"] === (int)$status_id) {
            $statusExists = true;
            break;
        }
    }

    if (!$familyExists) {
        $errors[] = "Selected family does not exist.";
    }

    if (!$statusExists) {
        $errors[] = "Selected conservation status does not exist.";
    }

    // Validate selected regions
    if (!is_array($selected_regions)) {
        $selected_regions = [];
        $errors[] = "Invalid region selection.";
    }

    $valid_region_ids = array_map(
        "intval",
        array_column($regions, "id")
    );

    foreach ($selected_regions as $region_id) {
        if (
            !filter_var($region_id, FILTER_VALIDATE_INT) ||
            !in_array((int)$region_id, $valid_region_ids, true)
        ) {
            $errors[] = "One or more selected regions are invalid.";
            break;
        }
    }

    // Parse local names
    // Format: Local name | Language
    $local_names = [];

    if ($local_names_text !== "") {
        $lines = preg_split("/\r\n|\n|\r/", $local_names_text);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === "") {
                continue;
            }

            $parts = explode("|", $line, 2);

            if (count($parts) !== 2) {
                $errors[] = "Invalid local name format. Use: Local name | Language";
                break;
            }

            $name = trim($parts[0]);
            $language = trim($parts[1]);

            if ($name === "" || $language === "") {
                $errors[] = "Each local name must have a name and language.";
                break;
            }

            $local_names[] = [
                "name" => $name,
                "language" => $language
            ];
        }
    }

    // Insert species and related information
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $insertSpecies = $pdo->prepare("
                INSERT INTO species (
                    scientific_name,
                    common_name,
                    description,
                    venomous_or_toxic,
                    is_endemic,
                    family_id,
                    status_id,
                    image_url,
                    image_credit,
                    source
                ) VALUES (
                    :scientific_name,
                    :common_name,
                    :description,
                    :venomous,
                    :endemic,
                    :family_id,
                    :status_id,
                    :image_url,
                    :image_credit,
                    :source
                )
            ");

            $insertSpecies->execute([
                ":scientific_name" => $scientific_name,
                ":common_name" => $common_name,
                ":description" => $description,
                ":venomous" => $venomous,
                ":endemic" => $endemic,
                ":family_id" => $family_id,
                ":status_id" => $status_id,
                ":image_url" => $image_url !== "" ? $image_url : null,
                ":image_credit" => $image_credit !== "" ? $image_credit : null,
                ":source" => $source
            ]);

            $species_id = $pdo->lastInsertId();

            // Insert local names
            if (!empty($local_names)) {
                $insertLocalName = $pdo->prepare("
                    INSERT INTO local_name (
                        species_id,
                        name,
                        language
                    ) VALUES (
                        :species_id,
                        :name,
                        :language
                    )
                ");

                foreach ($local_names as $local) {
                    $insertLocalName->execute([
                        ":species_id" => $species_id,
                        ":name" => $local["name"],
                        ":language" => $local["language"]
                    ]);
                }
            }

            // Insert distribution regions
            if (!empty($selected_regions)) {
                $insertRegion = $pdo->prepare("
                    INSERT INTO species_region (
                        species_id,
                        region_id
                    ) VALUES (
                        :species_id,
                        :region_id
                    )
                ");

                foreach ($selected_regions as $region_id) {
                    $insertRegion->execute([
                        ":species_id" => $species_id,
                        ":region_id" => (int)$region_id
                    ]);
                }
            }

            $pdo->commit();

            header("Location: species.php?added=1");
            exit;

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("PinoyDex add species error: " . $e->getMessage());
            $errors[] = "Unable to save the species. Please check your information and try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Species | PinoyDex</title>

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

        .header {
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0 0 8px;
            color: #075985;
        }

        .header p {
            color: #64748b;
            margin: 0;
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
            color: #075985;
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

        input,
        textarea,
        select {
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

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(2,132,199,0.12);
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

        .error-box ul {
            margin-bottom: 0;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        button,
        .cancel-button {
            display: inline-block;
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

            .button-group button,
            .button-group a {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <a href="species.php" class="back-link">
        &larr; Back to Manage Species
    </a>

    <div class="header">
        <h1>Add New Species</h1>
        <p>Enter the information needed to add a reef fish species to PinoyDex.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Please correct the following:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="">

        <div class="form-section">
            <h2>Basic Information</h2>

            <div class="form-group">
                <label for="common_name">Common Name *</label>
                <input
                    type="text"
                    id="common_name"
                    name="common_name"
                    value="<?= htmlspecialchars($_POST['common_name'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="scientific_name">Scientific Name *</label>
                <input
                    type="text"
                    id="scientific_name"
                    name="scientific_name"
                    value="<?= htmlspecialchars($_POST['scientific_name'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea
                    id="description"
                    name="description"
                    required
                ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="family_id">Family *</label>
                <select id="family_id" name="family_id" required>
                    <option value="">Select Family</option>

                    <?php foreach ($families as $family): ?>
                        <option
                            value="<?= (int)$family['id'] ?>"
                            <?= (($_POST['family_id'] ?? '') == $family['id']) ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($family['family_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="status_id">Conservation Status *</label>
                <select id="status_id" name="status_id" required>
                    <option value="">Select Conservation Status</option>

                    <?php foreach ($statuses as $status): ?>
                        <option
                            value="<?= (int)$status['id'] ?>"
                            <?= (($_POST['status_id'] ?? '') == $status['id']) ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($status['status_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="venomous_or_toxic">Venomous or Toxic? *</label>
                <select id="venomous_or_toxic" name="venomous_or_toxic">
                    <option value="No" <?= ($_POST['venomous_or_toxic'] ?? 'No') === 'No' ? 'selected' : '' ?>>No</option>
                    <option value="Yes" <?= ($_POST['venomous_or_toxic'] ?? '') === 'Yes' ? 'selected' : '' ?>>Yes</option>
                </select>
            </div>

            <div class="form-group">
                <label for="is_endemic">Philippine Endemic? *</label>
                <select id="is_endemic" name="is_endemic">
                    <option value="No" <?= ($_POST['is_endemic'] ?? 'No') === 'No' ? 'selected' : '' ?>>No</option>
                    <option value="Yes" <?= ($_POST['is_endemic'] ?? '') === 'Yes' ? 'selected' : '' ?>>Yes</option>
                </select>
            </div>
        </div>

        <div class="form-section">
            <h2>Image Information</h2>

            <div class="form-group">
                <label for="image_url">Image URL</label>
                <input
                    type="url"
                    id="image_url"
                    name="image_url"
                    value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>"
                    placeholder="https://example.com/fish-image.jpg"
                >
                <span class="help">Enter the direct image URL if available.</span>
            </div>

            <div class="form-group">
                <label for="image_credit">Image Credit</label>
                <input
                    type="text"
                    id="image_credit"
                    name="image_credit"
                    value="<?= htmlspecialchars($_POST['image_credit'] ?? '') ?>"
                    placeholder="Photographer, website, and license"
                >
                <span class="help">Include the photographer or creator and license when known.</span>
            </div>
        </div>

        <div class="form-section">
            <h2>Local Names</h2>

            <div class="form-group">
                <label for="local_names">Local Names</label>
                <textarea
                    id="local_names"
                    name="local_names"
                    placeholder="Danggit | Cebuano&#10;Local name | Language"
                ><?= htmlspecialchars($_POST['local_names'] ?? '') ?></textarea>
                <span class="help">
                    Enter one local name per line using this format:
                    Local name | Language
                </span>
            </div>
        </div>

        <div class="form-section">
            <h2>Distribution Regions</h2>
            <p class="help">Select the Philippine regions where this species is documented to occur.</p>

            <div class="region-grid">
                <?php foreach ($regions as $region): ?>
                    <label class="region-option">
                        <input
                            type="checkbox"
                            name="regions[]"
                            value="<?= (int)$region['id'] ?>"
                            <?= in_array(
                                (string)$region['id'],
                                array_map('strval', $_POST['regions'] ?? []),
                                true
                            ) ? 'checked' : '' ?>
                        >
                        <?= htmlspecialchars($region['region_name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-section">
            <h2>Research Reference</h2>

            <div class="form-group">
                <label for="source">Research Source *</label>
                <textarea
                    id="source"
                    name="source"
                    placeholder="Enter the reference title, organization, or source URL"
                    required
                ><?= htmlspecialchars($_POST['source'] ?? '') ?></textarea>
                <span class="help">Provide a credible reference supporting the species information.</span>
            </div>
        </div>

        <div class="button-group">
            <button type="submit">Save Species</button>
            <a href="species.php" class="cancel-button">Cancel</a>
        </div>

    </form>

</div>

</body>
</html>
