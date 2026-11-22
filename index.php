
<?php
require_once __DIR__ . '/config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PinoyDex | Philippine Reef Fish Database</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* HOMEPAGE CORAL REEF BACKGROUND */

        .home-hero {
            position: relative;
            min-height: 650px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;

            background-image:
                url("https://images.unsplash.com/photo-1546026423-cc4642628d2b?auto=format&fit=crop&w=2000&q=85");

            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* DARK OCEAN OVERLAY */

        .home-hero::before {
            content: "";
            position: absolute;
            inset: 0;

            background: linear-gradient(
                90deg,
                rgba(3, 25, 42, 0.88),
                rgba(3, 45, 65, 0.55),
                rgba(3, 45, 65, 0.25)
            );

            z-index: 1;
        }

        /* HERO CONTENT */

        .home-hero-content {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1150px;
            padding: 90px 7%;
            color: white;
        }

        .home-hero-content .eyebrow {
            color: #8ef3e8;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 18px;
        }

        .home-hero-content h1 {
            max-width: 780px;
            color: #ffffff;
            font-size: clamp(35px, 5vw, 58px);
            line-height: 1.15;
            margin-bottom: 22px;
            text-shadow: 0 3px 12px rgba(0, 0, 0, 0.45);
        }

        .home-hero-content .hero-description {
            max-width: 620px;
            color: #e5f4f7;
            font-size: 17px;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .home-hero-content .explore-btn {
            display: inline-block;
            padding: 15px 28px;
            background: #12b8b0;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 7px;
            transition: all 0.3s ease;
        }

        .home-hero-content .explore-btn:hover {
            background: #087e9b;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        /* INTRODUCTION */

        .intro {
            padding: 75px 20px;
            text-align: center;
        }

        .intro h2 {
            color: #073b5c;
            margin-bottom: 18px;
        }

        .intro > p:last-child {
            max-width: 800px;
            margin: 0 auto;
            color: #607d8b;
            line-height: 1.8;
        }

        /* MOBILE RESPONSIVENESS */

        @media (max-width: 768px) {
            .home-hero {
                min-height: 550px;
                background-position: center;
            }

            .home-hero-content {
                padding: 65px 25px;
            }

            .home-hero-content h1 {
                font-size: 36px;
            }

            .home-hero-content .hero-description {
                font-size: 15px;
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
        <a href="index.php" class="active">Home</a>
        <a href="species.php">Explore Species</a>
        <a href="statistics.php">Statistics</a>
        <a href="about.php">About</a>
        <a href="admin/login.php">Admin</a>
    </nav>

</header>

<!-- HOMEPAGE HERO -->

<section class="home-hero">

    <div class="home-hero-content">

        <p class="eyebrow">
            DISCOVER PHILIPPINE MARINE BIODIVERSITY
        </p>

        <h1>
            Explore the Colorful World of Philippine Reef Fish
        </h1>

        <p class="hero-description">
            Discover, search, and learn about the fascinating reef fish
            species found in Philippine waters.
        </p>

        <a href="species.php" class="explore-btn">
            Explore Species
        </a>

    </div>

</section>

<!-- INTRODUCTION -->

<section class="intro">

    <p class="eyebrow">WELCOME TO PINOYDEX</p>

    <h2>Your Digital Guide to Philippine Reef Fish</h2>

    <p>
        PinoyDex is a digital biodiversity database designed to help
        users discover Philippine reef fish, their scientific names,
        families, conservation status, local names, and geographic
        distribution.
    </p>

</section>

<!-- FOOTER -->

<footer>
    <p>
        &copy; <?= date("Y"); ?>
        PinoyDex. Philippine Reef Fish Database.
    </p>
</footer>

</body>
</html>
