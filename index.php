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
        <script src="https://cdn.tailwindcss.com"></script>
        <script>tailwind.config={theme:{extend:{colors:{obsidian:'#121212',charcoal:'#181818',line:'#282828',gold:'#D4AF37',ivory:'#F5F2EB'},fontFamily:{serif:['Playfair Display','serif'],sans:['Montserrat','sans-serif']}}}}</script>
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
        <section class="border-y border-[#282828] bg-[#121212] py-20 sm:py-24" id="showcase" aria-labelledby="signature-moments-title">
            <div class="mx-auto w-full max-w-[1200px] px-5 sm:px-8">
                <div class="max-w-2xl">
                    <p class="font-mono text-[11px] font-semibold uppercase tracking-[0.22em] text-[#D4AF37]">UNFORGETTABLE EXPERIENCES</p>
                    <h2 id="signature-moments-title" class="mt-3 font-serif text-4xl leading-tight text-[#F5F2EB] sm:text-5xl">Our Signature <em>Moments.</em></h2>
                    <p class="mt-5 text-sm leading-7 text-[#9CA3AF] sm:text-base">From the energy in the room to the details no guest ever sees, every LEGATO event is built with disciplined production and genuine care.</p>
                </div>
                <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <article class="group relative min-h-[390px] overflow-hidden rounded-2xl border border-[#D4AF37]/20 bg-[#181818] transition-all duration-300 hover:scale-[1.02] hover:border-[#D4AF37]/60 hover:shadow-lg hover:shadow-[#D4AF37]/10">
                        <button type="button" class="showcase-trigger absolute inset-0 h-full w-full text-left focus:outline-none focus:ring-2 focus:ring-[#D4AF37]" data-showcase-src="assets/pic1.jpg" data-showcase-fallback="Assest/pic1.jpg" data-showcase-alt="LEGATO premium main stage production" data-showcase-title="Premium Stage Production" data-showcase-caption="Crystal-clear audio, immersive lighting, and a technical team in complete control." aria-label="Preview Premium Stage Production image">
                            <img src="assets/pic1.jpg" onerror="this.onerror=null;this.src='Assest/pic1.jpg';" alt="LEGATO premium main stage production" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy">
                            <span class="absolute left-5 top-5 rounded-full border border-[#D4AF37]/30 bg-[#121212]/70 px-3 py-1 font-mono text-[10px] uppercase tracking-wider text-[#D4AF37] backdrop-blur-md">Main Stage</span>
                            <span class="absolute inset-0 bg-gradient-to-t from-[#121212] via-[#121212]/25 to-transparent"></span>
                        </button>
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 p-6"><p class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#9CA3AF]">VIP Production · Dumaguete</p><h3 class="mt-2 font-serif text-2xl text-[#F5F2EB]">Premium Stage Production</h3><p class="mt-2 pr-2 text-sm leading-6 text-[#F5F2EB]/80">Crystal-clear audio, immersive lighting, and a technical team in complete control.</p></div>
                        <a href="booking.php" class="absolute bottom-6 left-6 z-10 text-xs font-semibold uppercase tracking-wider text-[#D4AF37] underline decoration-[#D4AF37]/50 underline-offset-4 transition hover:text-[#F5F2EB]">Book Similar Event →</a>
                    </article>
                    <article class="group relative min-h-[390px] overflow-hidden rounded-2xl border border-[#D4AF37]/20 bg-[#181818] transition-all duration-300 hover:scale-[1.02] hover:border-[#D4AF37]/60 hover:shadow-lg hover:shadow-[#D4AF37]/10">
                        <button type="button" class="showcase-trigger absolute inset-0 h-full w-full text-left focus:outline-none focus:ring-2 focus:ring-[#D4AF37]" data-showcase-src="assets/pic2.jpg" data-showcase-fallback="Assest/pic2pg.jpg" data-showcase-alt="Guests enjoying a LEGATO event atmosphere" data-showcase-title="Atmosphere Guests Remember" data-showcase-caption="A celebration that feels effortless from the first welcome to the final applause." aria-label="Preview Atmosphere Guests Remember image">
                            <img src="assets/pic2.jpg" onerror="this.onerror=null;this.src='Assest/pic2pg.jpg';" alt="Guests enjoying a LEGATO event atmosphere" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy">
                            <span class="absolute left-5 top-5 rounded-full border border-[#D4AF37]/30 bg-[#121212]/70 px-3 py-1 font-mono text-[10px] uppercase tracking-wider text-[#D4AF37] backdrop-blur-md">Guest Experience</span>
                            <span class="absolute inset-0 bg-gradient-to-t from-[#121212] via-[#121212]/25 to-transparent"></span>
                        </button>
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 p-6"><p class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#9CA3AF]">Grand Celebration · Live Energy</p><h3 class="mt-2 font-serif text-2xl text-[#F5F2EB]">Atmosphere Guests Remember</h3><p class="mt-2 pr-2 text-sm leading-6 text-[#F5F2EB]/80">A celebration that feels effortless from the first welcome to the final applause.</p></div>
                        <a href="booking.php" class="absolute bottom-6 left-6 z-10 text-xs font-semibold uppercase tracking-wider text-[#D4AF37] underline decoration-[#D4AF37]/50 underline-offset-4 transition hover:text-[#F5F2EB]">Book Similar Event →</a>
                    </article>
                    <article class="group relative min-h-[390px] overflow-hidden rounded-2xl border border-[#D4AF37]/20 bg-[#181818] transition-all duration-300 hover:scale-[1.02] hover:border-[#D4AF37]/60 hover:shadow-lg hover:shadow-[#D4AF37]/10">
                        <button type="button" class="showcase-trigger absolute inset-0 h-full w-full text-left focus:outline-none focus:ring-2 focus:ring-[#D4AF37]" data-showcase-src="assets/pic3.jpg" data-showcase-fallback="Assest/pic3.jpg" data-showcase-alt="LEGATO crew preparing behind the scenes" data-showcase-title="Precision Behind the Scenes" data-showcase-caption="The thoughtful planning and practiced teamwork that let every event run beautifully." aria-label="Preview Precision Behind the Scenes image">
                            <img src="assets/pic3.jpg" onerror="this.onerror=null;this.src='Assest/pic3.jpg';" alt="LEGATO crew preparing behind the scenes" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy">
                            <span class="absolute left-5 top-5 rounded-full border border-[#D4AF37]/30 bg-[#121212]/70 px-3 py-1 font-mono text-[10px] uppercase tracking-wider text-[#D4AF37] backdrop-blur-md">Behind The Scenes</span>
                            <span class="absolute inset-0 bg-gradient-to-t from-[#121212] via-[#121212]/25 to-transparent"></span>
                        </button>
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 p-6"><p class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#9CA3AF]">Crew Focus · Event Execution</p><h3 class="mt-2 font-serif text-2xl text-[#F5F2EB]">Precision Behind the Scenes</h3><p class="mt-2 pr-2 text-sm leading-6 text-[#F5F2EB]/80">The thoughtful planning and practiced teamwork that let every event run beautifully.</p></div>
                        <a href="booking.php" class="absolute bottom-6 left-6 z-10 text-xs font-semibold uppercase tracking-wider text-[#D4AF37] underline decoration-[#D4AF37]/50 underline-offset-4 transition hover:text-[#F5F2EB]">Book Similar Event →</a>
                    </article>
                </div>
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
    <footer id="contact" class="site-footer">
        <div class="footer-container">
            <div class="footer-column footer-brand-section">
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
            <div class="footer-column">
                <p class="footer-title">Quick Links</p>
                <div class="footer-links">
                    <a href="custom.php">Services</a>
                    <a href="packages.php">Pricing</a>
                    <a href="terms.php">Terms &amp; Conditions</a>
                    <a href="privacy.php">Privacy Policy</a>
                    <a href="business_info.php">Business Info</a>
                </div>
            </div>
            <div class="footer-column">
                <p class="footer-title">Get In Touch</p>
                <div class="footer-links">
                    <span>Dumaguete City, Philippines 6200</span>
                    <a href="mailto:info@legatoevents.com">info@legatoevents.com</a>
                    <a href="tel:+639171234567">+63 917 123 4567</a>
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
    <div id="showcaseLightbox" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/95 p-4 opacity-0 backdrop-blur-md transition-opacity duration-300" role="dialog" aria-modal="true" aria-labelledby="showcaseLightboxTitle">
        <button id="showcaseClose" type="button" class="absolute right-5 top-5 z-10 rounded-full border border-[#D4AF37]/40 bg-[#121212]/80 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-[#D4AF37] transition hover:bg-[#D4AF37] hover:text-[#121212]" aria-label="Close image preview">Close ×</button>
        <div class="relative w-full max-w-6xl overflow-hidden rounded-2xl border border-[#D4AF37]/30 bg-[#181818] shadow-2xl"><img id="showcaseLightboxImage" src="" alt="" class="max-h-[72vh] w-full bg-[#121212] object-contain"><div class="border-t border-[#282828] px-6 py-5 sm:px-8"><p class="font-mono text-[10px] uppercase tracking-[0.18em] text-[#D4AF37]">LEGATO SIGNATURE MOMENT</p><h2 id="showcaseLightboxTitle" class="mt-2 font-serif text-3xl text-[#F5F2EB]"></h2><p id="showcaseLightboxCaption" class="mt-2 text-sm leading-6 text-[#9CA3AF]"></p></div></div>
    </div>
    <script>
        (() => {
            const lightbox = document.getElementById('showcaseLightbox'), image = document.getElementById('showcaseLightboxImage'), title = document.getElementById('showcaseLightboxTitle'), caption = document.getElementById('showcaseLightboxCaption'), closeButton = document.getElementById('showcaseClose');
            let lastTrigger = null;
            const close = () => { lightbox.classList.remove('opacity-100'); setTimeout(() => lightbox.classList.add('hidden'), 300); lastTrigger?.focus(); };
            document.querySelectorAll('.showcase-trigger').forEach((trigger) => trigger.addEventListener('click', () => { lastTrigger = trigger; image.src = trigger.dataset.showcaseSrc; image.onerror = () => { image.onerror = null; image.src = trigger.dataset.showcaseFallback; }; image.alt = trigger.dataset.showcaseAlt; title.textContent = trigger.dataset.showcaseTitle; caption.textContent = trigger.dataset.showcaseCaption; lightbox.classList.remove('hidden'); requestAnimationFrame(() => lightbox.classList.add('opacity-100')); closeButton.focus(); }));
            closeButton.addEventListener('click', close);
            lightbox.addEventListener('click', (event) => { if (event.target === lightbox) close(); });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !lightbox.classList.contains('hidden')) close(); });
        })();
    </script>
</body>
</html>
