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
        <script src="https://cdn.tailwindcss.com"></script>
        <script>tailwind.config={theme:{extend:{colors:{obsidian:'#121212',charcoal:'#181818',line:'#282828',gold:'#D4AF37',ivory:'#F5F2EB'},fontFamily:{serif:['Playfair Display','serif'],sans:['Montserrat','sans-serif']}}}}</script>
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
                    <a class="active" href="about.php">About Us</a>
                    <a href="packages.php">VIP Packages</a>
                    <a href="custom.php">Custom Services</a>
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
        <section class="border-y border-[#282828] bg-[#121212] py-20 sm:py-24" aria-labelledby="event-highlights-title">
            <div class="mx-auto w-full max-w-[1200px] px-5 sm:px-8">
                <div class="max-w-2xl">
                    <p class="font-mono text-[11px] font-semibold uppercase tracking-[0.22em] text-[#D4AF37]">EVENT HIGHLIGHTS</p>
                    <h2 id="event-highlights-title" class="mt-3 font-serif text-4xl leading-tight text-[#F5F2EB] sm:text-5xl">The moments behind the <em>magic.</em></h2>
                    <p class="mt-5 text-sm leading-7 text-[#9CA3AF] sm:text-base">A glimpse into the calm preparation, technical precision, and human energy that shape every LEGATO celebration.</p>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <button type="button" class="gallery-trigger group relative min-h-[420px] overflow-hidden rounded-2xl border border-[#D4AF37]/20 bg-[#181818] text-left shadow-2xl transition-all duration-300 hover:scale-[1.02] hover:border-[#D4AF37]/60 focus:outline-none focus:ring-2 focus:ring-[#D4AF37]" data-gallery-src="Assest/pic1.jpg" data-gallery-alt="LEGATO event showcase and main stage" data-gallery-title="The Main Stage" data-gallery-caption="Immersive lighting, confident sound, and every cue placed exactly where it belongs.">
                        <img src="Assest/pic1.jpg" alt="LEGATO event showcase and main stage" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#121212] via-[#121212]/25 to-transparent"></div>
                        <span class="absolute left-5 top-5 rounded-full border border-[#D4AF37]/30 bg-[#121212]/70 px-3 py-1.5 font-mono text-[10px] font-semibold tracking-[0.15em] text-[#D4AF37] backdrop-blur-md">LIVE EVENT</span>
                        <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8"><span class="rounded border border-[#282828] bg-[#121212]/75 px-2 py-1 font-mono text-[10px] uppercase tracking-wider text-[#9CA3AF] backdrop-blur">Dumaguete City · Production</span><h3 class="mt-4 font-serif text-3xl text-[#F5F2EB]">The Main Stage</h3><p class="mt-2 max-w-lg text-sm leading-6 text-[#F5F2EB]/80">Immersive lighting, confident sound, and every cue placed exactly where it belongs.</p></div>
                    </button>

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-1">
                        <button type="button" class="gallery-trigger group relative min-h-[260px] overflow-hidden rounded-2xl border border-[#D4AF37]/20 bg-[#181818] text-left transition-all duration-300 hover:scale-[1.02] hover:border-[#D4AF37]/60 focus:outline-none focus:ring-2 focus:ring-[#D4AF37]" data-gallery-src="Assest/pic2pg.jpg" data-gallery-alt="Guests enjoying a LEGATO celebration" data-gallery-title="A Room Full of Energy" data-gallery-caption="From the first welcome to the final applause, we shape a flow guests can feel.">
                            <img src="Assest/pic2pg.jpg" alt="Guests enjoying a LEGATO celebration" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#121212] via-[#121212]/20 to-transparent"></div>
                            <span class="absolute left-5 top-5 rounded-full border border-[#D4AF37]/30 bg-[#121212]/70 px-3 py-1.5 font-mono text-[10px] font-semibold tracking-[0.15em] text-[#D4AF37] backdrop-blur-md">GRAND CELEBRATION</span>
                            <div class="absolute inset-x-0 bottom-0 p-6"><span class="font-mono text-[10px] uppercase tracking-wider text-[#9CA3AF]">Guest Experience · Celebration</span><h3 class="mt-2 font-serif text-2xl text-[#F5F2EB]">A Room Full of Energy</h3><p class="mt-1 text-sm leading-6 text-[#F5F2EB]/80">A program built around connection, movement, and memorable atmosphere.</p></div>
                        </button>
                        <button type="button" class="gallery-trigger group relative min-h-[260px] overflow-hidden rounded-2xl border border-[#D4AF37]/20 bg-[#181818] text-left transition-all duration-300 hover:scale-[1.02] hover:border-[#D4AF37]/60 focus:outline-none focus:ring-2 focus:ring-[#D4AF37]" data-gallery-src="Assest/pic3.jpg" data-gallery-alt="Behind the scenes at a LEGATO event" data-gallery-title="The Work Before the Applause" data-gallery-caption="Experienced hands, thoughtful planning, and a team that stays focused on the details.">
                            <img src="Assest/pic3.jpg" alt="Behind the scenes at a LEGATO event" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#121212] via-[#121212]/20 to-transparent"></div>
                            <span class="absolute left-5 top-5 rounded-full border border-[#D4AF37]/30 bg-[#121212]/70 px-3 py-1.5 font-mono text-[10px] font-semibold tracking-[0.15em] text-[#D4AF37] backdrop-blur-md">BEHIND THE SCENES</span>
                            <div class="absolute inset-x-0 bottom-0 p-6"><span class="font-mono text-[10px] uppercase tracking-wider text-[#9CA3AF]">Crew Focus · Execution</span><h3 class="mt-2 font-serif text-2xl text-[#F5F2EB]">The Work Before the Applause</h3><p class="mt-1 text-sm leading-6 text-[#F5F2EB]/80">The production discipline that lets hosts and guests stay present.</p></div>
                        </button>
                    </div>
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
            <p class="footer-title">Get In Touch</p>
            <div class="footer-links">
                <span>Dumaguete City, Negros Oriental, Philippines 6200</span>
                <a href="mailto:info@legatoevents.com">info@legatoevents.com</a>
                <a href="tel:+639000000000">+63 917 123 4567</a>
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
    <div id="galleryLightbox" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 p-4 opacity-0 backdrop-blur-md transition-opacity duration-300" role="dialog" aria-modal="true" aria-labelledby="galleryLightboxTitle">
        <button type="button" id="galleryClose" class="absolute right-5 top-5 z-10 rounded-full border border-[#D4AF37]/40 bg-[#121212]/80 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-[#D4AF37] transition hover:bg-[#D4AF37] hover:text-[#121212]" aria-label="Close image preview">Close ×</button>
        <div class="relative max-h-full w-full max-w-6xl overflow-hidden rounded-2xl border border-[#D4AF37]/30 bg-[#181818] shadow-2xl">
            <img id="galleryLightboxImage" src="" alt="" class="max-h-[72vh] w-full bg-[#121212] object-contain">
            <div class="border-t border-[#282828] px-6 py-5 sm:px-8"><p id="galleryLightboxMeta" class="font-mono text-[10px] uppercase tracking-[0.18em] text-[#D4AF37]">LEGATO EVENT HIGHLIGHT</p><h2 id="galleryLightboxTitle" class="mt-2 font-serif text-3xl text-[#F5F2EB]"></h2><p id="galleryLightboxCaption" class="mt-2 text-sm leading-6 text-[#9CA3AF]"></p></div>
        </div>
    </div>
    <script>
        (() => {
            const lightbox = document.getElementById('galleryLightbox');
            const image = document.getElementById('galleryLightboxImage');
            const title = document.getElementById('galleryLightboxTitle');
            const caption = document.getElementById('galleryLightboxCaption');
            const closeButton = document.getElementById('galleryClose');
            let lastTrigger = null;
            const close = () => { lightbox.classList.remove('opacity-100'); setTimeout(() => lightbox.classList.add('hidden'), 300); lastTrigger?.focus(); };
            document.querySelectorAll('.gallery-trigger').forEach((trigger) => trigger.addEventListener('click', () => {
                lastTrigger = trigger; image.src = trigger.dataset.gallerySrc; image.alt = trigger.dataset.galleryAlt;
                title.textContent = trigger.dataset.galleryTitle; caption.textContent = trigger.dataset.galleryCaption;
                lightbox.classList.remove('hidden'); requestAnimationFrame(() => lightbox.classList.add('opacity-100')); closeButton.focus();
            }));
            closeButton.addEventListener('click', close);
            lightbox.addEventListener('click', (event) => { if (event.target === lightbox) close(); });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !lightbox.classList.contains('hidden')) close(); });
        })();
    </script>
</body>
</html>
