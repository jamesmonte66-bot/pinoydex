
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter your username and password.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, username, password
             FROM admin_users
             WHERE username = :username
             LIMIT 1"
        );

        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | PinoyDex</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;

            background:
                linear-gradient(
                    90deg,
                    rgba(2, 31, 51, 0.78),
                    rgba(2, 38, 58, 0.35)
                ),
                url("https://images.unsplash.com/photo-1546026423-cc4642628d2b?auto=format&fit=crop&w=2000&q=85");

            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;

            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 40px 8%;
        }

        /* LEFT BRANDING SECTION */

        .welcome-section {
            position: absolute;
            left: 8%;
            top: 50%;
            transform: translateY(-50%);
            max-width: 560px;
            color: white;
        }

        .welcome-logo {
            font-size: 45px;
            font-weight: 800;
            margin-bottom: 25px;
            letter-spacing: -1px;
        }

        .welcome-logo span {
            color: #12d4c5;
        }

        .welcome-section .eyebrow {
            color: #8ef3e8;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 3px;
            margin-bottom: 18px;
        }

        .welcome-section h1 {
            font-size: 48px;
            line-height: 1.2;
            margin: 0 0 20px;
            color: white;
            text-shadow: 0 3px 12px rgba(0,0,0,.3);
        }

        .welcome-section p {
            color: #e2f4f7;
            font-size: 16px;
            line-height: 1.8;
            max-width: 470px;
        }

        .welcome-line {
            width: 75px;
            height: 4px;
            background: #12b8b0;
            border-radius: 5px;
            margin-top: 28px;
        }

        /* RIGHT LOGIN PANEL */

        .login-card {
            position: relative;
            z-index: 2;

            width: 100%;
            max-width: 440px;

            padding: 45px 38px;

            background: rgba(255, 255, 255, 0.97);
            border: 1px solid rgba(255,255,255,.5);
            border-radius: 18px;

            box-shadow: 0 20px 60px rgba(0, 20, 35, 0.3);

            animation: slideIn .7s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(35px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .login-logo {
            font-size: 32px;
            font-weight: 800;
            color: #073b5c;
            margin-bottom: 8px;
        }

        .login-logo span {
            color: #00a6c7;
        }

        .login-subtitle {
            color: #718795;
            font-size: 14px;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .login-card h2 {
            color: #073b5c;
            font-size: 25px;
            margin: 0 0 8px;
        }

        .login-card label {
            display: block;
            margin: 20px 0 9px;
            color: #234456;
            font-size: 14px;
            font-weight: 700;
        }

        .login-card input {
            width: 100%;
            padding: 14px 15px;

            border: 1px solid #d5e2e8;
            border-radius: 8px;

            background: #f8fbfc;
            color: #073b5c;
            font-size: 14px;

            outline: none;
            transition: .3s ease;
        }

        .login-card input:focus {
            border-color: #008eae;
            background: white;
            box-shadow: 0 0 0 3px rgba(0,142,174,.1);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 65px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);

            border: none;
            background: none;
            color: #008eae;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }

        .login-card button[type="submit"] {
            width: 100%;
            padding: 15px;
            margin-top: 28px;

            border: none;
            border-radius: 8px;

            background: linear-gradient(135deg, #087e9b, #12b8b0);
            color: white;

            font-size: 15px;
            font-weight: bold;
            cursor: pointer;

            transition: .3s ease;
        }

        .login-card button[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,142,174,.25);
        }

        .error {
            padding: 13px;
            margin-bottom: 18px;

            background: #fff0f0;
            border: 1px solid #ffd1d1;
            border-radius: 8px;

            color: #b42318;
            font-size: 13px;
        }

        .back-link {
            display: block;
            margin-top: 25px;
            text-align: center;

            color: #008eae;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;

            transition: .3s ease;
        }

        .back-link:hover {
            color: #005b75;
        }

        .login-footer {
            margin-top: 30px;
            padding-top: 18px;
            border-top: 1px solid #e6eef2;

            text-align: center;
            color: #8a9da7;
            font-size: 11px;
        }

        /* MOBILE RESPONSIVENESS */

        @media (max-width: 900px) {
            body {
                justify-content: center;
                padding: 30px 20px;
            }

            .welcome-section {
                display: none;
            }

            .login-card {
                max-width: 440px;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 35px 25px;
            }

            .login-logo {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

    <!-- LEFT SIDE BRANDING -->

    <section class="welcome-section">

        <div class="welcome-logo">
            Pinoy<span>Dex</span>
        </div>

        <p class="eyebrow">
            PHILIPPINE MARINE BIODIVERSITY
        </p>

        <h1>
            Explore and Protect Our Underwater World
        </h1>

        <p>
            Welcome to the PinoyDex administration portal.
            Manage Philippine reef fish records, biodiversity
            information, and species data in one place.
        </p>

        <div class="welcome-line"></div>

    </section>

    <!-- RIGHT SIDE LOGIN PANEL -->

    <div class="login-card">

        <div class="login-logo">
            Pinoy<span>Dex</span>
        </div>

        <p class="login-subtitle">
            Philippine Reef Fish Biodiversity Database
        </p>

        <h2>Administrator Login</h2>

        <p class="login-subtitle">
            Sign in to access your administration dashboard.
        </p>

        <?php if ($error): ?>
            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label for="username">Username</label>

            <input
                type="text"
                id="username"
                name="username"
                placeholder="Enter your username"
                value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                autocomplete="username"
                required
            >

            <label for="password">Password</label>

            <div class="password-wrapper">

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

                <button
                    type="button"
                    class="toggle-password"
                    onclick="togglePassword()"
                    id="toggleButton"
                >
                    SHOW
                </button>

            </div>

            <button type="submit">
                Sign In to Dashboard
            </button>

        </form>

        <a href="../index.php" class="back-link">
            ← Back to Homepage
        </a>

        <div class="login-footer">
            &copy; <?= date('Y') ?> PinoyDex. All rights reserved.
        </div>

    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const button = document.getElementById('toggleButton');

            if (password.type === 'password') {
                password.type = 'text';
                button.textContent = 'HIDE';
            } else {
                password.type = 'password';
                button.textContent = 'SHOW';
            }
        }
    </script>

</body>
</html>
