<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../db.php';

function h(mixed $value): string { 
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8'); 
}

function peso(mixed $value): string { 
    return '&#8369;' . number_format((float) ($value ?? 0), 2); 
}

function ensureConfirmedCustomerSchema(PDO $pdo): void {
    if ($pdo->query("SHOW COLUMNS FROM inquiries LIKE 'confirmed_at'")->fetch() === false) {
        $pdo->exec('ALTER TABLE inquiries ADD COLUMN confirmed_at TIMESTAMP NULL DEFAULT NULL');
    }
    $pdo->exec('CREATE TABLE IF NOT EXISTS inquiry_contact_logs (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, inquiry_id INT UNSIGNED NOT NULL, note TEXT NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, CONSTRAINT fk_contact_log_inquiry FOREIGN KEY (inquiry_id) REFERENCES inquiries(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
}

$search = trim((string) ($_GET['q'] ?? ''));
$period = $_GET['period'] ?? 'upcoming';
if (!in_array($period, ['upcoming', 'past', 'all'], true)) {
    $period = 'upcoming';
}

$customers = []; 
$error = '';

try {
    $pdo = getDatabaseConnection(); 
    ensureConfirmedCustomerSchema($pdo);

    $conditions = ["LOWER(COALESCE(i.status, '')) = 'confirmed'"];
    $parameters = [];

    if ($search !== '') { 
        $conditions[] = '(i.name LIKE :search OR i.email LIKE :search OR i.target_event_date LIKE :search)'; 
        $parameters['search'] = '%' . $search . '%'; 
    }
    if ($period === 'upcoming') $conditions[] = 'i.target_event_date >= CURDATE()';
    if ($period === 'past') $conditions[] = 'i.target_event_date < CURDATE()';

    $sql = "SELECT i.id, i.reference_no, i.name, i.email, i.phone, i.event_type, i.target_event_date, i.event_start_time, i.venue, i.venue_type, i.guest_count, i.package_interest, i.budget_range, i.requested_services, i.special_requests, i.message, i.payment_method, i.payment_reference, i.receipt_path, i.downpayment_amount, i.remaining_balance, i.deposit_status, i.confirmed_at, i.created_at, COALESCE(i.total_amount, i.estimated_total, i.estimated_cost, 0) AS total_amount, a.full_name AS assigned_crew FROM inquiries i LEFT JOIN admin_users a ON a.id = i.assigned_admin_id WHERE " . implode(' AND ', $conditions) . ' ORDER BY i.target_event_date ASC';
    
    $statement = $pdo->prepare($sql); 
    $statement->execute($parameters); 
    $customers = $statement->fetchAll(PDO::FETCH_ASSOC);

    $logStatement = $pdo->prepare('SELECT inquiry_id, note, created_at FROM inquiry_contact_logs WHERE inquiry_id = ? ORDER BY created_at DESC');
    $taskStatement = $pdo->prepare("SELECT title, status, due_date FROM admin_tasks WHERE inquiry_id = ? ORDER BY due_date IS NULL, due_date ASC");

    foreach ($customers as &$customer) { 
        $logStatement->execute([$customer['id']]); 
        $customer['contact_logs'] = $logStatement->fetchAll(PDO::FETCH_ASSOC); 
        $taskStatement->execute([$customer['id']]); 
        $customer['tasks'] = $taskStatement->fetchAll(PDO::FETCH_ASSOC); 
    }
    unset($customer);
} catch (PDOException $exception) { 
    error_log('LEGATO confirmed customers: ' . $exception->getMessage()); 
    $error = 'Confirmed-customer data is temporarily unavailable.'; 
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmed Customers | LEGATO Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        obsidian: '#121212',
                        charcoal: '#181818',
                        line: '#282828',
                        gold: '#D4AF37',
                        ivory: '#F5F2EB'
                    },
                    fontFamily: {
                        serif: ['Playfair Display', 'serif'],
                        sans: ['Montserrat', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-[#121212] font-sans text-[#F5F2EB]">
    <header class="border-b border-[#282828] bg-[#181818]">
        <div class="mx-auto flex max-w-[1400px] flex-col gap-4 px-5 py-5 md:flex-row md:items-center md:justify-between">
            <a class="flex items-center gap-3" href="dashboard.php">
                <img src="../Assest/legato1.png" class="h-12 w-auto" alt="LEGATO">
                <span>
                    <span class="block font-mono text-[10px] uppercase tracking-[.2em] text-[#D4AF37]">Operations</span>
                    <strong class="font-serif text-xl">Confirmed Customers</strong>
                </span>
            </a>
            <nav class="flex flex-wrap gap-2 text-xs">
                <a class="rounded border border-[#282828] px-3 py-2 text-[#9CA3AF] hover:border-[#D4AF37] hover:text-[#D4AF37]" href="dashboard.php">Dashboard</a>
                <a class="rounded border border-[#D4AF37] px-3 py-2 text-[#D4AF37]" href="confirmed-customers.php">Confirmed Customers</a>
                <a class="rounded border border-[#282828] px-3 py-2 text-[#9CA3AF] hover:border-[#D4AF37] hover:text-[#D4AF37]" href="../admin_inquiries.php">Invoices</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-[1400px] px-5 py-8">
        <div class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <p class="font-mono text-[10px] font-bold uppercase tracking-[.22em] text-[#D4AF37]">Event delivery pipeline</p>
                <h1 class="mt-2 font-serif text-4xl">Confirmed <em>Customers.</em></h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-[#9CA3AF]">Confirmed event commitments, payment progress, production requirements, assigned crew, and contact history.</p>
            </div>
            <form method="get" class="flex flex-wrap gap-2">
                <input class="rounded border border-[#282828] bg-[#181818] px-3 py-2 text-xs outline-none focus:border-[#D4AF37]" name="q" value="<?php echo h($search); ?>" placeholder="Name, email, or event date">
                <select class="rounded border border-[#282828] bg-[#181818] px-3 py-2 text-xs outline-none focus:border-[#D4AF37]" name="period">
                    <option value="upcoming"<?php echo $period==='upcoming'?' selected':''; ?>>Upcoming events</option>
                    <option value="past"<?php echo $period==='past'?' selected':''; ?>>Past events</option>
                    <option value="all"<?php echo $period==='all'?' selected':''; ?>>All confirmed</option>
                </select>
                <button class="rounded bg-[#D4AF37] px-4 py-2 text-xs font-bold text-[#121212]">Filter</button>
            </form>
        </div>

        <?php if($error): ?>
            <p class="mt-6 border border-red-900 bg-[#181818] p-4 text-sm text-red-300"><?php echo h($error); ?></p>
        <?php endif; ?>

        <section class="mt-8 overflow-hidden rounded-2xl border border-[#282828] bg-[#181818]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left text-xs">
                    <thead class="bg-[#121212] font-mono text-[10px] uppercase tracking-[.14em] text-[#6B7280]">
                        <tr>
                            <th class="px-5 py-4">Customer</th>
                            <th class="px-4 py-4">Event</th>
                            <th class="px-4 py-4">Contact</th>
                            <th class="px-4 py-4">Package / Price</th>
                            <th class="px-4 py-4">Confirmed</th>
                            <th class="px-5 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#282828]">
                        <?php foreach($customers as $customer): ?>
                            <tr class="confirmed-row transition hover:bg-[#202020]">
                                <td class="px-5 py-4">
                                    <span class="font-mono text-[10px] text-[#D4AF37]">#<?php echo h($customer['reference_no']); ?></span>
                                    <strong class="mt-1 block text-sm"><?php echo h($customer['name']); ?></strong>
                                </td>
                                <td class="px-4 py-4 text-[#9CA3AF]">
                                    <?php echo h($customer['event_type']); ?>
                                    <span class="mt-1 block"><?php echo h($customer['target_event_date']); ?> · <?php echo h($customer['event_start_time']); ?></span>
                                </td>
                                <td class="px-4 py-4 text-[#9CA3AF]">
                                    <?php echo h($customer['email']); ?>
                                    <span class="mt-1 block"><?php echo h($customer['phone']); ?></span>
                                </td>
                                <td class="px-4 py-4">
                                    <strong class="block text-[#F5F2EB]"><?php echo h($customer['package_interest']); ?></strong>
                                    <span class="font-mono text-[#D4AF37]"><?php echo peso($customer['total_amount']); ?></span>
                                </td>
                                <td class="px-4 py-4 text-[#9CA3AF]">
                                    <?php echo h($customer['confirmed_at'] ?: $customer['created_at']); ?>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button" data-id="<?php echo (int)$customer['id']; ?>" class="details-button rounded border border-[#D4AF37]/50 px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-[#D4AF37] transition hover:bg-[#D4AF37] hover:text-[#121212]">
                                        View Full Details
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(!$customers): ?>
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-sm text-[#6B7280]">
                                    No confirmed customers match this filter.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <dialog id="customerDrawer" class="ml-auto h-screen w-[min(760px,100vw)] max-w-none border-l border-[#D4AF37]/40 bg-[#181818] p-0 text-[#F5F2EB] backdrop:bg-black/75">
        <div class="flex h-full flex-col">
            <header class="flex items-start justify-between border-b border-[#282828] bg-[#121212] px-6 py-5">
                <div>
                    <p id="drawerReference" class="font-mono text-[10px] uppercase tracking-[.16em] text-[#D4AF37]"></p>
                    <h2 id="drawerName" class="mt-1 font-serif text-3xl"></h2>
                </div>
                <button type="button" class="drawer-close rounded border border-[#282828] px-3 py-2 text-xs text-[#9CA3AF]">Close</button>
            </header>
            <div id="drawerContent" class="flex-1 overflow-y-auto p-6"></div>
            <footer class="flex justify-end gap-3 border-t border-[#282828] bg-[#121212] p-5">
                <button type="button" class="drawer-close rounded border border-[#282828] px-4 py-2 text-xs text-[#9CA3AF]">Close</button>
                <button id="completeEvent" type="button" class="rounded bg-[#D4AF37] px-4 py-2 text-xs font-bold uppercase tracking-wider text-[#121212]">Mark as Completed</button>
            </footer>
        </div>
    </dialog>

    <script>
        const customers = <?php echo json_encode($customers, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_THROW_ON_ERROR); ?>;
        const drawer = document.getElementById('customerDrawer');
        const content = document.getElementById('drawerContent');
        let selected = null;

        const esc = v => {
            const s = document.createElement('span');
            s.textContent = v ?? 'N/A';
            return s.innerHTML;
        };

        const list = v => {
            try {
                const a = JSON.parse(v || '[]');
                return Array.isArray(a) && a.length ? a.map(esc).join(', ') : esc(v || 'N/A');
            } catch {
                return esc(v || 'N/A');
            }
        };

        const row = (label, value) => `<div class="border-b border-[#282828] py-3"><dt class="font-mono text-[10px] uppercase tracking-wider text-[#6B7280]">${label}</dt><dd class="mt-1 text-sm leading-6">${value || 'N/A'}</dd></div>`;

        document.querySelectorAll('.details-button').forEach(button => button.addEventListener('click', () => {
            selected = customers.find(c => Number(c.id) === Number(button.dataset.id));
            if (!selected) return;

            document.getElementById('drawerReference').textContent = '#' + selected.reference_no;
            document.getElementById('drawerName').textContent = selected.name;

            const logs = (selected.contact_logs || []).map(log => `<li class="border-l-2 border-[#D4AF37] pl-3 text-xs text-[#9CA3AF]"><strong class="block text-[#F5F2EB]">${esc(log.note)}</strong>${esc(log.created_at)}</li>`).join('') || '<li class="text-xs text-[#6B7280]">No contact logs recorded.</li>';
            const tasks = (selected.tasks || []).map(task => `<li class="border-l-2 border-[#282828] pl-3 text-xs"><strong>${esc(task.title)}</strong><span class="block text-[#9CA3AF]">${esc(task.status)} · ${esc(task.due_date)}</span></li>`).join('') || '<li class="text-xs text-[#6B7280]">No linked production tasks.</li>';

            content.innerHTML = `<div class="grid gap-7 md:grid-cols-2"><section><p class="font-mono text-[10px] uppercase tracking-[.18em] text-[#D4AF37]">Customer & event</p><dl class="mt-3">${row('Email',esc(selected.email))}${row('Phone',esc(selected.phone))}${row('Event type',esc(selected.event_type))}${row('Date & time',esc(selected.target_event_date)+' · '+esc(selected.event_start_time))}${row('Venue',esc(selected.venue)+' · '+esc(selected.venue_type))}${row('Guests',esc(selected.guest_count))}</dl></section><section><p class="font-mono text-[10px] uppercase tracking-[.18em] text-[#D4AF37]">Production & payment</p><dl class="mt-3">${row('Package',esc(selected.package_interest))}${row('Technical requirements',list(selected.requested_services))}${row('Budget range',esc(selected.budget_range))}${row('Special notes',esc(selected.special_requests||selected.message))}${row('Total package','₱'+Number(selected.total_amount||0).toLocaleString('en-PH',{minimumFractionDigits:2}))}${row('Deposit status',esc(selected.deposit_status))}${row('Remaining balance','₱'+Number(selected.remaining_balance||0).toLocaleString('en-PH',{minimumFractionDigits:2}))}${row('Assigned crew',esc(selected.assigned_crew||'Unassigned'))}</dl></section><section><p class="font-mono text-[10px] uppercase tracking-[.18em] text-[#D4AF37]">Booking history</p><dl class="mt-3">${row('Submitted',esc(selected.created_at))}${row('Confirmed',esc(selected.confirmed_at||selected.created_at))}${row('Payment method',esc(selected.payment_method))}${row('Transaction number',esc(selected.payment_reference))}</dl></section><section><p class="font-mono text-[10px] uppercase tracking-[.18em] text-[#D4AF37]">Contact logs & tasks</p><ul class="mt-3 space-y-3">${logs}</ul><p class="mt-6 font-mono text-[10px] uppercase tracking-[.16em] text-[#D4AF37]">Assigned production tasks</p><ul class="mt-3 space-y-3">${tasks}</ul></section></div>`;
            drawer.showModal();
        }));

        document.querySelectorAll('.drawer-close').forEach(b => b.addEventListener('click', () => drawer.close()));

        document.getElementById('completeEvent').addEventListener('click', async () => {
            if (!selected) return;
            const form = new FormData();
            form.append('csrf_token', '<?php echo h(csrfToken()); ?>');
            form.append('inquiry_id', selected.id);
            form.append('total_amount', selected.total_amount || 0);
            form.append('status', 'Completed');
            form.append('ajax', '1');

            const response = await fetch('update_inquiry.php', {
                method: 'POST',
                body: form,
                headers: { Accept: 'application/json' }
            });

            const result = await response.json();
            if (!response.ok || !result.ok) {
                alert(result.message || 'Unable to complete event.');
                return;
            }

            document.querySelector(`.details-button[data-id="${selected.id}"]`)?.closest('tr')?.remove();
            drawer.close();
        });
    </script>
</body>
</html>