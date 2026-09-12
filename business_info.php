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
        <title>Policies &amp; Info | LEGATO Events &amp; Productions</title>
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

            .policies-main {
                background: #121212;
                color: #F5F2EB;
            }

            .policies-main .hero {
                position: relative;
                overflow: hidden;
            }

            .policies-main .hero-content {
                padding-bottom: 90px;
            }

            .policies-layout {
                display: grid;
                grid-template-columns: 220px minmax(0, 1fr);
                gap: 28px;
                align-items: start;
                padding-bottom: 100px;
            }

            .policies-tabs {
                position: sticky;
                top: 112px;
                display: grid;
                gap: 8px;
                padding: 10px;
                border: 1px solid rgba(212, 175, 55, 0.2);
                border-radius: 8px;
                background: #1A1A1A;
            }

            .policies-tab {
                padding: 14px 16px;
                border: 1px solid transparent;
                border-radius: 4px;
                background: transparent;
                color: rgba(245, 242, 235, 0.62);
                cursor: pointer;
                font-family: "Montserrat", sans-serif;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-align: left;
                text-transform: uppercase;
            }

            .policies-tab:hover,
            .policies-tab.active {
                border-color: rgba(212, 175, 55, 0.35);
                background: rgba(212, 175, 55, 0.12);
                color: #D4AF37;
            }

            .policy-card {
                display: none;
                padding: clamp(24px, 4vw, 52px);
                border: 1px solid rgba(212, 175, 55, 0.2);
                border-radius: 8px;
                background: #1A1A1A;
            }

            .policy-card.active {
                display: block;
            }

            .policy-card h2,
            .policy-card h3 {
                color: #F5F2EB;
                font-family: "Playfair Display", serif;
            }

            .policy-card h2 {
                margin: 10px 0 34px;
                font-size: clamp(30px, 4vw, 48px);
            }

            .policy-card h3 {
                margin: 0 0 10px;
                font-size: clamp(21px, 2.5vw, 29px);
            }

            .policy-card p,
            .policy-card li {
                color: rgba(245, 242, 235, 0.78);
                line-height: 1.85;
            }

            .policy-card ol {
                display: grid;
                gap: 30px;
                margin: 0;
                padding-left: 28px;
            }

            .policy-card ul {
                display: grid;
                gap: 9px;
                margin: 14px 0 0;
                padding-left: 24px;
            }

            .policy-card li::marker {
                color: #D4AF37;
                font-weight: 700;
            }

            .policy-card strong {
                color: #F5F2EB;
            }

            @media (max-width: 760px) {
                .policies-layout {
                    grid-template-columns: 1fr;
                }

                .policies-tabs {
                    position: static;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .policies-tab {
                    text-align: center;
                }
            }
        </style>
    </head>
    <body>
        <header class="navbar">
            <div class="container nav-content">
                <a href="index.php" class="brand">
                    <img class="custom-logo h-16 sm:h-20 lg:h-24 w-auto object-contain transition-transform duration-200 hover:scale-105" src="Assest/legato1.png" alt="LEGATO Events & Productions" />
                </a>
                <nav class="nav-links" id="navLinks">
                    <a href="index.php">Home</a>
                    <a href="about.php">About Us</a>
                    <a href="packages.php">VIP Packages</a>
                    <a href="custom.php">Custom Services</a>
                    <a class="active" href="business_info.php">Policies &amp; Info</a>
                </nav>
                <div class="nav-actions">
                    <a href="booking.php" class="btn btn-gold nav-book">Book An Event</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="profile.php" class="nav-login">My Account</a>
                        <a href="logout.php" class="nav-login">Log Out</a>
                    <?php else: ?>
                    <a href="login.php?redirect=booking.php&message=Please+log+in+or+create+an+account+to+finalize+your+event+booking." class="nav-login">Log In</a>
                <?php endif; ?>
                <button class="menu-button" id="menuButton" aria-label="Open menu">☰</button>
            </div>
        </div>
    </header>
    <main class="policies-main">
        <section class="hero" id="home">
            <div class="hero-pattern"></div>
            <div class="container hero-content">
                <span class="eyebrow-badge">LEGATO EVENTS &amp; PRODUCTIONS</span>
                <h1>Policies &amp; <em>Information.</em></h1>
                <p class="hero-description">Review the terms and privacy practices that guide LEGATO Events &amp; Productions.</p>
            </div>
        </section>

        <section class="policies-layout container" aria-label="Policies and information">
            <nav class="policies-tabs" aria-label="Policy sections">
                <button class="policies-tab active" type="button" data-policy-tab="terms-panel" aria-controls="terms-panel" aria-selected="true">Terms &amp; Conditions</button>
                <button class="policies-tab" type="button" data-policy-tab="privacy-panel" aria-controls="privacy-panel" aria-selected="false">Privacy Policy</button>
            </nav>

            <div>
                <article class="policy-card active" id="terms-panel" data-policy-panel role="tabpanel">
                    <p class="section-label">TERMS AND CONDITIONS</p>
                    <h2>Terms and Conditions</h2>
                    <ol>
                        <li>
                            <h3>Acceptance of Terms</h3>
                            <p>By booking, signing an agreement, or paying a deposit for services with LEGATO Events &amp; Productions ("Company"), the client ("Client") agrees to be bound by these Terms and Conditions.</p>
                        </li>
                        <li>
                            <h3>Booking, Deposit, and Payment Terms</h3>
                            <ul>
                                <li><strong>Down Payment:</strong> A non-refundable down payment of fifty percent (50%) of the total quoted price is required to secure the event date and booking.</li>
                                <li><strong>Balance Payment:</strong> The remaining fifty percent (50%) balance must be settled in full at least three (3) days prior to the scheduled event date, or as explicitly agreed upon in writing.</li>
                                <li><strong>Custom;:</strong> Custom package rates are calculated based on selected options and are subject to the same payment schedule.</li>
                            </ul>
                        </li>
                        <li>
                            <h3>Cancellation and Refund Policy</h3>
                            <ul>
                                <li><strong>Client Cancellation:</strong> If the Client cancels the booking:
                                    <ul>
                                        <li>More than 3 weeks prior to the event: The 50% down payment is fully refundable upon cancellation.</li>
                                        <li>3 weeks or less prior to the event: The 50% down payment is retained by the Company as liquidated damages to cover initial coordination, scheduling, and lost operational opportunities.</li>
                                    </ul>
                                </li>
                                <li><strong>Company Cancellation:</strong> If the Company cancels due to unforeseen internal constraints, a 100% refund of all payments made will be returned to the Client immediately.</li>
                            </ul>
                        </li>
                        <li>
                            <h3>Service Area &amp; Transport Surcharges</h3>
                            <p>Coverage radii are defined from Dumaguete City, Negros Oriental:</p>
                            <ul>
                                <li>VIP 1: Up to 15 km</li>
                                <li>VIP 2: Up to 25 km</li>
                                <li>VIP 3: Up to 50 km</li>
                                <li>Out-of-Coverage Area: Events taking place beyond 50 km from Dumaguete City are subject to additional transport, logistics, and crew allowance fees.</li>
                            </ul>
                        </li>
                        <li>
                            <h3>Client Responsibilities &amp; Venue Requirements</h3>
                            <ul>
                                <li><strong>Venue Access &amp; Power:</strong> The Client must ensure the event venue grants setup access to LEGATO staff at least 3–4 hours prior to event start. The venue must provide stable, safe, and sufficient power sources for all technical, sound, lighting, and multimedia equipment.</li>
                                <li><strong>Permits &amp; Fees:</strong> The Client is responsible for securing all venue permissions, local government permits, sound ordinances, or association fees required for the event.</li>
                            </ul>
                        </li>
                        <li>
                            <h3>Safety, Equipment Protection, and Liability</h3>
                            <ul>
                                <li><strong>Equipment Safety:</strong> The Client is responsible for ensuring a safe environment for LEGATO’s personnel and equipment. If hazardous conditions (e.g., severe weather without cover, physical fights, unsafe electrical wiring) threaten equipment or staff safety, LEGATO reserves the right to suspend operations until conditions are rectified.</li>
                                <li><strong>Damage &amp; Loss:</strong> The Client will be held liable for any repair or replacement costs arising from intentional damage, negligence, or loss of equipment caused by event guests or venue hazards.</li>
                            </ul>
                        </li>
                        <li>
                            <h3>Force Majeure</h3>
                            <p>Neither party shall be held liable for failure or delay in performance caused by acts of God, extreme natural disasters (typhoons, earthquakes), civil unrest, government restrictions, or other circumstances beyond reasonable control. In such cases, bookings may be rescheduled without penalty, subject to equipment availability.</p>
                        </li>
                    </ol>
                </article>

                <article class="policy-card" id="privacy-panel" data-policy-panel role="tabpanel" hidden>
                    <p class="section-label">PRIVACY POLICY</p>
                    <h2>Privacy Policy</h2>
                    <ol>
                        <li>
                            <h3>Compliance with Philippine Laws</h3>
                            <p>LEGATO Events &amp; Productions respects your privacy rights and is committed to protecting your personal information in accordance with Republic Act No. 10173, also known as the Data Privacy Act of 2012 (DPA) of the Philippines.</p>
                        </li>
                        <li>
                            <h3>Information We Collect</h3>
                            <p>We collect personal information necessary to fulfill event management and production requests, including:</p>
                            <ul>
                                <li>Full name and contact information (phone number, email address, mailing address).</li>
                                <li>Event details (dates, venue locations, guest counts, and special preferences).</li>
                                <li>Billing, payment, and transaction records.</li>
                                <li>Event photography and videography captured during the event execution (when included in your service package).</li>
                            </ul>
                        </li>
                        <li>
                            <h3>Purpose of Data Collection</h3>
                            <p>Collected information is used exclusively for:</p>
                            <ul>
                                <li>Processing bookings, contracts, and invoicing.</li>
                                <li>Event coordination, site inspections, and logistics management.</li>
                                <li>Communication regarding timeline approvals, vendor updates, and post-event feedback.</li>
                                <li>Marketing and promotional media (e.g., portfolio highlights, website, social media showcases), provided prior consent is given by the Client.</li>
                            </ul>
                        </li>
                        <li>
                            <h3>Data Protection and Security Measures</h3>
                            <p>We implement physical, organizational, and technical safeguards to protect your personal data from unauthorized access, disclosure, alteration, or accidental loss. Only authorized LEGATO administrative staff and event coordinators have access to your details.</p>
                        </li>
                        <li>
                            <h3>Data Sharing and Third Parties</h3>
                            <p>We do not sell, rent, or trade your personal data to third parties. Information is shared only with verified internal technicians, sub-contracted partners (e.g., specific photographers, venue coordinators), or legal authorities strictly when necessary to execute your event or comply with legal obligations.</p>
                        </li>
                        <li>
                            <h3>Your Data Subject Rights</h3>
                            <p>In accordance with the Data Privacy Act of 2012, you have the right to:</p>
                            <ul>
                                <li>Access, correct, or request the deletion of your personal data stored in our system.</li>
                                <li>Withdraw consent for the use of your event photos/videos for promotional purposes at any time.</li>
                            </ul>
                        </li>
                        <li>
                            <h3>Contact Information</h3>
                            <p>For any questions regarding this Privacy Policy or your personal data, please contact LEGATO Events &amp; Productions at our main office in Dumaguete City, Negros Oriental.</p>
                        </li>
                    </ol>
                </article>
            </div>
        </section>
    </main>

    <footer id="contact">
        <div class="container footer-grid">
            <div>
                <a href="index.php" class="brand">
                    <img class="footer-custom-logo h-12 sm:h-14 w-auto object-contain transition-transform duration-200 hover:scale-105" src="Assest/legato1.png" alt="LEGATO Events & Productions" />
                </a>
                <p class="footer-tagline">Where flawless production meets unforgettable celebration.</p>
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
                <span>© 2026 LEGATO Events &amp; Productions. All Rights Reserved.</span>
                <span>Dumaguete · Negros Oriental</span>
            </div>
        </div>
    </footer>
    <script src="script.js"></script>
    <script>
        document.querySelectorAll('[data-policy-tab]').forEach((tab) => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('[data-policy-tab]').forEach((item) => {
                    item.classList.toggle('active', item === tab);
                    item.setAttribute('aria-selected', item === tab ? 'true' : 'false');
                });

                document.querySelectorAll('[data-policy-panel]').forEach((panel) => {
                    const isActive = panel.id === tab.dataset.policyTab;
                    panel.classList.toggle('active', isActive);
                    panel.hidden = !isActive;
                });
            });
        });
    </script>
</body>
</html>
