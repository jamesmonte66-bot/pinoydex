
<?php
require_once __DIR__ . '/../config/database.php';

$message = '';
$adminExists = (int) $pdo->query(
    "SELECT COUNT(*) FROM admin_users"
)->fetchColumn() > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$adminExists) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($username === '' || $password === '') {
        $message = "Please complete all fields.";
    } elseif (strlen($password) < 8) {
        $message = "Password must contain at least 8 characters.";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO admin_users (username, password)
                 VALUES (:username, :password)"
            );

            $stmt->execute([
                'username' => $username,
                'password' => $hashedPassword
            ]);

            $adminExists = true;
            $message = "Admin account created successfully! You can now log in.";
        } catch (PDOException $e) {
            $message = "Unable to create account. Try a different username.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin | PinoyDex</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .setup-card {
            background: white;
            width: 100%;
            max-width: 430px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,.08);
        }

        .setup-card h1 {
            color: #073b5c;
            margin-bottom: 10px;
        }

        .setup-card p {
            color: #718795;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .setup-card label {
            display: block;
            margin: 15px 0 7px;
            font-weight: bold;
        }

        .setup-card input {
            width: 100%;
            padding: 13px;
            border: 1px solid #cbdde6;
            border-radius: 6px;
        }

        .setup-card button {
            width: 100%;
            padding: 14px;
            margin-top: 22px;
            border: none;
            border-radius: 6px;
            background: #008eae;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .message {
            padding: 12px;
            background: #e8f7ef;
            color: #176b3a;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .login-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #008eae;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="setup-card">
    <h1>Pinoy<span style="color:#00a6c7">Dex</span></h1>
    <p>Create your first administrator account.</p>

    <?php if ($message): ?>
        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (!$adminExists): ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required maxlength="100">

            <label>Password</label>
            <input type="password" name="password" required minlength="8">

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required minlength="8">

            <button type="submit">Create Admin Account</button>
        </form>

    <?php else: ?>

        <p>An administrator account already exists or has just been created.</p>
        <a class="login-link" href="login.php">Go to Admin Login</a>

    <?php endif; ?>
</div>

</body>
</html>
