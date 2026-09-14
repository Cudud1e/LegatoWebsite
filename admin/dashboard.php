<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../db.php';

function escaped(mixed $value): string { 
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8'); 
}

function money(mixed $value): string { 
    return '&#8369;' . number_format((float) ($value ?? 0), 2); 
}

function ensureDashboardSchema(PDO $pdo): void {
    $column = $pdo->query("SHOW COLUMNS FROM inquiries LIKE 'is_archived'");
    if ($column->fetch() === false) {
        $pdo->exec('ALTER TABLE inquiries ADD COLUMN is_archived TINYINT(1) NULL DEFAULT 0 AFTER status');
    }
    $column = $pdo->query("SHOW COLUMNS FROM inquiries LIKE 'confirmed_at'");
    if ($column->fetch() === false) {
        $pdo->exec('ALTER TABLE inquiries ADD COLUMN confirmed_at TIMESTAMP NULL DEFAULT NULL AFTER is_archived');
    }
}

$message = $_SESSION['admin_message'] ?? '';
unset($_SESSION['admin_message']);

$error = ''; 
$search = trim((string) ($_GET['q'] ?? ''));
$statuses = ['Pending Review', 'Pending Verification', 'In-Person Pending', 'Confirmed', 'Rejected', 'Completed', 'Cancelled'];
$inquiries = $tasks = $activity = $archivedInquiries = [];
$kpis = ['revenue' => 0.0, 'inquiries' => 0, 'upcoming' => 0, 'outstanding' => 0.0];

