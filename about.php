<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>About Us | LEGATO Events & Productions</title>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="style.css" />
    </head>
    <body class="inner-page">
        <header class="navbar">
            <div class="container nav-content">
                <a href="index.html" class="brand">
                    <img class="custom-logo h-16 sm:h-20 lg:h-24 w-auto object-contain transition-transform duration-200 hover:scale-105" src="Assest/legato1.png" alt="LEGATO Events & Productions" />
                </a>
                <nav class="nav-links" id="navLinks">
                    <a href="index.html">Home</a>
                    <a class="active" href="about.html">About Us</a>
                    <a href="packages.html">VIP Packages</a>
                    <a href="custom.html">Custom Services</a>
                    <a href="business_info.php">Policies &amp; Info</a>
                </nav>
                <div class="nav-actions">
                    <a href="booking.php" class="btn btn-gold nav-book">Book An Event</a>
                    <?php include __DIR__ . '/includes/auth_nav.php'; ?>
                    <button class="menu-button" id="menuButton" aria-label="Open menu">☰</button>
                </div>
            </div>
        </header>
        <main>
            <section class="page-hero">
                <div class="container">
                    <p class="section-label">THE LEGATO DIFFERENCE</p>
                    <h1>Events with <em>meaning.</em>
                </h1>
                <p>We bring technical precision, creative direction, and genuine local talent together so every celebration feels effortless and unforgettable.</p>
            </div>
        </section>
        <section class="inner-section">
            <div class="container story-grid">
                <div>
                    <p class="section-label">WHO WE ARE</p>
                    <h2>Production that lets you be present.</h2>
                </div>
                <div>
                    <p>LEGATO Events & Productions is a Dumaguete-based event production team for milestones that deserve more than a standard setup. From the first program cue to the final applause, we coordinate the details behind the scenes with calm, careful execution.</p>
                    <p>Our team combines premium audio and lighting equipment with professional hosts, directors, and technical crew. The result is a polished event that still feels personal to the people at its center.</p>
                </div>
            </div>
        </section>
        <section class="why-section">
            <div class="container">
                <p class="section-label">WHAT WE VALUE</p>
                <h2>Clear planning. Warm service. Memorable results.</h2>
                <div class="why-grid">
                    <article class="why-card">
                        <span class="card-number">01</span>
                        <h3>One trusted team</h3>
                        <p>Audio, lighting, hosting, and program flow stay connected from planning to showtime.</p>
                    </article>
                    <article class="why-card">
                        <span class="card-number">02</span>
                        <h3>Technical excellence</h3>
                        <p>Reliable equipment and experienced operators protect the quality of every moment.</p>
                    </article>
                    <article class="why-card">
                        <span class="card-number">03</span>
                        <h3>Local talent</h3>
                        <p>We uplift the region's emcees, directors, and production professionals.</p>
                    </article>
                </div>
            </div>
        </section>
    </main>
    <footer id="contact">
        <div class="container footer-grid">
            <div>
                <a href="index.html" class="brand">
                    <img class="footer-custom-logo h-12 sm:h-14 w-auto object-contain transition-transform duration-200 hover:scale-105" src="Assest/legato1.png" alt="LEGATO Events & Productions" />
                </a>
                <p class="footer-tagline">Where flawless production meets unforgettable celebration.</p>
            </div>
            <div>
                <p class="footer-title">Explore</p>
                <div class="footer-links">
                    <a href="about.html">About Us</a>
                    <a href="packages.html">VIP Packages</a>
                    <a href="custom.html">Custom Services</a>
                    <a href="login.html">Log In</a>
                    <a href="terms.php">Terms &amp; Conditions</a>
                    <a href="privacy.php">Privacy Policy</a>
                    <a href="business_info.php">Business Info</a>
                </div>
            </div>
            <div>
                <p class="footer-title">Contact</p>
                <div class="footer-links">
                    <span>Dumaguete City, Philippines</span>
                    <a href="mailto:info@legatoevents.com">info@legatoevents.com</a>
                    <a href="tel:+639000000000">+63 9XX XXX XXXX</a>
                    <span>Monday to Saturday, 9:00 AM to 6:00 PM</span>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <span>© 2026 LEGATO Events & Productions. All Rights Reserved.</span>
                <span>Dumaguete · Negros Oriental</span>
            </div>
        </div>
    </footer>
    <script src="script.js">
    </script>
</body>
</html>
