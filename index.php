<?php
require_once __DIR__ . '/includes/session.php';
$sessionUser = $_SESSION['user'] ?? [];
$isLoggedIn = isset($sessionUser['id']);
$userEmail = htmlspecialchars((string) ($sessionUser['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$userNickname = htmlspecialchars((string) ($sessionUser['nickname'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>LEGATO Events & Productions</title>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap"
    rel="stylesheet"
  />
        <link rel="stylesheet" href="style.css" />
    </head>
    <body>
        <header class="navbar">
            <div class="container nav-content">
                <a href="index.php" class="brand">
                    <img
    class="custom-logo h-16 sm:h-20 lg:h-24 w-auto object-contain transition-transform duration-200 hover:scale-105"
  src="Assest/legato1.png"
  alt="LEGATO Events & Productions"
/>
                </a>
                <nav class="nav-links" id="navLinks">
                    <a class="active" href="index.php">Home</a>
                    <a href="about.php">About Us</a>
                    <a href="packages.php">VIP Packages</a>
                    <a href="custom.php">Custom Services</a>
                    <a href="business_info.php">Policies &amp; Info</a>
                </nav>
                <div class="nav-actions">
                    <a href="booking.php" class="btn btn-gold nav-book">Book An Event</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="profile.php" class="nav-login">My Account</a>
                        <a href="logout.php" class="nav-login">Log Out</a>
                    <?php else: ?>
                    <a href="login.php?redirect=booking.php&message=Please+log+in+or+create+an+account+to+finalize+your+event+booking." class="nav-login">Log In</a>
                <?php endif; ?>
                <button class="menu-button" id="menuButton" aria-label="Open menu">
          ☰
                </button>
            </div>
        </div>
    </header>
    <main>
        <section class="hero" id="home">
            <div class="hero-pattern">
            </div>
            <div class="container hero-content">
                <?php if ($isLoggedIn): ?>
                    <div class="welcome-panel">
                        <?php echo $userNickname ?: $userEmail; ?>
                    </div>
                <?php endif; ?>
                <span class="eyebrow-badge">Premium Event Production in Dumaguete City</span>
                <h1>
          Redefining How Events Are
                    <em>Experienced,</em>
          Managed, and Remembered.
                </h1>
                <p class="hero-description">
          Complete, worry-free event solutions from crystal clear sound and
          dynamic lighting to professional emcees and full scale event direction.
                </p>
                <div class="hero-actions">
                    <a class="btn btn-gold" href="#packages">Explore VIP Packages</a>
                    <a class="btn btn-outline" href="#custom">Build Custom Package</a>
                </div>
                <div class="feature-tags">
                    <span>State of the-Art AV Gear</span>
                    <span>Top-Tier Professional Emcees</span>
                    <span>100% On Time & Stress Free Execution</span>
                </div>
            </div>
        </section>
        <section class="why-section" id="about">
            <div class="container">
                <p class="section-label">THE LEGATO DIFFERENCE</p>
                <h2>Why LEGATO Events & Productions?</h2>
                <div class="why-grid">
                    <article class="why-card">
                        <span class="card-number">01</span>
                        <h3>All-In-One Event Solution</h3>
                        <p>
              Eliminate vendor stress. We handle audio, stage lighting, hosting,
              and program flow under a single synchronized team.
                        </p>
                    </article>
                    <article class="why-card">
                        <span class="card-number">02</span>
                        <h3>Professional Quality & Technical Excellence</h3>
                        <p>
              Premium audio clarity, dynamic LED lighting, moving heads, and
              high-performance equipment operated by certified technicians.
                        </p>
                    </article>
                    <article class="why-card">
                        <span class="card-number">03</span>
                        <h3>Uplifting Local Talent</h3>
                        <p>
              Powered by top regional emcees, directors, and production crews
              dedicated to elevating every milestone.
                        </p>
                    </article>
                </div>
            </div>
        </section>
        <section class="packages-section" id="packages">
            <div class="container">
                <div class="section-heading center">
                    <p class="section-label">SIGNATURE EXPERIENCES</p>
                    <h2>Curated VIP Event Packages</h2>
                    <p>
            Choose a complete, high-end package designed for seamless execution.
                    </p>
                </div>
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
        <section class="custom-section" id="custom">
            <div class="container custom-grid">
                <div>
                    <p class="section-label">TAILORED TO YOUR OCCASION</p>
                    <h2>Build Your Own Custom Package</h2>
                    <p class="custom-description">
            Select only the specific services you need for your event format.
            A LEGATO specialist will finalize your tailored production plan.
                    </p>
                    <div class="service-list">
                        <label class="service-item">
                            <input type="checkbox" data-price="18000" checked />
                            <span>Audio System</span>
                            <b>₱18,000</b>
                        </label>
                        <label class="service-item">
                            <input type="checkbox" data-price="12000" checked />
                            <span>Professional EMCEE / Host</span>
                            <b>₱12,000</b>
                        </label>
                        <label class="service-item">
                            <input type="checkbox" data-price="10000" checked />
                            <span>Stage & Décor Support</span>
                            <b>₱10,000</b>
                        </label>
                        <label class="service-item">
                            <input type="checkbox" data-price="15000" checked />
                            <span>Visual & Multimedia Support</span>
                            <b>₱15,000</b>
                        </label>
                        <label class="service-item">
                            <input type="checkbox" data-price="18000" />
                            <span>Photography & Videography</span>
                            <b>₱18,000</b>
                        </label>
                        <label class="service-item">
                            <input type="checkbox" data-price="25000" />
                            <span>Full Event Management</span>
                            <b>₱25,000</b>
                        </label>
                    </div>
                </div>
                <aside class="estimate-card">
                    <p class="section-label">YOUR PRELIMINARY INVESTMENT</p>
                    <div class="estimate-total">
                        <span>Estimated Cost</span>
                        <strong id="estimatedCost">₱55,000</strong>
                    </div>
                    <p>
            Includes selected production services. Final quote is confirmed after
            venue, event duration, and technical requirements review.
                    </p>
                    <a href="booking.php?package=Custom%20Build" class="btn btn-gold full-width">
            Reserve Custom Booking
                    </a>
                </aside>
            </div>
        </section>
        <section class="coverage-section">
            <div class="container coverage-grid">
                <div>
                    <p class="section-label">WHEREVER THE MOMENT TAKES YOU</p>
                    <h2>Mobile & Ready Across Negros Oriental</h2>
                    <p>
            Based in Dumaguete City, our mobile production team brings the
            complete LEGATO experience to celebrations across the region.
                    </p>
                </div>
                <div class="coverage-map">
                    <div class="ring ring-3">
                    </div>
                    <div class="ring ring-2">
                    </div>
                    <div class="ring ring-1">
                    </div>
                    <div class="map-center">Dumaguete<br />City</div>
                    <span class="ring-label vip3">VIP 3 · 50km+</span>
                    <span class="ring-label vip2">VIP 2 · 25km</span>
                    <span class="ring-label vip1">VIP 1 · 15km</span>
                </div>
            </div>
        </section>
    </main>
    <footer id="contact">
        <div class="container footer-grid">
            <div>
                <a href="#" class="brand">
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
