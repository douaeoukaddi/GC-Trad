<?php
$donnee = new PDO("sqlite:career.db");

// Enable error reporting for PDO
$donnee->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$donnee->exec("
    CREATE TABLE IF NOT EXISTS projects(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT,
        date_de_creature DATETIME,
        description TEXT,
        image TEXT,
        category TEXT
    )
");

// --- START: Code to add missing columns (Run once, then remove or comment out) ---
// IMPORTANT: Make sure you've either deleted career.db or run this section once.
// After successful execution and verification, you can comment out or remove this block.
try {
    // Check if 'date_de_creature' column exists and add if not
    $donnee->exec("ALTER TABLE projects ADD COLUMN date_de_creature DATETIME");
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'duplicate column name: date_de_creature') === false) {
        throw $e;
    }
}

try {
    // Check if 'category' column exists and add if not
    $donnee->exec("ALTER TABLE projects ADD COLUMN category TEXT");
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'duplicate column name: category') === false) {
        throw $e;
    }
}
// --- END: Code to add missing columns ---


// Insert some dummy data if the table is empty (for demonstration)
// You can remove this block once you have real data.
// Ensure your image files (project1.jpg, project2.jpg, etc.) exist in an 'images' folder.
$count = $donnee->query("SELECT COUNT(*) FROM projects")->fetchColumn();
if ($count == 0) {
    $donnee->exec("INSERT INTO projects (nom, date_de_creature, description, image, category) VALUES
        ('UC Merced Social Sciences and Management Building', '2023-01-15 10:00:00', 'Architectural lighting design for a modern academic facility.', 'project1.jpg', 'Higher Ed'),
        ('Lathrop Police Department', '2022-11-20 09:30:00', 'Security and low voltage systems for a new police station.', 'project2.jpg', 'Civic'),
        ('UC Davis Engineering Student Design Center', '2023-03-01 14:00:00', 'Innovative engineering solutions for student workshops and labs.', 'project3.jpg', 'Higher Ed'),
        ('Placer County Health & Human Services Building', '2023-05-10 11:00:00', 'Comprehensive electrical design for a large county facility.', 'project4.jpg', 'Health Care'),
        ('Monterey Regional Airport - Aircraft', '2022-09-05 08:45:00', 'Airfield lighting and power distribution systems.', 'project5.jpg', 'Parking & Transportation'),
        ('Fresno City Hall', '2023-07-22 16:00:00', 'Renovation of electrical systems for a historic city hall.', 'project6.jpg', 'Civic'),
        ('Sacramento Convention Center Expansion', '2023-02-28 13:00:00', 'Electrical infrastructure for large-scale convention center expansion.', 'project7.jpg', 'Commercial & Office'),
        ('Inderkum High School Public Safety', '2023-04-03 10:15:00', 'Safety and security systems for a public high school.', 'project8.jpg', 'K-12')
    ");
}

$st = $donnee->query("SELECT * FROM projects");
$projects = $st->fetchAll(PDO::FETCH_ASSOC);

// Get unique categories for the filter links
$categories_stmt = $donnee->query("SELECT DISTINCT category FROM projects ORDER BY category");
$categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career - GC Trad</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* General Resets & Body */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            background: linear-gradient(#737373, #b4b4b4); /* Light grey gradient background for overall page */
            scroll-behavior: smooth; /* Smooth scrolling for anchor links */
        }

        /* Variables CSS for easier maintenance */
        :root {
            --primary-color: #ffb800; /* Yellow/Orange */
            --secondary-color: #ff8800; /* Darker Orange */
            --text-white: #ffffff;
            --text-dark: #000000;
            --bg-overlay: rgba(255, 136, 0, 0.93); /* Orange overlay for dropdown */
            --transition: all 0.3s ease;
            --dark-grey: #545454; /* For description box */
            --black-bg: #111; /* For stats section */
            --accent-red: #c36477; /* For stat labels */
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 3rem; /* Increased padding */
            background: rgba(0, 0, 0, 0.7); /* More opaque header */
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .logo {
            font-size: clamp(1.8rem, 4vw, 2.8rem); /* Larger logo */
            color: var(--primary-color);
            font-weight: 700; /* Bolder */
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem; /* More space between links */
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-white);
            font-size: clamp(1rem, 1.5vw, 1.2rem); /* Slightly larger font */
            transition: var(--transition);
            padding: 0.5rem 0; /* Vertical padding only */
            position: relative; /* For underline effect */
        }

        .nav-links a::after { /* Underline effect */
            content: '';
            position: absolute;
            width: 0%;
            height: 2px;
            background-color: var(--primary-color);
            left: 0;
            bottom: -5px;
            transition: width 0.3s ease-in-out;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        button {
            padding: 0.8rem 1.8rem; /* Larger button */
            background-color: var(--primary-color);
            color: var(--text-dark);
            border: none;
            border-radius: 5px; /* Less rounded */
            font-weight: 600; /* Bolder text */
            cursor: pointer;
            transition: var(--transition);
            font-size: clamp(0.9rem, 1.5vw, 1.1rem);
            text-transform: uppercase; /* Uppercase text */
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        button:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px); /* Slight lift */
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        }

        /* Dropdown */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: rgba(30, 30, 30, 0.95); /* Darker dropdown background */
            min-width: 220px; /* Wider dropdown */
            box-shadow: 0px 10px 20px rgba(0,0,0,0.5); /* Stronger shadow */
            z-index: 1;
            border-radius: 8px;
            padding: 0.5rem 0; /* Reduced padding */
            top: 100%;
            left: 0;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease-out, transform 0.3s ease-out;
        }

        .dropdown:hover .dropdown-content {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown-content a {
            padding: 0.8rem 1.5rem; /* More padding for items */
            border-bottom: 1px solid rgba(255,255,255,0.08); /* Lighter separator */
            color: var(--text-white);
            font-size: 1rem; /* Fixed size */
            white-space: nowrap;
        }

        .dropdown-content a:last-child {
            border-bottom: none;
        }

        .dropdown-content a:hover {
            background-color: rgba(255, 255, 255, 0.08); /* Subtle hover background */
            color: var(--primary-color);
        }

        /* Language Toggle Icon */
        .lang-toggle {
            font-size: 1.5rem; /* Icon size */
            color: var(--text-white);
            cursor: pointer;
            margin-left: 1rem; /* Space from button */
            transition: var(--transition);
        }

        .lang-toggle:hover {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            .nav-links {
                gap: 0.5rem;
            }

            header {
                padding: 0.8rem 1rem;
            }

            .dropdown-content {
                min-width: 150px;
            }
        }

        /* Hero Section */
        .hero-section {
            background: url('acceuil6.png') no-repeat center center/cover; /* Using your actual image */
            height: 100vh;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Pushes overlay to top, description-box to bottom */
            color: var(--text-white); /* Ensure text is white for hero */
        }

        .hero-overlay {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.2)); /* Darker overlay for text readability */
            flex-grow: 1; /* Makes overlay take remaining space */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding-top: 80px; /* Space for fixed header */
        }

        .hero-title {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
        }

        .hero-subtitle {
            font-size: clamp(1.5rem, 3.5vw, 2.8rem);
            font-weight: 400;
            margin-top: 0.5rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
        }

        .scroll-button {
            background: rgba(255, 255, 255, 0.2); /* Semi-transparent white */
            border: 2px solid var(--text-white);
            color: var(--text-white);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            position: absolute;
            bottom: 2rem; /* Position at the bottom of the hero section */
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite; /* Animation for bounce effect */
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.4);
            cursor: pointer; /* Ensure cursor indicates it's clickable */
        }

        .scroll-button:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--text-dark);
            animation: none; /* Stop bounce on hover */
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateX(-50%) translateY(0);
            }
            40% {
                transform: translateX(-50%) translateY(-15px);
            }
            60% {
                transform: translateX(-50%) translateY(-7px);
            }
        }

        .description-box {
            background-color: var(--dark-grey); /* Dark grey background */
            color: var(--text-white);
            padding: 3rem; /* More padding */
            text-align: left;
            max-width: 100%; /* Ensure it spans full width of parent */
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.3); /* Shadow to separate from hero */
        }

        .description-box h2 {
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            border-bottom: 2px solid var(--primary-color); /* Underline effect */
            padding-bottom: 0.5rem;
            display: inline-block; /* Make underline only as wide as text */
        }

        .description-box p {
            font-size: clamp(1rem, 2vw, 1.2rem);
            line-height: 1.8;
            max-width: 800px; /* Limit line length for readability */
            margin: 0 auto; /* Center paragraph if max-width is applied */
        }

        /* Styling for the container of the titles (category filter) */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 40px auto; /* Adjust margin for spacing below description box */
            padding: 0 20px;
        }

        .titles {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px; /* Slightly reduced gap for more categories */
            margin-bottom: 40px; /* Space below categories */
        }

        .titles a {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: clamp(0.8rem, 1.5vw, 1.1rem); /* Adjusted font size for categories */
            text-decoration: none;
            color: #555; /* Default category link color */
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: var(--transition);
            background-color: #f5f5f5; /* Light background for categories */
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .titles a:hover {
            background-color: var(--primary-color);
            color: var(--text-dark);
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .titles a.active-category { /* Class for the currently selected category */
            background-color: var(--primary-color);
            color: var(--text-dark);
            border-color: var(--primary-color);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }


        /* Project Grid Section */
        .projects-section {
            padding: 40px 20px; /* Add padding around the project grid */
            max-width: 1200px;
            margin: 0 auto;
            background-color: #f9f9f9; /* Light background for the project section */
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 50px; /* Space at the bottom */
        }

        .project-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Responsive grid */
            gap: 30px; /* Space between project cards */
            padding: 20px 0;
        }

        .project-card {
            background-color: var(--text-white);
            border-radius: 8px;
            overflow: hidden; /* Ensures image corners are rounded */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            cursor: pointer;
        }

        .project-card:hover {
            transform: translateY(-5px); /* Lift effect on hover */
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .project-card img {
            width: 100%;
            height: 200px; /* Fixed height for project images */
            object-fit: cover; /* Ensures images cover the area without distortion */
            display: block;
        }

        .project-card-info {
            padding: 15px;
            text-align: center;
            position: absolute; /* Position title over image */
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0)); /* Gradient overlay */
            color: var(--text-white);
            padding-top: 50px; /* Adjust padding for text to appear on gradient */
        }

        .project-card-info h3 {
            font-size: clamp(1rem, 2vw, 1.2rem);
            margin: 0;
            font-weight: 600;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
        }

        /* Optional: Hidden description on card for future use/modal */
        .project-card-info p {
            font-size: 0.9rem;
            opacity: 0; /* Hidden by default */
            max-height: 0;
            overflow: hidden;
            transition: opacity 0.3s ease, max-height 0.3s ease;
            margin-top: 5px;
        }

        .project-card:hover .project-card-info p {
            opacity: 1;
            max-height: 100px; /* Max height to reveal text */
        }


        /* Footer (optional, but good practice to include a basic one) */
        footer {
            background-color: var(--black-bg);
            color: var(--text-white);
            text-align: center;
            padding: 2rem 1rem;
            margin-top: 50px;
            font-size: 0.9rem;
        }

        footer p {
            margin-bottom: 0.5rem;
        }

        footer .social-icons a {
            color: var(--text-white);
            font-size: 1.5rem;
            margin: 0 10px;
            transition: var(--transition);
        }

        footer .social-icons a:hover {
            color: var(--primary-color);
            transform: scale(1.2);
        }

    </style>
