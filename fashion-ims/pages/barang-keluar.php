<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /fashion-ims/login.php'); exit; }
require_once __DIR__ . '/../config/database.php';

$pageTitle    = 'Barang Keluar';
$pageSubtitle = 'Manajemen Nota Keluar, Pengiriman, & Quality Control (QC)';

$outboundOrders = $pdo->query("
    SELECT oo.*,
    COUNT(oi.id) AS total_varian,
    IFNULL(SUM(oi.qty), 0) AS total_qty
    FROM barang_keluar oo
    LEFT JOIN detail_barang_keluar oi ON oi.barang_keluar_id = oo.id
    GROUP BY oo.id
    ORDER BY oo.created_at DESC, oo.id DESC
")->fetchAll();

require_once __DIR__ . '/../components/head.php';
require_once __DIR__ . '/../components/sidebar.php';
?>

<div class="lg:ml-[240px] ml-0 flex-1 flex flex-col min-w-0 transition-all duration-200">
<?php require_once __DIR__ . '/../components/topbar.php'; ?>
<main class="p-6 flex-1">

<div class="flex items-start justify-between mb-5 flex-wrap gap-3">
    <div>
        <div class="flex items-center gap-1.5 text-[0.75rem] text-muted mb-1">
            <a href="<?= BASE_URL ?>/pages/dasbor.php" class="hover:text-primary transition-colors">Dashboard</a>
            <span class="text-[0.65rem]">›</span><span>Barang Keluar</span>
        </div>
        <h1 class="font-display text-[1.35rem] font-extrabold text-textmain leading-tight">Alur Barang Keluar (Nota)</h1>
        <p class="text-[0.78rem] text-muted mt-0.5"><?= count($outboundOrders) ?> nota barang keluar terdaftar</p>
    </div>
    <a href="<?= BASE_URL ?>/pages/tambah-barang-keluar.php"
       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all">
        <i data-lucide="plus" style="width:15px;height:15px;"></i> Buat Nota Baru
    </a>
</div>

<div class="bg-white border border-border rounded-lg shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 pl-5 pr-3 text-left border-b border-border">Nomor Nota / Invoice</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Nama Pelanggan</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Tanggal Dibuat</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Status</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Varian Produk</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Kuantitas</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Total Harga</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 pr-5 pl-3 text-right border-b border-border">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($outboundOrders)): ?>
                <tr><td colspan="8" class="py-12 text-center text-muted">
                    <i data-lucide="arrow-up-right" style="width:48px;height:48px;opacity:0.3;margin:0 auto 0.75rem;display:block;"></i>
                    <p>Belum ada riwayat nota barang keluar.</p>
                    <a href="<?= BASE_URL ?>/pages/tambah-barang-keluar.php" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-primary text-white hover:bg-primary-dark transition-all mt-3">+ Buat Nota Penjualan</a>
                </td></tr>
                <?php else: ?>
                <?php foreach ($outboundOrders as $order): ?>
                <?php
                    if ($order['status'] === 'pending')         { $badgeCls = 'bg-warning-light text-warning';  $statusLabel = 'Pending QC'; }
                    elseif ($order['status'] === 'qc_passed')   { $badgeCls = 'bg-primary-light text-primary';  $statusLabel = 'QC Pass'; }
                    elseif ($order['status'] === 'shipped')     { $badgeCls = 'bg-success-light text-success';  $statusLabel = 'Dikirim'; }
                    else                                         { $badgeCls = 'bg-danger-light text-danger';    $statusLabel = 'Batal'; }
                ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 pl-5 pr-3 border-b border-slate-100 align-middle">
                        <span class="font-mono font-bold text-danger"><?= e($order['invoice_no']) ?></span>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle font-semibold text-textmain"><?= e($order['customer_name']) ?></td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.855rem] text-textmain"><?= date('d M Y H:i', strtotime($order['created_at'])) ?></td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold <?= $badgeCls ?>"><?= $statusLabel ?></span>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.855rem] text-textmain"><?= $order['total_varian'] ?> variasi</td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.855rem] text-textmain"><?= number_format($order['total_qty']) ?> pcs</td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle font-bold text-primary"><?= rupiah($order['total_price']) ?></td>
                    <td class="py-3 pr-5 pl-3 border-b border-slate-100 align-middle text-right">
                        <a href="<?= BASE_URL ?>/pages/detail-barang-keluar.php?id=<?= $order['id'] ?>"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[0.78rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
                            <i data-lucide="eye" style="width:13px;height:13px;"></i> Detail & QC
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
<?php require_once __DIR__ . '/../components/footer.php'; ?>