try {
    $pdo = getDatabaseConnection(); 
    ensureDashboardSchema($pdo);
    
    $where = ['(i.is_archived = 0 OR i.is_archived IS NULL)']; 
    $parameters = [];
    
    if ($search !== '') { 
        $where[] = "(i.reference_no LIKE :search OR i.name LIKE :search OR i.email LIKE :search OR i.phone LIKE :search OR i.target_event_date LIKE :search OR COALESCE(i.status, 'Pending Verification') LIKE :search)"; 
        $parameters['search'] = '%' . $search . '%'; 
    }

    $sql = "SELECT i.id, i.reference_no, i.name, i.email, i.phone, i.event_type, i.target_event_date, i.event_start_time, i.setup_access_time, i.venue, i.venue_type, i.guest_count, i.package_interest, i.budget_range, i.payment_method, i.payment_reference, i.receipt_path, i.downpayment_amount, i.remaining_balance, i.requested_services, i.special_requests, i.message, COALESCE(i.status, 'Pending Verification') AS status, COALESCE(i.is_archived, 0) AS is_archived, COALESCE(i.total_amount, i.estimated_total, i.estimated_cost, 0) AS total_amount, i.created_at FROM inquiries i WHERE " . implode(' AND ', $where) . ' ORDER BY i.created_at DESC';
    $statement = $pdo->prepare($sql); 
    $statement->execute($parameters); 
    $inquiries = $statement->fetchAll(PDO::FETCH_ASSOC);

    $kpis['inquiries'] = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE (is_archived = 0 OR is_archived IS NULL) AND COALESCE(status, 'Pending Verification') NOT IN ('Confirmed','Rejected','Completed','Cancelled')")->fetchColumn();
    $kpis['revenue'] = (float) $pdo->query("SELECT COALESCE(SUM(COALESCE(total_amount, estimated_total, estimated_cost, 0)), 0) FROM inquiries WHERE COALESCE(status, 'Pending Verification') IN ('Confirmed','Completed') AND YEAR(target_event_date) = YEAR(CURDATE())")->fetchColumn();
    $kpis['upcoming'] = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE COALESCE(status, 'Pending Verification') = 'Confirmed' AND target_event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
    
    try { 
        $kpis['outstanding'] = (float) $pdo->query("SELECT COALESCE(SUM(remaining_balance), 0) FROM invoices WHERE status NOT IN ('Paid','Paid in Full')")->fetchColumn(); 
    } catch (PDOException) {}

    try { 
        $tasks = $pdo->query("SELECT t.title, t.status, t.due_date, i.reference_no, a.full_name AS assignee FROM admin_tasks t JOIN inquiries i ON i.id=t.inquiry_id LEFT JOIN admin_users a ON a.id=t.assigned_admin_id WHERE t.status <> 'Completed' ORDER BY t.due_date IS NULL, t.due_date LIMIT 8")->fetchAll(PDO::FETCH_ASSOC); 
    } catch (PDOException) {}

    $activity = $pdo->query("SELECT reference_no, name, created_at, receipt_path, COALESCE(status, 'Pending Verification') AS status FROM inquiries ORDER BY created_at DESC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
    $archive = $pdo->query("SELECT id, reference_no, name, target_event_date, package_interest, COALESCE(status, 'Pending Verification') AS status, COALESCE(total_amount, estimated_total, estimated_cost, 0) AS total_amount FROM inquiries WHERE is_archived=1 OR COALESCE(status, 'Pending Verification') IN ('Confirmed','Rejected','Completed','Cancelled') ORDER BY created_at DESC"); 
    $archivedInquiries = $archive->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) { 
    error_log('LEGATO admin dashboard: ' . $exception->getMessage()); 
    $error = 'Dashboard data is temporarily unavailable. Check the database migration and try again.'; 
}

$closed = ['Confirmed', 'Rejected', 'Completed', 'Cancelled'];
$activeInquiries = array_values(array_filter($inquiries, static fn(array $row): bool => !in_array((string) ($row['status'] ?? ''), $closed, true)));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | LEGATO</title>
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
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 7px; height: 7px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #121212; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #D4AF37; border-radius: 99px; }
        .custom-scrollbar { scrollbar-color: #D4AF37 #121212; scrollbar-width: thin; }
    </style>
</head>
<body class="min-h-screen bg-[#121212] font-sans text-[#F5F2EB]">
    <header class="border-b border-[#282828] bg-[#181818]">
        <div class="mx-auto flex max-w-[1600px] flex-col gap-5 px-5 py-5 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <div class="flex items-center gap-4">
                <a href="dashboard.php">
                    <img src="../Assest/legato1.png" class="h-12 w-auto object-contain" alt="LEGATO Operations">
                </a>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[.25em] text-[#D4AF37]">Operations Console</p>
                    <h1 class="font-serif text-2xl">Executive Dashboard</h1>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="get" class="flex gap-2">
                    <input class="w-52 rounded border border-[#282828] bg-[#121212] px-3 py-2 text-xs outline-none transition-all duration-200 focus:border-[#D4AF37]" name="q" value="<?php echo escaped($search); ?>" placeholder="Reference, client, email">
                    <button class="rounded border border-[#D4AF37] px-3 py-2 text-xs font-semibold text-[#D4AF37] transition-all duration-200 hover:bg-[#D4AF37] hover:text-[#121212]">Search</button>
                </form>
                <a class="rounded border border-[#282828] px-3 py-2 text-xs text-[#9CA3AF] hover:border-[#D4AF37]" href="confirmed-customers.php">Confirmed Customers</a>
                <a class="rounded border border-[#282828] px-3 py-2 text-xs text-[#9CA3AF] hover:border-[#D4AF37]" href="settings.php">Settings</a>
                <a class="rounded bg-[#D4AF37] px-3 py-2 text-xs font-bold text-[#121212]" href="logout.php">Log out</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-[1600px] px-5 py-7 lg:px-8">
        <?php if ($message !== ''): ?>
            <div class="mb-5 border border-[#D4AF37]/30 bg-[#181818] px-4 py-3 text-xs text-[#D4AF37]"><?php echo escaped($message); ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="mb-5 border border-[#282828] bg-[#181818] px-4 py-3 text-xs text-[#9CA3AF]"><?php echo escaped($error); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <section class="space-y-6 lg:col-span-2">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <?php foreach ([['Revenue YTD', money($kpis['revenue'])], ['Active inquiries', (string)$kpis['inquiries']], ['Next 7 days', (string)$kpis['upcoming']], ['Outstanding', money($kpis['outstanding'])]] as [$label, $value]): ?>
                        <article class="border border-[#D4AF37]/20 bg-[#181818] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[.18em] text-[#6B7280]"><?php echo escaped($label); ?></p>
                            <strong class="mt-3 block font-serif text-3xl"><?php echo $value; ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>

                <section id="inquiries" class="border border-[#282828] bg-[#181818]">
                    <div class="flex flex-col gap-4 border-b border-[#282828] bg-[#121212] px-5 py-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[.2em] text-[#D4AF37]">Inquiry queue</p>
                            <h2 class="font-serif text-2xl">Bookings requiring action</h2>
                        </div>
                        <div class="flex gap-2">
                            <button class="inquiry-tab rounded border border-[#D4AF37] px-3 py-2 text-[11px] font-semibold text-[#D4AF37]" data-target="active-table" type="button">Active (<?php echo count($activeInquiries); ?>)</button>
                            <button class="inquiry-tab rounded border border-[#282828] px-3 py-2 text-[11px] text-[#9CA3AF]" data-target="archive-table" type="button">Archived (<?php echo count($archivedInquiries); ?>)</button>
                        </div>
                    </div>

                    <div id="active-table" class="custom-scrollbar overflow-x-auto">
                        <table class="w-full min-w-[900px] text-left text-xs">
                            <thead class="bg-[#121212] text-[10px] uppercase tracking-[.16em] text-[#6B7280]">
                                <tr>
                                    <th class="px-5 py-4">Reference / Client</th>
                                    <th class="px-4 py-4">Event</th>
                                    <th class="px-4 py-4">Payment</th>
                                    <th class="px-4 py-4">Investment</th>
                                    <th class="px-4 py-4">Status</th>
                                    <th class="px-5 py-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#282828]">
                                <?php foreach ($activeInquiries as $row): 
                                    $status = (string)($row['status'] ?? 'Pending Verification');
                                    $receipt = trim((string)($row['receipt_path'] ?? '')); 
                                ?>
                                    <tr class="transition-all duration-200 hover:bg-[#202020]">
                                        <td class="px-5 py-4">
                                            <span class="font-mono text-[10px] text-[#D4AF37]">#<?php echo escaped($row['reference_no'] ?? 'N/A'); ?></span>
                                            <strong class="mt-1 block"><?php echo escaped($row['name'] ?? 'N/A'); ?></strong>
                                            <span class="text-[#9CA3AF]"><?php echo escaped($row['email'] ?? 'N/A'); ?></span>
                                        </td>
                                        <td class="px-4 py-4 text-[#9CA3AF]">
                                            <?php echo escaped($row['target_event_date'] ?? 'N/A'); ?>
                                            <span class="mt-1 block text-[10px]"><?php echo escaped($row['package_interest'] ?? 'N/A'); ?></span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <?php if ($receipt !== ''): ?>
                                                <a href="../<?php echo escaped($receipt); ?>" target="_blank" rel="noopener noreferrer" class="text-[#D4AF37] underline">Receipt / <?php echo escaped($row['payment_reference'] ?? 'View'); ?></a>
                                            <?php else: ?>
                                                <span class="rounded border border-[#282828] px-2 py-1 text-[10px] text-[#6B7280]">No receipt</span>
                                            <?php endif; ?>
                                            <span class="mt-1 block text-[10px] text-[#9CA3AF]"><?php echo escaped($row['payment_method'] ?? 'N/A'); ?></span>
                                        </td>
                                        <td class="px-4 py-4 font-mono">
                                            <?php echo money($row['total_amount'] ?? 0); ?>
                                            <span class="mt-1 block text-[10px] text-[#9CA3AF]">Due <?php echo money($row['remaining_balance'] ?? 0); ?></span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="rounded-full border border-[#D4AF37]/50 px-2 py-1 text-[10px] text-[#D4AF37]"><?php echo escaped($status); ?></span>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <button class="review-button rounded border border-[#282828] px-3 py-2 text-[10px] font-semibold uppercase tracking-wider transition-all duration-200 hover:border-[#D4AF37] hover:text-[#D4AF37]" data-dialog="inquiry-<?php echo (int)($row['id'] ?? 0); ?>" type="button">Review</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$activeInquiries): ?>
                                    <tr>
                                        <td class="px-5 py-10 text-center text-[#6B7280]" colspan="6">No active inquiries match this view.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="archive-table" class="custom-scrollbar hidden overflow-x-auto">
                        <table class="w-full min-w-[700px] text-left text-xs">
                            <thead class="bg-[#121212] text-[10px] uppercase tracking-[.16em] text-[#6B7280]">
                                <tr>
                                    <th class="px-5 py-4">Reference / Client</th>
                                    <th class="px-4 py-4">Date</th>
                                    <th class="px-4 py-4">Investment</th>
                                    <th class="px-5 py-4">Final status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#282828]">
                                <?php foreach ($archivedInquiries as $row): ?>
                                    <tr class="hover:bg-[#202020]">
                                        <td class="px-5 py-4">
                                            <span class="font-mono text-[#D4AF37]">#<?php echo escaped($row['reference_no'] ?? 'N/A'); ?></span>
                                            <strong class="mt-1 block"><?php echo escaped($row['name'] ?? 'N/A'); ?></strong>
                                        </td>
                                        <td class="px-4 py-4 text-[#9CA3AF]"><?php echo escaped($row['target_event_date'] ?? 'N/A'); ?></td>
                                        <td class="px-4 py-4 font-mono"><?php echo money($row['total_amount'] ?? 0); ?></td>
                                        <td class="px-5 py-4 text-[#9CA3AF]"><?php echo escaped($row['status'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$archivedInquiries): ?>
                                    <tr>
                                        <td class="px-5 py-10 text-center text-[#6B7280]" colspan="4">No archived records.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </section>

            <aside class="space-y-6">
                <section class="border border-[#282828] bg-[#181818] p-5">
                    <p class="text-[10px] font-bold uppercase tracking-[.2em] text-[#D4AF37]">Recent activity</p>
                    <div class="custom-scrollbar mt-4 max-h-72 space-y-1 overflow-y-auto">
                        <?php foreach ($activity as $item): ?>
                            <div class="px-3 py-2.5 text-[11px] transition-all duration-200 hover:bg-[#222]">
                                <span class="font-mono text-[#6B7280]"><?php echo escaped($item['created_at'] ?? ''); ?></span>
                                <strong class="mt-1 block">#<?php echo escaped($item['reference_no'] ?? 'N/A'); ?> · <?php echo escaped($item['name'] ?? 'N/A'); ?></strong>
                                <span class="text-[#9CA3AF]"><?php echo !empty($item['receipt_path']) ? 'Payment receipt uploaded' : escaped($item['status'] ?? 'New submission'); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$activity): ?>
                            <p class="px-3 py-4 text-xs text-[#6B7280]">No recent activity.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="border border-[#282828] bg-[#181818] p-5">
                    <p class="text-[10px] font-bold uppercase tracking-[.2em] text-[#D4AF37]">Quick actions</p>
                    <div class="mt-4 grid gap-2">
                        <a class="rounded border border-[#282828] px-3 py-3 text-xs transition-all duration-200 hover:border-[#D4AF37] hover:text-[#D4AF37]" href="../booking.php">Add manual booking</a>
                        <a class="rounded border border-[#282828] px-3 py-3 text-xs transition-all duration-200 hover:border-[#D4AF37] hover:text-[#D4AF37]" href="../admin_inquiries.php">Invoices &amp; payments</a>
                        <a class="rounded border border-[#282828] px-3 py-3 text-xs transition-all duration-200 hover:border-[#D4AF37] hover:text-[#D4AF37]" href="dashboard.php">Refresh report</a>
                    </div>
                </section>

                <section class="border border-[#282828] bg-[#181818] p-5">
                    <p class="text-[10px] font-bold uppercase tracking-[.2em] text-[#D4AF37]">Open production tasks</p>
                    <div class="mt-4 space-y-3">
                        <?php foreach ($tasks as $task): ?>
                            <div class="border-l-2 border-[#D4AF37] pl-3 text-xs">
                                <strong class="block"><?php echo escaped($task['title'] ?? 'Task'); ?></strong>
                                <span class="text-[#9CA3AF]"><?php echo escaped($task['reference_no'] ?? 'N/A'); ?> · <?php echo escaped($task['assignee'] ?? 'Unassigned'); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$tasks): ?>
                            <p class="text-xs text-[#6B7280]">No open production tasks.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </aside>
        </div>
    </main>

    <?php foreach ($activeInquiries as $row): 
        $receipt = trim((string)($row['receipt_path'] ?? '')); 
    ?>
        <dialog id="inquiry-<?php echo (int)($row['id'] ?? 0); ?>" class="w-[min(720px,calc(100%-2rem))] border border-[#D4AF37]/50 bg-[#181818] p-0 text-[#F5F2EB] backdrop:bg-black/80">
            <div class="border-b border-[#282828] bg-[#121212] px-6 py-5">
                <p class="font-mono text-[10px] text-[#D4AF37]">#<?php echo escaped($row['reference_no'] ?? 'N/A'); ?></p>
                <h2 class="mt-1 font-serif text-2xl"><?php echo escaped($row['name'] ?? 'N/A'); ?></h2>
            </div>
            <div class="grid gap-5 p-6 md:grid-cols-2">
                <div class="space-y-3 text-sm">
                    <p><span class="text-[#6B7280]">Email</span><br><?php echo escaped($row['email'] ?? 'N/A'); ?></p>
                    <p><span class="text-[#6B7280]">Phone</span><br><?php echo escaped($row['phone'] ?? 'N/A'); ?></p>
                    <p><span class="text-[#6B7280]">Event</span><br><?php echo escaped($row['event_type'] ?? 'N/A'); ?> · <?php echo escaped($row['target_event_date'] ?? 'N/A'); ?></p>
                    <p><span class="text-[#6B7280]">Notes</span><br><?php echo nl2br(escaped(($row['special_requests'] ?? '') ?: ($row['message'] ?? 'N/A'))); ?></p>
                </div>
                <div class="border border-[#282828] bg-[#121212] p-4">
                    <p class="text-[10px] font-bold uppercase tracking-[.16em] text-[#D4AF37]">Payment proof</p>
                    <?php if ($receipt !== ''): ?>
                        <a class="mt-3 inline-block text-sm text-[#D4AF37] underline" target="_blank" rel="noopener noreferrer" href="../<?php echo escaped($receipt); ?>">Open uploaded receipt</a>
                    <?php else: ?>
                        <p class="mt-3 text-sm text-[#6B7280]">No receipt uploaded.</p>
                    <?php endif; ?>
                    <p class="mt-3 font-mono text-xs text-[#9CA3AF]"><?php echo escaped($row['payment_reference'] ?? 'No transaction number'); ?></p>
                </div>
            </div>
            <form method="post" action="update_inquiry.php" class="flex flex-col gap-3 border-t border-[#282828] p-6 md:flex-row">
                <input type="hidden" name="csrf_token" value="<?php echo escaped(csrfToken()); ?>">
                <input type="hidden" name="inquiry_id" value="<?php echo (int)($row['id'] ?? 0); ?>">
                <input class="min-w-0 flex-1 rounded border border-[#282828] bg-[#121212] px-3 py-2 text-sm outline-none focus:border-[#D4AF37]" name="total_amount" type="number" min="0" step="0.01" value="<?php echo number_format((float)($row['total_amount'] ?? 0), 2, '.', ''); ?>">
                <select class="rounded border border-[#282828] bg-[#121212] px-3 py-2 text-sm outline-none focus:border-[#D4AF37]" name="status">
                    <?php foreach ($statuses as $status): ?>
                        <option<?php echo $status === ($row['status'] ?? '') ? ' selected' : ''; ?>><?php echo escaped($status); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="rounded bg-[#D4AF37] px-4 py-2 text-xs font-bold uppercase tracking-wider text-[#121212]" type="submit">Save</button>
                <button class="dialog-close rounded border border-[#282828] px-4 py-2 text-xs" type="button">Close</button>
            </form>
        </dialog>
    <?php endforeach; ?>

    <dialog id="customerReviewModal" class="w-[min(900px,calc(100%-2rem))] border border-[#D4AF37]/50 bg-[#181818] p-0 text-[#F5F2EB] backdrop:bg-black/80">
        <div class="flex items-start justify-between border-b border-[#282828] bg-[#121212] px-6 py-5">
            <div>
                <p id="reviewReference" class="font-mono text-[10px] uppercase tracking-[.18em] text-[#D4AF37]"></p>
                <h2 id="reviewName" class="mt-1 font-serif text-3xl"></h2>
            </div>
            <button type="button" class="review-close rounded border border-[#282828] px-3 py-2 text-xs text-[#9CA3AF] hover:border-[#D4AF37] hover:text-[#D4AF37]">Close</button>
        </div>
        <div id="reviewContent" class="custom-scrollbar max-h-[65vh] overflow-y-auto p-6"></div>
        <footer class="flex flex-col gap-3 border-t border-[#282828] bg-[#121212] p-5 sm:flex-row sm:justify-end">
            <button type="button" data-status="Rejected" class="review-status rounded border border-red-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-red-300 transition hover:border-red-400">Decline / Reject</button>
            <button type="button" data-status="Confirmed" class="review-status rounded bg-[#D4AF37] px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-[#121212] transition hover:opacity-90">Confirm Booking</button>
            <button type="button" class="review-close rounded border border-[#282828] px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-[#9CA3AF]">Close</button>
        </footer>
    </dialog>

    <script>
        const inquiryData = <?php echo json_encode($activeInquiries, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>;
        const reviewModal = document.getElementById('customerReviewModal');
        const reviewContent = document.getElementById('reviewContent');
        let currentInquiry = null;
        let currentTrigger = null;

        const safe = (value) => {
            const e = document.createElement('span');
            e.textContent = value ?? 'N/A';
            return e.innerHTML;
        };

        const services = (value) => {
            try {
                const parsed = JSON.parse(value || '[]');
                return Array.isArray(parsed) && parsed.length ? parsed.map(safe).join(', ') : safe(value || 'N/A');
            } catch {
                return safe(value || 'N/A');
            }
        };

        const field = (label, value) => `<div class="border-b border-[#282828] pb-3"><dt class="font-mono text-[10px] uppercase tracking-wider text-[#6B7280]">${label}</dt><dd class="mt-1 text-sm leading-6 text-[#F5F2EB]">${value ? safe(value) : 'N/A'}</dd></div>`;

        document.querySelectorAll('.inquiry-tab').forEach(b => b.addEventListener('click', () => {
            const a = b.dataset.target === 'archive-table';
            document.getElementById('active-table').classList.toggle('hidden', a);
            document.getElementById('archive-table').classList.toggle('hidden', !a);
            document.querySelectorAll('.inquiry-tab').forEach(t => t.classList.toggle('border-[#D4AF37]', t === b));
        }));

        document.querySelectorAll('.review-button').forEach(button => button.addEventListener('click', () => {
            currentTrigger = button;
            const id = Number(button.dataset.dialog.replace('inquiry-', ''));
            currentInquiry = inquiryData.find(row => Number(row.id) === id);
            if (!currentInquiry) return;

            document.getElementById('reviewReference').textContent = '#' + (currentInquiry.reference_no || 'N/A');
            document.getElementById('reviewName').textContent = currentInquiry.name || 'N/A';
            reviewContent.innerHTML = `<div class="grid gap-7 md:grid-cols-2"><section><p class="font-mono text-[10px] font-bold uppercase tracking-[.18em] text-[#D4AF37]">Personal information</p><dl class="mt-4 space-y-3">${field('Full name', currentInquiry.name)}${field('Email', currentInquiry.email)}${field('Phone number', currentInquiry.phone)}${field('Organization / Company', currentInquiry.organization || currentInquiry.company || 'Not provided')}</dl></section><section><p class="font-mono text-[10px] font-bold uppercase tracking-[.18em] text-[#D4AF37]">Event details</p><dl class="mt-4 space-y-3">${field('Event type', currentInquiry.event_type)}${field('Event date', currentInquiry.target_event_date)}${field('Event time', currentInquiry.event_start_time)}${field('Venue / location', currentInquiry.venue)}${field('Venue type', currentInquiry.venue_type)}${field('Guests', currentInquiry.guest_count)}</dl></section><section><p class="font-mono text-[10px] font-bold uppercase tracking-[.18em] text-[#D4AF37]">Service &amp; technical specifications</p><dl class="mt-4 space-y-3">${field('Package selected', currentInquiry.package_interest)}${field('Production requirements', services(currentInquiry.requested_services))}${field('Budget range', currentInquiry.budget_range)}${field('Special requests / notes', currentInquiry.special_requests || currentInquiry.message)}${field('Estimated total', '₱' + Number(currentInquiry.total_amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 }))}</dl></section><section><p class="font-mono text-[10px] font-bold uppercase tracking-[.18em] text-[#D4AF37]">Submission metadata</p><dl class="mt-4 space-y-3">${field('Submitted', currentInquiry.created_at)}${field('Current status', currentInquiry.status || 'Pending Verification')}${field('Payment method', currentInquiry.payment_method)}${field('Transaction number', currentInquiry.payment_reference)}${currentInquiry.receipt_path ? `<div><a class="inline-block rounded border border-[#D4AF37]/50 px-3 py-2 text-xs text-[#D4AF37]" href="../${safe(currentInquiry.receipt_path)}" target="_blank" rel="noopener noreferrer">Open uploaded receipt</a></div>` : '<p class="text-xs text-[#6B7280]">No receipt uploaded.</p>'}</dl></section></div>`;
            reviewModal.showModal();
        }));

        document.querySelectorAll('.review-close').forEach(button => button.addEventListener('click', () => reviewModal.close()));

        document.querySelectorAll('.review-status').forEach(button => button.addEventListener('click', async () => {
            if (!currentInquiry) return;
            button.disabled = true;

            const data = new FormData();
            data.append('csrf_token', '<?php echo escaped(csrfToken()); ?>');
            data.append('inquiry_id', currentInquiry.id);
            data.append('total_amount', currentInquiry.total_amount || 0);
            data.append('status', button.dataset.status);
            data.append('ajax', '1');

            try {
                const response = await fetch('update_inquiry.php', {
                    method: 'POST',
                    body: data,
                    headers: { Accept: 'application/json' }
                });
                const result = await response.json();
                if (!response.ok || !result.ok) throw new Error(result.message || 'Update failed');

                currentTrigger?.closest('tr')?.remove();
                reviewModal.close();
                currentInquiry = null;
                currentTrigger = null;
            } catch (error) {
                alert(error.message || 'Unable to update this booking.');
                button.disabled = false;
            }
        }));
    </script>
</body>
</html>