</head>
<body>
    <header>
        <div class="logo">GC Trad</div>
        <nav class="nav-links">
            <a href="acceuil.php" data-key="navHome">Home</a>
            <div class="dropdown">
                <a href="#entreprise" data-key="navCompany">Entreprise <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="aboutus.php" data-key="navAboutUs">About us</a>
                    <a href="whyus.php" data-key="navWhyUs">Why us</a>
                    <a href="team.php" data-key="navOurTeam">Our team</a>
                    <a href="client.php" data-key="navClients">Clients</a>
                    <a href="career.php" data-key="navCareer">Career</a>
                </div>
            </div>
            <a href="contact.php" data-key="navContact">Contact</a>
            <a href="faq.php" data-key="navFAQ">FAQ</a>
            <button onclick="location.href='contact.php'" data-key="btnContactUs">Contact Us</button>
            <i class="fas fa-globe lang-toggle" id="langToggle" title="Toggle Language"></i>
        </nav>
    </header>

    <section class="hero-section">
        <div class="hero-overlay">
            <h1 class="hero-title" data-key="heroTitle">Our Projects</h1>
            <button id="scrollButton" class="scroll-button">&#11167;</button>
        </div>
        <div class="description-box" id="about-section">
            <h2 data-key="aboutSectionTitle">PROJECTS</h2>
            <p data-key="aboutSectionText">The Engineering Enterprise has worked on thousands of projects since our inception in 1974. The project page links below offer a summary of our work by project type. Each project category encompasses multiple examples of completed projects for which we designed the electrical and/or low voltage systems. Our firm can handle projects of any size and complexity.</p>
        </div>
    </section>

    <section class="projects-section">
        <div class="container">
            <div class="titles">
                <a href="#" class="active-category" data-category="all" data-key="categoryAll">All</a>
                <?php foreach ($categories as $category): ?>
                    <a href="#" data-category="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></a>
                <?php endforeach; ?>
            </div>

            <div class="project-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-card" data-category="<?= htmlspecialchars($project['category']) ?>">
                        <img src="images/<?= htmlspecialchars($project['image']) ?>" alt="<?= htmlspecialchars($project['nom']) ?>">
                        <div class="project-card-info">
                            <h3><?= htmlspecialchars($project['nom']) ?></h3>
                            </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2025 GC Trad. All rights reserved.</p>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll for the down arrow button
            const scrollButton = document.getElementById('scrollButton');
            scrollButton.addEventListener('click', function() {
                document.getElementById('about-section').scrollIntoView({
                    behavior: 'smooth'
                });
            });

            // Project filtering logic
            const categoryLinks = document.querySelectorAll('.titles a');
            const projectCards = document.querySelectorAll('.project-card');

            categoryLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent default anchor link behavior

                    // Remove active class from all links
                    categoryLinks.forEach(l => l.classList.remove('active-category'));
                    // Add active class to the clicked link
                    this.classList.add('active-category');

                    const selectedCategory = this.dataset.category;

                    projectCards.forEach(card => {
                        const cardCategory = card.dataset.category;
                        if (selectedCategory === 'all' || cardCategory === selectedCategory) {
                            card.style.display = 'block'; // Show the card
                        } else {
                            card.style.display = 'none'; // Hide the card
                        }
                    });
                });
            });


            // --- Language Translation Logic ---
            const translations = {
                'en': {
                    // Navigation
                    'navHome': 'Home',
                    'navCompany': 'Company',
                    'navAboutUs': 'About us',
                    'navWhyUs': 'Why us',
                    'navOurTeam': 'Our team',
                    'navClients': 'Clients',
                    'navCareer': 'Career',
                    'navContact': 'Contact',
                    'navFAQ': 'FAQ',
                    'btnContactUs': 'Contact Us',
                    // Hero Section
                    'heroTitle': 'Our Projects',
                    // Description Box
                    'aboutSectionTitle': 'PROJECTS',
                    'aboutSectionText': 'The Engineering Enterprise has worked on thousands of projects since our inception in 1974. The project page links below offer a summary of our work by project type. Each project category encompasses multiple examples of completed projects for which we designed the electrical and/or low voltage systems. Our firm can handle projects of any size and complexity.',
                    // Project Categories (only 'All' needs a data-key as others are dynamic)
                    'categoryAll': 'All',
                    // Note: Project names from DB won't be translated by this JS.
                    // If project names need translation, they should be stored in the DB in multiple languages
                    // or fetched from a different translation source.
                },
                'fr': {
                    // Navigation
                    'navHome': 'Accueil',
                    'navCompany': 'Entreprise',
                    'navAboutUs': 'À propos de nous',
                    'navWhyUs': 'Pourquoi nous',
                    'navOurTeam': 'Notre équipe',
                    'navClients': 'Clients',
                    'navCareer': 'Carrières',
                    'navContact': 'Contact',
                    'navFAQ': 'FAQ',
                    'btnContactUs': 'Nous Contacter',
                    // Hero Section
                    'heroTitle': 'Nos Projets',
                    // Description Box
                    'aboutSectionTitle': 'PROJETS',
                    'aboutSectionText': 'The Engineering Enterprise a travaillé sur des milliers de projets depuis sa création en 1974. Les liens de la page de projet ci-dessous offrent un résumé de notre travail par type de projet. Chaque catégorie de projet englobe de multiples exemples de projets achevés pour lesquels nous avons conçu les systèmes électriques et/ou à basse tension. Notre entreprise peut gérer des projets de toute taille et complexité.',
                    // Project Categories
                    'categoryAll': 'Tout',
                }
            };

            let currentLanguage = 'fr'; // Default language

            // Function to update content based on the current language
            function updateContent() {
                document.querySelectorAll('[data-key]').forEach(element => {
                    const key = element.dataset.key;
                    if (translations[currentLanguage][key]) {
                        // Special handling for button text
                        if (element.tagName === 'BUTTON') {
                            element.textContent = translations[currentLanguage][key];
                        } else {
                            element.textContent = translations[currentLanguage][key];
                        }
                    }
                });

                // Update the lang attribute of the HTML tag
                document.documentElement.lang = currentLanguage;

                 // Update "Entreprise" dropdown text and caret
                const entrepriseLink = document.querySelector('a[data-key="navCompany"]');
                if (entrepriseLink) {
                    const baseText = translations[currentLanguage]['navCompany'];
                    entrepriseLink.innerHTML = `${baseText} <i class="fas fa-caret-down"></i>`;
                }

                // Translate dynamic category links
                // This will only translate the category names if they match exact keys in 'translations'.
                // For categories fetched from the DB, you'd need a more complex solution (e.g., storing translated categories in DB or a separate JSON).
                // For simplicity, I'm assuming you want to translate the *display* of the category names
                // if they are static or can be mapped.
                // For now, only 'All' is directly translatable via data-key.
                // If the categories themselves (like 'Higher Ed', 'Civic') need translation,
                // you would need to store translations for them in the 'translations' object or fetch them
                // in the correct language from the database.
            }

            // Event listener for the language toggle icon
            const langToggle = document.getElementById('langToggle');
            langToggle.addEventListener('click', function() {
                currentLanguage = (currentLanguage === 'fr') ? 'en' : 'fr'; // Toggle language
                updateContent(); // Update all text on the page
            });

            // Initial content load
            updateContent();
        });
    </script>
</body>
</html>