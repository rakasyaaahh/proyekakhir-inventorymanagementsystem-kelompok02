<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /fashion-ims/login.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';

$pageTitle    = 'Dashboard';
$pageSubtitle = 'Selamat datang di FashionIMS';

$totalProduk    = $pdo->query("SELECT COUNT(*) FROM produk WHERE status = 'active'")->fetchColumn();
$stokRendah     = $pdo->query("SELECT COUNT(*) FROM produk WHERE stock <= stock_min AND status = 'active'")->fetchColumn();
$nilaiInventori = $pdo->query("SELECT IFNULL(SUM(price_sell * stock), 0) FROM produk WHERE status = 'active'")->fetchColumn();

$produkTerbaru = $pdo->query("
    SELECT p.name, p.sku, p.stock, p.stock_min, p.price_sell, p.image
    FROM produk p
    ORDER BY p.created_at DESC
    LIMIT 8
")->fetchAll();

$listStokRendah = $pdo->query("
    SELECT p.id, p.name, p.stock, p.stock_min
    FROM produk p
    WHERE p.stock <= p.stock_min AND p.status = 'active'
    ORDER BY p.stock ASC
    LIMIT 6
")->fetchAll();

require_once __DIR__ . '/../components/head.php';
require_once __DIR__ . '/../components/sidebar.php';
?>

<div class="lg:ml-[240px] ml-0 flex-1 flex flex-col min-w-0 transition-all duration-200">
<?php require_once __DIR__ . '/../components/topbar.php'; ?>
<main class="p-6 flex-1">

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">

    <div class="bg-white border border-border border-l-4 border-l-primary rounded-lg p-5 shadow-card fade-in hover:-translate-y-0.5 transition-transform duration-200">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-[0.78rem] text-muted">Total Produk</p>
                <p class="font-display text-3xl font-extrabold text-textmain leading-none mt-1"><?= number_format($totalProduk) ?></p>
            </div>
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-primary-light text-primary">
                <i data-lucide="package" style="width:20px;height:20px;"></i>
            </div>
        </div>
        <p class="text-[0.72rem] text-muted pt-2.5 border-t border-border">Produk aktif tersedia</p>
    </div>

    <div class="bg-white border border-border border-l-4 <?= $stokRendah > 0 ? 'border-l-danger' : 'border-l-success' ?> rounded-lg p-5 shadow-card fade-in hover:-translate-y-0.5 transition-transform duration-200">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-[0.78rem] text-muted">Stok Rendah</p>
                <p class="font-display text-3xl font-extrabold leading-none mt-1 <?= $stokRendah > 0 ? 'text-danger' : 'text-success' ?>">
                    <?= $stokRendah ?>
                </p>
            </div>
            <div class="w-10 h-10 rounded-lg flex items-center justify-center <?= $stokRendah > 0 ? 'bg-danger-light text-danger' : 'bg-success-light text-success' ?>">
                <i data-lucide="<?= $stokRendah > 0 ? 'alert-triangle' : 'check-circle' ?>" style="width:20px;height:20px;"></i>
            </div>
        </div>
        <p class="text-[0.72rem] text-muted pt-2.5 border-t border-border"><?= $stokRendah > 0 ? 'Perlu segera direstok' : 'Semua stok aman' ?></p>
    </div>

    <div class="bg-white border border-border border-l-4 border-l-success rounded-lg p-5 shadow-card fade-in hover:-translate-y-0.5 transition-transform duration-200">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-[0.78rem] text-muted">Nilai Inventori</p>
                <p class="font-display text-xl font-extrabold text-textmain leading-none mt-1"><?= rupiah($nilaiInventori) ?></p>
            </div>
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-success-light text-success">
                <i data-lucide="trending-up" style="width:20px;height:20px;"></i>
            </div>
        </div>
        <p class="text-[0.72rem] text-muted pt-2.5 border-t border-border">Estimasi total nilai jual</p>
    </div>

</div>

<div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-5 items-start">

    <div class="bg-white border border-border rounded-lg shadow-card">
        <div class="px-5 py-4 border-b border-border flex items-center justify-between">
            <div>
                <p class="font-display text-[0.875rem] font-bold text-textmain">Produk Terbaru</p>
                <p class="text-[0.72rem] text-muted mt-0.5">8 produk terakhir ditambahkan</p>
            </div>
            <a href="<?= BASE_URL ?>/pages/produk.php"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[0.78rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
                Lihat Semua
                <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 pl-5 pr-4 text-left border-b border-border whitespace-nowrap">Produk</th>
                        <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-4 text-left border-b border-border whitespace-nowrap">Harga Jual</th>
                        <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-4 text-left border-b border-border whitespace-nowrap">Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produkTerbaru as $p): ?>
                    <?php
                        if ($p['stock'] == 0)                   { $kelasStok = 'bg-danger-light text-danger';   $labelStok = 'Habis'; }
                        elseif ($p['stock'] <= $p['stock_min']) { $kelasStok = 'bg-warning-light text-warning'; $labelStok = 'Rendah'; }
                        else                                    { $kelasStok = 'bg-success-light text-success'; $labelStok = $p['stock'].' unit'; }
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3.5 pl-5 pr-4 border-b border-slate-100 align-middle text-[0.855rem] text-textmain">
                            <div class="flex items-center gap-2.5">
                                <?php if ($p['image'] && file_exists(UPLOAD_PATH . $p['image'])): ?>
                                    <img src="<?= UPLOAD_URL . e($p['image']) ?>" alt=""
                                         class="w-10 h-10 rounded-lg object-cover border border-border shrink-0">
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-lg bg-primary-light border border-border flex items-center justify-center text-primary shrink-0">
                                        <i data-lucide="shirt" style="width:16px;height:16px;"></i>
                                    </div>
                                <?php endif; ?>
                                <p class="font-medium"><?= e($p['name']) ?></p>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 border-b border-slate-100 align-middle font-semibold text-secondary">
                            <?= rupiah($p['price_sell']) ?>
                        </td>
                        <td class="py-3.5 px-4 border-b border-slate-100 align-middle">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold <?= $kelasStok ?>">
                                <?= $labelStok ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($produkTerbaru)): ?>
                    <tr><td colspan="3" class="text-center py-12 text-muted">Belum ada produk.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-col gap-4">

        <?php if (!empty($listStokRendah)): ?>
        <div class="bg-white border border-border border-l-4 border-l-danger rounded-lg shadow-card">
            <div class="px-4 py-3 bg-danger-light/40 border-b border-border">
                <p class="font-display text-[0.875rem] font-bold text-danger flex items-center gap-1.5">
                    <i data-lucide="alert-triangle" style="width:16px;height:16px;"></i>
                    Stok Rendah
                </p>
                <p class="text-[0.72rem] text-muted mt-0.5"><?= count($listStokRendah) ?> produk perlu direstok</p>
            </div>
            <div class="p-3 flex flex-col gap-0.5">
                <?php foreach ($listStokRendah as $item): ?>
                <a href="<?= BASE_URL ?>/pages/detail-produk.php?id=<?= $item['id'] ?>"
                   class="flex items-center justify-between px-2 py-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <p class="text-[0.82rem] font-medium text-textmain"><?= e($item['name']) ?></p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold <?= $item['stock'] == 0 ? 'bg-danger-light text-danger' : 'bg-warning-light text-warning' ?>">
                        <?= $item['stock'] == 0 ? 'Habis' : $item['stock'].' unit' ?>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white border border-border rounded-lg shadow-card">
            <div class="px-4 py-3 border-b border-border">
                <p class="font-display text-[0.875rem] font-bold text-textmain">Aksi Cepat</p>
            </div>
            <div class="p-4 flex flex-col gap-2">
                <a href="<?= BASE_URL ?>/pages/tambah-produk.php"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all">
                    <i data-lucide="plus" style="width:15px;height:15px;"></i>
                    Tambah Produk Baru
                </a>
                <?php if (($_SESSION['user_role'] ?? 'admin') === 'admin'): ?>
                <a href="<?= BASE_URL ?>/pages/pemasok.php"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
                    <i data-lucide="truck" style="width:15px;height:15px;"></i>
                    Kelola Supplier
                </a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

</main>
<?php require_once __DIR__ . '/../components/footer.php'; ?>
