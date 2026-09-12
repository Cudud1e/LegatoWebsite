<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>VIP Packages | LEGATO Events & Productions</title>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="style.css" />
        <style>
            /* --- FOOTER GRID FIX --- */
            #contact { background-color: #121212; color: #F5F2EB; padding-top: 3rem; padding-bottom: 1.5rem; border-top: 1px solid #262626; margin-top: 4rem; }
            #contact .container.footer-grid { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 2.5rem; align-items: start; }
            #contact .footer-tagline { color: #a3a3a3; font-size: 0.875rem; margin-top: 0.75rem; max-width: 300px; line-height: 1.5; }
            #contact .footer-title { color: #D4AF37; font-family: 'Montserrat', sans-serif; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; }
            #contact .footer-links { display: flex; flex-direction: column; gap: 0.5rem; }
            #contact .footer-links a, #contact .footer-links span { color: #d4d4d4; font-size: 0.875rem; text-decoration: none; transition: color 0.2s ease; }
            #contact .footer-links a:hover { color: #D4AF37; }
            #contact .footer-bottom { border-top: 1px solid #262626; margin-top: 2.5rem; padding-top: 1.5rem; }
            #contact .footer-bottom .container { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; color: #737373; text-transform: uppercase; letter-spacing: 0.05em; }
            @media (max-width: 768px) { #contact .container.footer-grid { grid-template-columns: 1fr; gap: 2rem; } #contact .footer-bottom .container { flex-direction: column; gap: 0.5rem; text-align: center; } }
        </style>
    </head>
    <body class="inner-page">
        <header class="navbar">
            <div class="container nav-content">
                    <a href="index.php" class="brand">
                    <img class="custom-logo h-16 sm:h-20 lg:h-24 w-auto object-contain transition-transform duration-200 hover:scale-105" src="Assest/legato1.png" alt="LEGATO Events & Productions" />
                </a>
                <nav class="nav-links" id="navLinks">
                    <a href="index.php">Home</a>
                    <a href="about.php">About Us</a>
                    <a class="active" href="packages.php">VIP Packages</a>
                    <a href="custom.php">Custom Services</a>
                    <a href="business_info.php">Policies &amp; Info</a>
                </nav>
                <div class="nav-actions">
                    <a href="booking.php" class="btn btn-gold nav-book">Book An Event</a>
                    <a href="login.php" class="nav-login">Log In</a>
                    <button class="menu-button" id="menuButton" aria-label="Open menu">☰</button>
                </div>
            </div>
        </header>
        <main>
            <section class="page-hero">
                <div class="container">
                    <p class="section-label">SIGNATURE EXPERIENCES</p>
                    <h1>Choose your <em>moment.</em>
                </h1>
                <p>Complete, high-end production packages designed for seamless execution.</p>
            </div>
        </section>
        <section class="packages-section">
            <div class="container">
                <div class="package-grid">
                    <article class="package-card">
                        <p class="package-name">VIP 1</p>
                        <h3>The Elite Starter</h3>
                        <div class="package-price">
                            <strong>₱49,999</strong>
                            <span>Up to 50 Guests</span>
                        </div>
                        <ul>
                            <li>Standard Sound · 2 Speakers, 2 Mics</li>
                            <li>24 LED Pars Ambient Lighting</li>
                            <li>1 Professional Emcee</li>
                            <li>Sound Tech & Program Guidance</li>
                        </ul>
                        <a href="booking.php?package=VIP1" class="btn btn-outline package-button">Select VIP 1</a>
                    </article>
                    <article class="package-card featured-package">
                        <span class="featured-label">MOST REQUESTED</span>
                        <p class="package-name">VIP 2</p>
                        <h3>The Prestige Experience</h3>
                        <div class="package-price">
                            <strong>₱79,999</strong>
                            <span>50–150 Guests</span>
                        </div>
                        <ul>
                            <li>Enhanced Sound · 4–6 Speakers, Mixer</li>
                            <li>Moving Heads & LED Wall</li>
                            <li>1 Professional Emcee</li>
                            <li>Full Event Coordination & Timeline</li>
                            <li>Dedicated Vendor Liaison</li>
                        </ul>
                        <a href="booking.php?package=VIP2" class="btn btn-gold package-button">Select VIP 2</a>
                    </article>
                    <article class="package-card">
                        <p class="package-name">VIP 3</p>
                        <h3>The Grand Luxe Production</h3>
                        <div class="package-price">
                            <strong>₱179,999</strong>
                            <span>150+ Guests</span>
                        </div>
                        <ul>
                            <li>Advanced Line Array & Subwoofers</li>
                            <li>Full Stage Lighting + FX</li>
                            <li>2 Professional Emcees</li>
                            <li>Complete Creative Direction & On-Site Team</li>
                            <li>Photo/Video & VIP Concierge</li>
                        </ul>
                        <a href="booking.php?package=VIP3" class="btn btn-outline package-button">Select VIP 3</a>
                    </article>
                </div>
            </div>
        </section>
    </main>
    <footer id="contact">
    <div class="container footer-grid">
        <div>
            <a href="index.php" class="brand">
                <img
                    class="footer-custom-logo h-12 sm:h-14 w-auto object-contain transition-transform duration-200 hover:scale-105"
                    src="Assest/legato1.png"
                    alt="LEGATO Events & Productions"
                />
            </a>
            <p class="footer-tagline">
                Where flawless production meets unforgettable celebration.
            </p>
        </div>
        <div>
            <p class="footer-title">Quick Links</p>
            <div class="footer-links">
                <a href="custom.php">Services</a>
                <a href="packages.php">Pricing</a>
                <a href="business_info.php">Terms &amp; Conditions</a>
                <a href="business_info.php">Privacy Policy</a>
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
