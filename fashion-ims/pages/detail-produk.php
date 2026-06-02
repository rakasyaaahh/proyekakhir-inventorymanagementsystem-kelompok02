<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /fashion-ims/login.php'); exit; }
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('/pages/produk.php');

$query = $pdo->prepare("
    SELECT p.*, s.name AS nama_supplier, s.email AS email_supplier, s.phone AS telp_supplier
    FROM produk p LEFT JOIN supplier s ON s.id = p.supplier_id WHERE p.id = ?
");
$query->execute([$id]);
$p = $query->fetch();

if (!$p) { setFlash('error', 'Produk tidak ditemukan.'); redirect('/pages/produk.php'); }

$pageTitle    = 'Detail Produk';
$pageSubtitle = $p['name'];

$margin    = $p['price_sell'] - $p['price_buy'];
$marginPct = $p['price_buy'] > 0 ? round(($margin / $p['price_buy']) * 100) : 0;

if ($p['stock'] == 0)                   { $kelasStok = 'bg-danger-light text-danger';   $labelStok = 'Stok Habis'; }
elseif ($p['stock'] <= $p['stock_min']) { $kelasStok = 'bg-warning-light text-warning'; $labelStok = 'Stok Rendah'; }
else                                    { $kelasStok = 'bg-success-light text-success'; $labelStok = 'Stok Aman'; }

$pctBar = $p['stock_min'] > 0 ? min(100, round(($p['stock'] / ($p['stock_min'] * 3)) * 100)) : 100;
$barColor = $p['stock'] == 0 ? '#9e2a2b' : ($p['stock'] <= $p['stock_min'] ? '#d97706' : '#16a34a');

require_once __DIR__ . '/../components/head.php';
require_once __DIR__ . '/../components/sidebar.php';
?>

<div class="lg:ml-[240px] ml-0 flex-1 flex flex-col min-w-0 transition-all duration-200">
<?php require_once __DIR__ . '/../components/topbar.php'; ?>
<main class="p-6 flex-1">

<div class="flex items-start justify-between mb-5 flex-wrap gap-3">
    <div>
        <div class="flex items-center gap-1.5 text-[0.75rem] text-muted mb-1">
            <a href="<?= BASE_URL ?>/pages/produk.php" class="hover:text-primary transition-colors">Produk</a>
            <span class="text-[0.65rem]">›</span><span>Detail</span>
        </div>
        <h1 class="font-display text-[1.35rem] font-extrabold text-textmain leading-tight"><?= e($p['name']) ?></h1>
    </div>
    <div class="flex gap-2">
        <a href="<?= BASE_URL ?>/pages/ubah-produk.php?id=<?= $p['id'] ?>"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all">
            <i data-lucide="pencil" style="width:15px;height:15px;"></i> Edit
        </a>
        <?php if (($_SESSION['user_role'] ?? 'admin') === 'admin'): ?>
        <button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-danger-light text-danger border border-danger/20 hover:bg-danger hover:text-white transition-all cursor-pointer"
                data-hapus-url="<?= BASE_URL ?>/actions/hapus-produk.php?id=<?= $p['id'] ?>"
                data-hapus-nama="<?= e($p['name']) ?>">
            <i data-lucide="trash-2" style="width:15px;height:15px;"></i> Hapus
        </button>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/pages/produk.php"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
            <i data-lucide="arrow-left" style="width:15px;height:15px;"></i>
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-5 items-start">

    <div class="flex flex-col gap-4">

        <div class="bg-white border border-border rounded-lg p-4 shadow-card">
            <?php if ($p['image'] && file_exists(UPLOAD_PATH . $p['image'])): ?>
                <img src="<?= UPLOAD_URL . e($p['image']) ?>" alt="<?= e($p['name']) ?>"
                     class="w-full aspect-square object-cover rounded-lg border border-slate-200">
            <?php else: ?>
                <div class="w-full aspect-square bg-primary-light rounded-lg flex items-center justify-center border border-border">
                    <i data-lucide="shirt" style="width:56px;height:56px;" class="text-primary opacity-30"></i>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white border border-border rounded-lg p-4 shadow-card">
            <p class="font-display text-[0.875rem] font-bold text-textmain mb-3 flex items-center gap-2">
                <i data-lucide="package" style="width:17px;height:17px;" class="text-primary"></i> Status Stok
            </p>
            <div class="flex items-end justify-between mb-2.5">
                <div>
                    <p class="text-[0.72rem] text-muted">Stok Sekarang</p>
                    <p class="font-display text-4xl font-extrabold text-textmain leading-none mt-0.5">
                        <?= $p['stock'] ?><span class="text-[0.875rem] font-normal text-muted ml-1">unit</span>
                    </p>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[0.72rem] font-semibold <?= $kelasStok ?>"><?= $labelStok ?></span>
            </div>
            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden mb-1">
                <div class="h-full rounded-full transition-all duration-500" style="width:<?= $pctBar ?>%;background:<?= $barColor ?>;"></div>
            </div>
            <p class="text-[0.7rem] text-muted">Minimum: <?= $p['stock_min'] ?> unit</p>
        </div>

    </div>

    <div class="flex flex-col gap-4">

        <div class="bg-white border border-border rounded-lg p-5 shadow-card">
            <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                <i data-lucide="file-text" style="width:17px;height:17px;" class="text-primary"></i> Informasi Produk
            </p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[0.72rem] text-muted uppercase tracking-wide mb-1">Harga Beli</p>
                    <p class="text-base font-semibold text-textmain"><?= rupiah($p['price_buy']) ?></p>
                </div>
                <div>
                    <p class="text-[0.72rem] text-muted uppercase tracking-wide mb-1">Harga Jual</p>
                    <p class="text-base font-bold text-secondary"><?= rupiah($p['price_sell']) ?></p>
                </div>
                <div>
                    <p class="text-[0.72rem] text-muted uppercase tracking-wide mb-1">Margin Keuntungan</p>
                    <p class="text-[0.9rem] font-semibold text-success">
                        <?= rupiah($margin) ?> <span class="text-[0.78rem] text-muted font-normal">(<?= $marginPct ?>%)</span>
                    </p>
                </div>
                <div>
                    <p class="text-[0.72rem] text-muted uppercase tracking-wide mb-1">Status</p>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.72rem] font-semibold <?= $p['status'] === 'active' ? 'bg-success-light text-success' : 'bg-slate-100 text-slate-500' ?>">
                        <i data-lucide="<?= $p['status'] === 'active' ? 'check' : 'x' ?>" style="width:12px;height:12px;"></i>
                        <?= $p['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                </div>
                <div>
                    <p class="text-[0.72rem] text-muted uppercase tracking-wide mb-1">Lokasi Rak Gudang</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-primary-light text-primary font-mono"><?= e($p['bin_location'] ?: '—') ?></span>
                </div>
            </div>

            <?php if (!empty(trim($p['size'] ?? ''))): ?>
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-[0.72rem] text-muted uppercase tracking-wide mb-2">Ukuran Tersedia</p>
                <?php foreach (explode(',', $p['size']) as $u) echo '<span class="inline-block px-2 py-0.5 rounded text-[0.68rem] font-semibold bg-surface border border-border text-muted m-px">'.e(trim($u)).'</span>'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty(trim($p['color'] ?? ''))): ?>
            <div class="mt-3">
                <p class="text-[0.72rem] text-muted uppercase tracking-wide mb-2">Warna Tersedia</p>
                <?php foreach (explode(',', $p['color']) as $w) echo '<span class="inline-block m-px px-2.5 py-0.5 bg-accent/20 rounded-full text-[0.72rem] font-medium text-primary-dark">'.e(trim($w)).'</span>'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($p['description'])): ?>
            <div class="mt-3 pt-4 border-t border-slate-100">
                <p class="text-[0.72rem] text-muted uppercase tracking-wide mb-2">Deskripsi</p>
                <p class="text-[0.875rem] text-muted leading-relaxed"><?= nl2br(e($p['description'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($p['nama_supplier']): ?>
        <div class="bg-white border border-border rounded-lg p-5 shadow-card">
            <p class="font-display text-[0.875rem] font-bold text-textmain mb-3 flex items-center gap-2">
                <i data-lucide="truck" style="width:17px;height:17px;" class="text-primary"></i> Supplier
            </p>
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 bg-primary-light rounded-xl flex items-center justify-center shrink-0 text-primary">
                    <i data-lucide="building-2" style="width:20px;height:20px;"></i>
                </div>
                <div>
                    <p class="font-semibold text-textmain"><?= e($p['nama_supplier']) ?></p>
                    <?php if ($p['email_supplier']): ?>
                    <p class="text-[0.78rem] text-muted flex items-center gap-1.5 mt-0.5">
                        <i data-lucide="mail" style="width:13px;height:13px;"></i> <?= e($p['email_supplier']) ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($p['telp_supplier']): ?>
                    <p class="text-[0.78rem] text-muted flex items-center gap-1.5 mt-0.5">
                        <i data-lucide="phone" style="width:13px;height:13px;"></i> <?= e($p['telp_supplier']) ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white border border-border rounded-lg p-4 shadow-card">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[0.72rem] text-muted mb-1">Ditambahkan</p>
                    <p class="text-[0.85rem] text-textmain"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></p>
                </div>
                <div>
                    <p class="text-[0.72rem] text-muted mb-1">Terakhir Diubah</p>
                    <p class="text-[0.85rem] text-textmain"><?= date('d M Y H:i', strtotime($p['updated_at'])) ?></p>
                </div>
            </div>
        </div>

    </div>
</div>

</main>
<?php require_once __DIR__ . '/../components/footer.php'; ?>
