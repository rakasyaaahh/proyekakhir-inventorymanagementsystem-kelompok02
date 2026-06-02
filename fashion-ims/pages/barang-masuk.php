<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /fashion-ims/login.php'); exit; }
require_once __DIR__ . '/../config/database.php';

$pageTitle    = 'Barang Masuk';
$pageSubtitle = 'Logistik Masuk & Penerimaan Stok dari Supplier';

$inboundLogs = $pdo->query("
    SELECT io.*, s.name AS nama_supplier,
           COUNT(ii.id) AS total_varian,
           IFNULL(SUM(ii.qty), 0) AS total_qty,
           IFNULL(SUM(ii.qty * ii.price_buy), 0) AS total_nilai
    FROM barang_masuk io
    LEFT JOIN supplier s ON s.id = io.supplier_id
    LEFT JOIN detail_barang_masuk ii ON ii.barang_masuk_id = io.id
    GROUP BY io.id
    ORDER BY io.received_date DESC, io.id DESC
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
            <span class="text-[0.65rem]">›</span><span>Barang Masuk</span>
        </div>
        <h1 class="font-display text-[1.35rem] font-extrabold text-textmain leading-tight">Alur Barang Masuk</h1>
        <p class="text-[0.78rem] text-muted mt-0.5"><?= count($inboundLogs) ?> pengiriman tercatat dari Supplier</p>
    </div>
    <a href="<?= BASE_URL ?>/pages/tambah-barang-masuk.php"
       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all">
        <i data-lucide="plus" style="width:15px;height:15px;"></i> Penerimaan Stok Baru
    </a>
</div>

<div class="bg-white border border-border rounded-lg shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 pl-5 pr-3 text-left border-b border-border">Nomor Terima</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Supplier</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Tanggal Diterima</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Diterima Oleh</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Total Varian</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Total Qty</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Total Nilai (HPP)</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($inboundLogs)): ?>
                <tr><td colspan="8" class="py-12 text-center text-muted">
                    <i data-lucide="arrow-down-left" style="width:48px;height:48px;opacity:0.3;margin:0 auto 0.75rem;display:block;"></i>
                    <p>Belum ada riwayat penerimaan barang masuk.</p>
                    <a href="<?= BASE_URL ?>/pages/tambah-barang-masuk.php" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-primary text-white hover:bg-primary-dark transition-all mt-3">+ Catat Barang Masuk</a>
                </td></tr>
                <?php else: ?>
                <?php foreach ($inboundLogs as $log): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 pl-5 pr-3 border-b border-slate-100 align-middle">
                        <span class="font-mono font-bold text-primary"><?= e($log['receive_no']) ?></span>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="truck" style="width:14px;height:14px;" class="text-muted"></i>
                            <p class="font-medium text-textmain"><?= e($log['nama_supplier'] ?: 'Tanpa Supplier') ?></p>
                        </div>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.855rem] text-textmain"><?= date('d M Y', strtotime($log['received_date'])) ?></td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-primary-light text-primary"><?= e($log['received_by']) ?></span>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.855rem] text-textmain"><?= $log['total_varian'] ?> variasi</td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle font-semibold text-textmain"><?= number_format($log['total_qty']) ?> pcs</td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle font-bold text-secondary"><?= rupiah($log['total_nilai']) ?></td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.78rem] text-muted max-w-[180px] truncate" title="<?= e($log['notes']) ?>">
                        <?= e($log['notes'] ?: '—') ?>
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
