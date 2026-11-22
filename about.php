
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About PinoyDex | Philippine Reef Fish Biodiversity Database</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        body {
            background: #f3f8fb;
            color: #17384b;
        }

        /* HERO SECTION */

        .about-hero {
            background:
                linear-gradient(
                    120deg,
                    rgba(6, 43, 67, 0.96),
                    rgba(8, 126, 155, 0.86)
                ),
                url("assets/images/reef.jpg") center/cover no-repeat;

            padding: 85px 20px;
            text-align: center;
            color: white;
        }

        .about-hero .eyebrow {
            color: #6ce9dc;
            letter-spacing: 3px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .about-hero h1 {
            font-size: clamp(36px, 5vw, 50px);
            margin: 15px 0;
        }

        .about-hero p {
            max-width: 750px;
            margin: auto;
            color: #e0f3f7;
            font-size: 17px;
            line-height: 1.9;
        }

        /* MAIN CONTAINER */

        .about-container {
            max-width: 1150px;
            margin: 55px auto;
            padding: 0 25px;
        }

        /* CONTENT CARDS */

        .about-card {
            background: white;
            padding: 35px;
            border-radius: 16px;
            border: 1px solid #e3edf2;
            box-shadow: 0 8px 25px rgba(6, 43, 67, 0.05);
            margin-bottom: 28px;
        }

        .about-card h2 {
            color: #062b43;
            font-size: 25px;
            margin-bottom: 18px;
        }

        .about-card p {
            color: #607888;
            font-size: 15px;
            line-height: 1.9;
            margin-bottom: 15px;
        }

        .about-card p:last-child {
            margin-bottom: 0;
        }

        /* HIGHLIGHT */

        .about-highlight {
            border-left: 5px solid #12b8b0;
            background: #eaf9f7;
            padding: 22px 25px;
            border-radius: 8px;
            margin-top: 22px;
        }

        .about-highlight p {
            color: #245b62;
            margin: 0;
        }

        /* FEATURE GRID */

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(230px, 1fr)
            );
            gap: 20px;
            margin-top: 25px;
        }

        .feature-card {
            background: #f6fafc;
            padding: 25px;
            border: 1px solid #e5eef2;
            border-radius: 13px;
            transition: 0.25s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(6, 43, 67, 0.08);
            border-color: #a8e5df;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dff7f5;
            color: #087e9b;
            border-radius: 12px;
            font-size: 23px;
            margin-bottom: 18px;
        }

        .feature-card h3 {
            color: #073b5c;
            font-size: 17px;
            margin-bottom: 10px;
        }

        .feature-card p {
            color: #718795;
            font-size: 14px;
            line-height: 1.8;
            margin: 0;
        }

        /* DATABASE INFORMATION */

        .database-list {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(200px, 1fr)
            );
            gap: 15px;
            margin-top: 22px;
        }

        .database-item {
            background: #f3f8fb;
            padding: 18px;
            border-radius: 10px;
            border: 1px solid #e3edf2;
        }

        .database-item strong {
            display: block;
            color: #087e9b;
            font-size: 15px;
            margin-bottom: 5px;
        }

        .database-item span {
            color: #718795;
            font-size: 13px;
        }

        /* FOOTER */

        .about-footer {
            background: #062b43;
            color: #d6e8ef;
            text-align: center;
            padding: 30px 20px;
            font-size: 14px;
        }

        /* RESPONSIVE */

        @media (max-width: 600px) {

            .about-hero {
                padding: 60px 20px;
            }

            .about-hero p {
                font-size: 15px;
            }

            .about-container {
                margin: 30px auto;
                padding: 0 15px;
            }

            .about-card {
                padding: 23px;
            }

            .about-card h2 {
                font-size: 22px;
            }

            .feature-grid {
                grid-template-columns: 1fr;
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
        <a href="statistics.php">Statistics</a>
        <a href="about.php" class="active">About</a>
        <a href="admin/login.php" class="admin-link">Admin</a>
    </nav>

</header>

<!-- HERO SECTION -->

<section class="about-hero">

    <span class="eyebrow">Philippine Marine Biodiversity</span>

    <h1>About PinoyDex</h1>

    <p>
        A digital biodiversity database dedicated to documenting,
        organizing, and presenting information about reef fish
        found in Philippine waters.
    </p>

</section>

<!-- MAIN CONTENT -->

<main class="about-container">

    <!-- ABOUT PINOYDEX -->

    <section class="about-card">

        <h2>What is PinoyDex?</h2>

        <p>
            PinoyDex is a Philippine Reef Fish Biodiversity Database
            developed as a digital reference platform for information
            about reef fish species recorded in the Philippines.
            It provides an organized and searchable collection of
            species-related information through a user-friendly
            website.
        </p>

        <p>
            Each species record presents relevant information such as
            scientific and common names, descriptions, taxonomic
            families, conservation statuses, endemicity, local names,
            geographic distribution records, and associated images.
        </p>

        <div class="about-highlight">
            <p>
                PinoyDex aims to make information about Philippine
                reef fish more accessible while supporting awareness
                and appreciation of the country's marine biodiversity.
            </p>
        </div>

    </section>

    <!-- PURPOSE -->

    <section class="about-card">

        <h2>Our Purpose</h2>

        <p>
            The primary purpose of PinoyDex is to provide a centralized
            digital reference for exploring and understanding
            Philippine reef fish biodiversity.
        </p>

        <p>
            The platform is intended to support students, educators,
            researchers, and individuals interested in marine life
            by making species information easier to browse, search,
            and understand.
        </p>

        <p>
            It also promotes awareness of marine biodiversity and
            highlights the importance of responsible documentation
            and conservation of aquatic ecosystems.
        </p>

    </section>

    <!-- WEBSITE FEATURES -->

    <section class="about-card">

        <h2>Website Features</h2>

        <p>
            PinoyDex provides several features designed to make
            Philippine reef fish information accessible and organized.
        </p>

        <div class="feature-grid">

            <div class="feature-card">

                <div class="feature-icon">🐠</div>

                <h3>Species Directory</h3>

                <p>
                    Browse recorded reef fish species through an
                    organized directory featuring species images,
                    common names, and scientific names.
                </p>

            </div>

            <div class="feature-card">

                <div class="feature-icon">🔎</div>

                <h3>Search and Filtering</h3>

                <p>
                    Locate species using common names, scientific
                    names, and available local names. Refine results
                    using family and conservation status filters.
                </p>

            </div>

            <div class="feature-card">

                <div class="feature-icon">📚</div>

                <h3>Species Information</h3>

                <p>
                    View species descriptions, taxonomic information,
                    conservation status, endemicity, local names,
                    distribution records, and image credits.
                </p>

            </div>

            <div class="feature-card">

                <div class="feature-icon">📊</div>

                <h3>Biodiversity Statistics</h3>

                <p>
                    Explore summarized records showing the number
                    of species, endemic species, fish families,
                    regions, and conservation categories.
                </p>

            </div>

            <div class="feature-card">

                <div class="feature-icon">🗂️</div>

                <h3>Administrative Management</h3>

                <p>
                    Authorized administrators can add, update,
                    view, and manage species records and related
                    classification information.
                </p>

            </div>

            <div class="feature-card">

                <div class="feature-icon">🌊</div>

                <h3>Marine Biodiversity Awareness</h3>

                <p>
                    Provide an accessible educational reference
                    that encourages interest in Philippine reef
                    fish and marine biodiversity.
                </p>

            </div>

        </div>

    </section>

    <!-- DATABASE INFORMATION -->

    <section class="about-card">

        <h2>About the Database</h2>

        <p>
            PinoyDex uses a relational database to organize
            species information and their associated classifications.
            Its database structure connects species records with
            taxonomic families, conservation statuses, local names,
            and geographic regions.
        </p>

        <div class="database-list">

            <div class="database-item">
                <strong>Species</strong>
                <span>Species identification and descriptions</span>
            </div>

            <div class="database-item">
                <strong>Family</strong>
                <span>Taxonomic family classification</span>
            </div>

            <div class="database-item">
                <strong>Conservation Status</strong>
                <span>Recorded conservation categories</span>
            </div>

            <div class="database-item">
                <strong>Local Name</strong>
                <span>Commonly used local species names</span>
            </div>

            <div class="database-item">
                <strong>Region</strong>
                <span>Geographic region references</span>
            </div>

            <div class="database-item">
                <strong>Species Region</strong>
                <span>Species-to-region relationships</span>
            </div>

        </div>

    </section>

    <!-- DATA RELIABILITY -->

    <section class="about-card">

        <h2>Research and Data References</h2>

        <p>
            Species information should be supported by reliable
            scientific references and relevant biodiversity resources.
            Individual records include research source information
            and image attribution whenever available.
        </p>

        <p>
            Image credits identify the respective photographers
            or contributors and applicable licenses where documented.
            Conservation information should be interpreted according
            to the cited assessment source.
        </p>

        <div class="about-highlight">
            <p>
                <strong>Educational Disclaimer:</strong>
                PinoyDex is intended for educational and informational
                purposes. Species records are not a substitute for
                expert taxonomic identification, official conservation
                assessments, or professional scientific research.
            </p>
        </div>

    </section>

</main>

<!-- FOOTER -->

<footer class="about-footer">

    &copy; <?= date('Y') ?> PinoyDex.
    Philippine Reef Fish Biodiversity Database.

</footer>

</body>
</html>
