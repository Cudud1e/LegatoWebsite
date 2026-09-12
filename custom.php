<?php
$services = [
    'Audio System' => ['Basic' => 8000, 'Standard' => 15000, 'Premium' => 25000],
    'Professional EMCEE / Host' => ['Basic' => 5000, 'Standard' => 8000, 'Premium' => 12000],
    'Stage & Décor Support' => ['Basic' => 3000, 'Standard' => 7000, 'Premium' => 12000],
    'Visual & Multimedia Support' => ['Basic' => 2000, 'Standard' => 5000, 'Premium' => 8000],
    'Photography & Videography' => ['Basic' => 10000, 'Standard' => 15000, 'Premium' => 20000],
    'Full Event Management' => ['Basic' => 8000, 'Standard' => 12000, 'Premium' => 15000],
];
function escaped(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Custom Services | LEGATO Events & Productions</title>
        <script src="https://cdn.tailwindcss.com">
        </script>
        <script>tailwind.config={theme:{extend:{colors:{obsidian:'#121212',charcoal:'#181818',gold:'#D4AF37',ivory:'#F5F2EB'},fontFamily:{serif:['Playfair Display','serif'],sans:['Montserrat','sans-serif']}}}}</script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
        <style>
            #contact { background: #121212; border-top: 1px solid #282828; color: #F5F2EB; padding: 0 1.5rem; }
            #contact .footer-grid { display: flex; max-width: 1200px; margin: 0 auto; padding: 40px 0; gap: 30px; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; }
            #contact .footer-grid > div { flex: 1 1 220px; }
            #contact .footer-custom-logo { display: block; height: 3.5rem; width: auto; object-fit: contain; }
            #contact .footer-tagline { max-width: 280px; color: rgba(245, 242, 235, .55); font-size: 13px; line-height: 1.8; }
            #contact .footer-title { margin: 0 0 12px; color: #D4AF37; font-size: .85rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; }
            #contact .footer-links { display: flex; flex-direction: column; gap: 8px; color: rgba(245, 242, 235, .6); font-size: 13px; }
            #contact .footer-links a:hover { color: #D4AF37; }
            #contact .footer-bottom { max-width: 1200px; margin: 8px auto 0; border-top: 1px solid #262626; }
            #contact .footer-bottom .container { display: flex; justify-content: center; gap: 20px; padding: 24px 0 0; color: rgba(245, 242, 235, .35); font-size: 10px; letter-spacing: .1em; text-align: center; }
        </style>
    </head>
    <body class="min-h-screen bg-[#121212] font-sans text-[#F5F2EB]">
        <header class="border-b border-[#282828] bg-[#181818]">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6">
                <a href="index.php" class="transition-transform duration-200 hover:scale-105">
                    <img src="Assest/legato1.png" alt="LEGATO Events & Productions" class="h-16 sm:h-20 lg:h-24 w-auto object-contain transition-transform duration-200 hover:scale-105">
                </a>
                <nav class="hidden items-center gap-6 text-xs uppercase tracking-widest text-gray-400 md:flex">
                    <a class="hover:text-[#D4AF37]" href="index.php">Home</a>
                    <a class="hover:text-[#D4AF37]" href="packages.php">VIP Packages</a>
                    <a class="text-[#D4AF37]" href="custom.php">Custom Services</a>
                    <a class="hover:text-[#D4AF37]" href="business_info.php">Policies &amp; Info</a>
                </nav>
                <a href="contact.php?package=Custom%20Build" class="rounded bg-[#D4AF37] px-4 py-2 text-xs font-bold uppercase tracking-wider text-black transition hover:bg-[#b8952d]">Book An Event</a>
            </div>
        </header>
        <main>
            <section class="mx-auto max-w-7xl px-6 pb-12 pt-20">
                <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#D4AF37]">TAILORED TO YOUR OCCASION</p>
                <h1 class="mt-4 font-serif text-5xl font-bold leading-tight md:text-7xl">Build your <em class="text-[#D4AF37]">own package.</em>
            </h1>
            <p class="mt-5 max-w-2xl text-sm leading-7 text-gray-400">Choose the services and tiers that fit your production vision.</p>
        </section>
        <section class="mx-auto max-w-7xl px-6 pb-20">
            <div class="grid gap-8 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <div class="mb-6">
                        <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#D4AF37]">SELECT SERVICES</p>
                        <h2 class="mt-3 font-serif text-3xl font-bold">Designed around your event.</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <?php foreach ($services as $service => $tiers): ?>
                            <article class="service-card rounded-lg border border-[#282828] bg-[#181818] p-5 transition-all hover:border-[#D4AF37]" data-service-card>
                                <div class="flex items-start justify-between gap-4">
                                    <label class="flex min-w-0 cursor-pointer items-start gap-3">
                                        <input type="checkbox" class="service-enabled mt-1 h-4 w-4 accent-[#D4AF37]" data-service="<?php echo escaped($service); ?>">
                                        <span class="font-serif text-lg leading-snug">
                                            <?php echo escaped($service); ?>
                                        </span>
                                    </label>
                                    <span class="service-badge shrink-0 text-right text-[10px] uppercase leading-4 tracking-wider text-gray-500">Not selected</span>
                                </div>
                                <label class="mt-5 block text-[10px] font-semibold uppercase tracking-wider text-gray-500">Select tier<select class="service-tier mt-2 w-full cursor-pointer rounded border border-[#333] bg-[#121212] px-3 py-2.5 text-sm text-gray-200 outline-none transition focus:border-[#D4AF37] disabled:cursor-not-allowed disabled:opacity-40" disabled>
                                    <?php foreach ($tiers as $tier => $price): ?>
                                        <option value="<?php echo escaped($tier); ?>" data-price="<?php echo $price; ?>">
                                        <?php echo escaped($tier); ?> (₱<?php echo number_format($price); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <aside class="sticky top-8 h-fit rounded-lg border border-[#D4AF37]/40 bg-[#181818] p-6 shadow-xl">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D4AF37]">YOUR PRELIMINARY INVESTMENT</p>
                <div class="mt-7 flex items-end justify-between gap-4">
                    <span class="text-sm text-gray-400">Estimated Cost</span>
                    <strong id="estimatedCost" class="font-serif text-4xl font-bold text-[#D4AF37]">₱0</strong>
                </div>
                <div id="selectionSummary" class="my-7 space-y-3 text-xs text-gray-400">Select at least one service.</div>
                <p class="border-t border-[#282828] pt-5 text-xs leading-6 text-gray-500">Your final proposal is confirmed after venue, duration, and technical requirements review.</p>
                <a id="reserveCustom" href="#" aria-disabled="true" class="mt-6 block w-full cursor-not-allowed rounded bg-[#D4AF37] px-4 py-3 text-center text-sm font-semibold uppercase tracking-wider text-black opacity-40 transition hover:bg-[#b8952d]">Reserve Custom Booking</a>
            </aside>
        </div>
    </section>
</main>
<footer id="contact">
    <div class="container footer-grid">
        <div>
            <a href="index.php" class="brand">
                <img class="footer-custom-logo h-12 sm:h-14 w-auto object-contain transition-transform duration-200 hover:scale-105" src="Assest/legato1.png" alt="LEGATO Events &amp; Productions" />
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
            <span>&copy; 2026 LEGATO Events &amp; Productions. All Rights Reserved.</span>
            <span>Dumaguete &middot; Negros Oriental</span>
        </div>
    </div>
</footer>
<script>
const cards=[...document.querySelectorAll('[data-service-card]')], estimate=document.getElementById('estimatedCost'), summary=document.getElementById('selectionSummary'), reserve=document.getElementById('reserveCustom');
function updateBuilder(){const chosen=cards.filter(card=>card.querySelector('.service-enabled').checked), selections={}; let total=0; chosen.forEach(card=>{const input=card.querySelector('.service-enabled'), select=card.querySelector('.service-tier'), option=select.selectedOptions[0], price=Number(option.dataset.price); selections[input.dataset.service]=select.value; total+=price; card.classList.add('border-[#D4AF37]','bg-[#D4AF37]/10','shadow-[0_0_15px_rgba(212,175,55,0.15)]'); card.querySelector('.service-badge').textContent=`${select.value} (₱${price.toLocaleString('en-PH')})`; card.querySelector('.service-badge').classList.add('text-[#D4AF37]');}); cards.filter(card=>!card.querySelector('.service-enabled').checked).forEach(card=>{card.classList.remove('border-[#D4AF37]','bg-[#D4AF37]/10','shadow-[0_0_15px_rgba(212,175,55,0.15)]');card.querySelector('.service-badge').textContent='Not selected';card.querySelector('.service-badge').classList.remove('text-[#D4AF37]');}); estimate.textContent=`₱${total.toLocaleString('en-PH')}`; summary.innerHTML=chosen.length?chosen.map(card=>`<div class="flex justify-between gap-4 border-b border-[#282828] pb-2">
    <span>${card.querySelector('.service-enabled').dataset.service}</span>
    <strong class="text-[#F5F2EB]">${card.querySelector('.service-tier').value}</strong>
</div>`).join(''):'Select at least one service.'; reserve.classList.toggle('opacity-40',!total);reserve.classList.toggle('cursor-not-allowed',!total);reserve.setAttribute('aria-disabled',total?'false':'true');reserve.href=total?`contact.php?package=Custom%20Build&services=${encodeURIComponent(JSON.stringify(selections))}&total=${total}`:'#';}
cards.forEach(card=>{const check=card.querySelector('.service-enabled'),select=card.querySelector('.service-tier');check.addEventListener('change',()=>{select.disabled=!check.checked;updateBuilder();});select.addEventListener('change',updateBuilder);});reserve.addEventListener('click',event=>{if(reserve.getAttribute('aria-disabled')==='true')event.preventDefault();});
updateBuilder();
</script>
</body>
</html>
