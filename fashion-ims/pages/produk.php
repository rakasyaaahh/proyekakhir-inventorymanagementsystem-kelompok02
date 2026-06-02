<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /fashion-ims/login.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';

$pageTitle    = 'Produk';
$pageSubtitle = 'Kelola semua produk fashion';

$cari         = trim($_GET['q']        ?? '');
$statFilter   = trim($_GET['status']   ?? '');
$filterRendah = isset($_GET['filter']) && $_GET['filter'] === 'stokrendah';

$perHalaman = 10;
$halaman    = max(1, (int)($_GET['hal'] ?? 1));
$offset     = ($halaman - 1) * $perHalaman;

$kondisi = ['1=1'];
$params  = [];

if ($cari) {
    $kondisi[] = '(p.name LIKE :cari OR p.sku LIKE :cari2)';
    $params[':cari']  = "%$cari%";
    $params[':cari2'] = "%$cari%";
}
if ($statFilter) {
    $kondisi[] = 'p.status = :stat';
    $params[':stat'] = $statFilter;
}
if ($filterRendah) {
    $kondisi[] = 'p.stock <= p.stock_min';
}

$where = implode(' AND ', $kondisi);

$totalData = $pdo->prepare("SELECT COUNT(*) FROM produk p WHERE $where");
$totalData->execute($params);
$total    = (int)$totalData->fetchColumn();
$totalHal = (int)ceil($total / $perHalaman);

$query = $pdo->prepare("
    SELECT p.*, k.name AS kategori_nama
    FROM produk p
    LEFT JOIN kategori k ON p.kategori_id = k.id
    WHERE $where
    ORDER BY p.created_at DESC
    LIMIT :lim OFFSET :off
");
foreach ($params as $k => $v) $query->bindValue($k, $v);
$query->bindValue(':lim', $perHalaman, PDO::PARAM_INT);
$query->bindValue(':off', $offset, PDO::PARAM_INT);
$query->execute();
$produk = $query->fetchAll();

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    ob_start();
    if (empty($produk)): ?>
        <tr><td colspan="9"><div class="py-12 text-center text-muted"><i data-lucide="package" style="width:48px;height:48px;opacity:0.3;margin:0 auto 0.75rem;display:block;"></i><p>Tidak ada produk ditemukan.</p></div></td></tr>
    <?php else:
        foreach ($produk as $p):
            $kelasStok = ($p['stock'] == 0) ? 'bg-danger-light text-danger' : (($p['stock'] <= $p['stock_min']) ? 'bg-warning-light text-warning' : 'bg-success-light text-success');
            $labelStok = ($p['stock'] == 0) ? 'Habis' : $p['stock'] . ' unit';
            ?>
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="py-3 pl-5 pr-3 border-b border-slate-100 align-middle">
                    <?php if ($p['image'] && file_exists(UPLOAD_PATH . $p['image'])): ?>
                        <img src="<?= UPLOAD_URL . e($p['image']) ?>" alt="" class="w-10 h-10 rounded-lg object-cover border border-border">
                    <?php else: ?>
                        <div class="w-10 h-10 rounded-lg bg-primary-light border border-border flex items-center justify-center text-primary"><i data-lucide="shirt" style="width:16px;height:16px;"></i></div>
                    <?php endif; ?>
                </td>
                <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.855rem]"><p class="font-medium text-textmain"><?= e($p['name']) ?></p></td>
                <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.8rem] text-slate-500 font-medium"><?= e($p['kategori_nama'] ?: '—') ?></td>
                <td class="py-3 px-3 border-b border-slate-100 align-middle"><?php $ukuran = array_slice(explode(',', $p['size'] ?? ''), 0, 3); foreach ($ukuran as $u) echo '<span class="inline-block px-1.5 py-0.5 rounded text-[0.68rem] font-semibold bg-surface border border-border text-muted m-px">'.e(trim($u)).'</span>'; ?></td>
                <td class="py-3 px-3 border-b border-slate-100 align-middle">
                    <p class="font-semibold text-secondary"><?= rupiah($p['price_sell']) ?></p>
                    <p class="text-[0.7rem] text-slate-400">Beli: <?= rupiah($p['price_buy']) ?></p>
                </td>
                <td class="py-3 px-3 border-b border-slate-100 align-middle">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold <?= $kelasStok ?>"><?= $labelStok ?></span>
                    <?php if ($p['stock'] > 0 && $p['stock'] <= $p['stock_min']): ?>
                    <p class="text-[0.65rem] text-warning mt-0.5">Min: <?= $p['stock_min'] ?></p>
                    <?php endif; ?>
                </td>
                <td class="py-3 px-3 border-b border-slate-100 align-middle">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-primary-light text-primary font-mono"><?= e($p['bin_location'] ?: '—') ?></span>
                </td>
                <td class="py-3 px-3 border-b border-slate-100 align-middle">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold <?= $p['status'] === 'active' ? 'bg-success-light text-success' : 'bg-slate-100 text-slate-500' ?>">
                        <?= $p['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                </td>
                <td class="py-3 pr-5 pl-3 border-b border-slate-100 align-middle text-right">
                    <div class="flex justify-end gap-1.5">
                        <a href="<?= BASE_URL ?>/pages/detail-produk.php?id=<?= $p['id'] ?>" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-border text-primary hover:border-primary transition-all" title="Detail"><i data-lucide="eye" style="width:14px;height:14px;"></i></a>
                        <a href="<?= BASE_URL ?>/pages/ubah-produk.php?id=<?= $p['id'] ?>" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-border text-secondary hover:border-secondary transition-all" title="Edit"><i data-lucide="pencil" style="width:14px;height:14px;"></i></a>
                        <?php if (($_SESSION['user_role'] ?? 'admin') === 'admin'): ?>
                        <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-danger-light text-danger border border-danger/20 hover:bg-danger hover:text-white transition-all cursor-pointer" title="Hapus" data-hapus-url="<?= BASE_URL ?>/actions/hapus-produk.php?id=<?= $p['id'] ?>" data-hapus-nama="<?= e($p['name']) ?>"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach;
    endif;
    $tbodyHtml = ob_get_clean();

    ob_start();
    if ($totalHal > 1): ?>
        <p class="text-[0.78rem] text-slate-500">Menampilkan <?= $offset+1 ?>–<?= min($offset+$perHalaman,$total) ?> dari <?= $total ?> produk</p>
        <div class="flex items-center gap-1">
            <?php if ($halaman > 1): ?><a href="?<?= http_build_query(array_merge($_GET, ['hal' => $halaman-1])) ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-[0.8rem] text-muted border border-border bg-white hover:border-primary hover:text-primary transition-all">‹</a><?php endif; ?>
            <?php for ($i = 1; $i <= $totalHal; $i++): ?><a href="?<?= http_build_query(array_merge($_GET, ['hal' => $i])) ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-[0.8rem] border transition-all <?= $i == $halaman ? 'bg-primary text-white border-primary font-semibold' : 'text-muted border-border bg-white hover:border-primary hover:text-primary' ?>"><?= $i ?></a><?php endfor; ?>
            <?php if ($halaman < $totalHal): ?><a href="?<?= http_build_query(array_merge($_GET, ['hal' => $halaman+1])) ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-[0.8rem] text-muted border border-border bg-white hover:border-primary hover:text-primary transition-all">›</a><?php endif; ?>
        </div>
    <?php endif;
    $pagHtml = ob_get_clean();

    echo json_encode([
        'tbody'    => $tbodyHtml,
        'paginasi' => $pagHtml,
        'total'    => number_format($total) . ' produk ditemukan'
    ]);
    exit;
}

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
            <span class="text-[0.65rem]">›</span>
            <span>Produk</span>
        </div>
        <h1 class="font-display text-[1.35rem] font-extrabold text-textmain leading-tight">Daftar Produk</h1>
        <p id="total-produk" class="text-[0.78rem] text-muted mt-0.5"><?= number_format($total) ?> produk ditemukan</p>
    </div>
    <a href="<?= BASE_URL ?>/pages/tambah-produk.php"
       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all">
        <i data-lucide="plus" style="width:15px;height:15px;"></i>
        Tambah Produk
    </a>
</div>

<form id="form-filter" method="GET" action="">
    <div class="flex items-center gap-2 flex-wrap bg-white border border-border rounded-lg px-5 py-3.5 mb-5 shadow-card">
        <div class="relative flex-1 min-w-[200px]">
            <i data-lucide="search" style="width:15px;height:15px;" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-muted pointer-events-none"></i>
            <input type="text" id="input-cari" name="q"
                   class="w-full bg-white border border-border rounded-lg pl-9 pr-3 py-[0.58rem] text-sm text-textmain outline-none placeholder:text-slate-300 form-input"
                   placeholder="Cari nama produk..."
                   value="<?= e($cari) ?>">
        </div>
        <select name="status" class="border border-border rounded-lg px-3 py-[0.58rem] text-sm text-textmain bg-white outline-none min-w-[140px] form-select" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="active"   <?= $statFilter === 'active'   ? 'selected' : '' ?>>Aktif</option>
            <option value="inactive" <?= $statFilter === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
        <?php if ($cari || $statFilter || $filterRendah): ?>
        <a href="<?= BASE_URL ?>/pages/produk.php"
           class="inline-flex items-center gap-1 px-3 py-[0.58rem] rounded-lg text-[0.78rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
            <i data-lucide="x" style="width:13px;height:13px;"></i> Reset
        </a>
        <?php endif; ?>
    </div>
</form>

<?php
$jmlRendah = $pdo->query("SELECT COUNT(*) FROM produk WHERE stock <= stock_min AND status='active'")->fetchColumn();
if ($jmlRendah > 0 && !$filterRendah):
?>
<div class="bg-danger-light/50 border border-danger/25 rounded-xl px-5 py-3.5 mb-5 flex items-center gap-3">
    <i data-lucide="alert-triangle" style="width:18px;height:18px;" class="text-danger shrink-0"></i>
    <p class="text-[0.85rem] text-textmain">
        <strong class="text-danger"><?= $jmlRendah ?> produk</strong> memiliki stok di bawah minimum.
        <a href="?filter=stokrendah" class="text-primary underline ml-1">Lihat semua →</a>
    </p>
</div>
<?php endif; ?>

<div class="bg-white border border-border rounded-lg shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 pl-5 pr-3 text-left border-b border-border w-12"></th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Nama Produk</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Kategori</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Ukuran</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Harga Jual</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Stok</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Lokasi Rak</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Status</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 pr-5 pl-3 text-right border-b border-border">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabel-produk">
                <?php if (empty($produk)): ?>
                <tr>
                    <td colspan="9" class="py-12 text-center text-muted">
                        <i data-lucide="package" style="width:48px;height:48px;opacity:0.3;margin:0 auto 0.75rem;display:block;"></i>
                        <p>Tidak ada produk ditemukan.</p>
                        <a href="<?= BASE_URL ?>/pages/tambah-produk.php"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-primary text-white hover:bg-primary-dark transition-all mt-3">
                            <i data-lucide="plus" style="width:15px;height:15px;"></i> Tambah Produk
                        </a>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($produk as $p): ?>
                <?php
                    if ($p['stock'] == 0)                   { $kelasStok = 'bg-danger-light text-danger';   $labelStok = 'Habis'; }
                    elseif ($p['stock'] <= $p['stock_min']) { $kelasStok = 'bg-warning-light text-warning'; $labelStok = $p['stock'].' unit'; }
                    else                                    { $kelasStok = 'bg-success-light text-success'; $labelStok = $p['stock'].' unit'; }
                ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 pl-5 pr-3 border-b border-slate-100 align-middle">
                        <?php if ($p['image'] && file_exists(UPLOAD_PATH . $p['image'])): ?>
                            <img src="<?= UPLOAD_URL . e($p['image']) ?>" alt="" class="w-10 h-10 rounded-lg object-cover border border-border">
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-lg bg-primary-light border border-border flex items-center justify-center text-primary"><i data-lucide="shirt" style="width:16px;height:16px;"></i></div>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.855rem]">
                        <p class="font-medium text-textmain"><?= e($p['name']) ?></p>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.8rem] text-slate-500 font-medium"><?= e($p['kategori_nama'] ?: '—') ?></td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle">
                        <?php $ukuran = array_slice(explode(',', $p['size'] ?? ''), 0, 3); foreach ($ukuran as $u) echo '<span class="inline-block px-1.5 py-0.5 rounded text-[0.68rem] font-semibold bg-surface border border-border text-muted m-px">'.e(trim($u)).'</span>'; ?>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle">
                        <p class="font-semibold text-secondary"><?= rupiah($p['price_sell']) ?></p>
                        <p class="text-[0.7rem] text-slate-400">Beli: <?= rupiah($p['price_buy']) ?></p>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold <?= $kelasStok ?>"><?= $labelStok ?></span>
                        <?php if ($p['stock'] > 0 && $p['stock'] <= $p['stock_min']): ?>
                        <p class="text-[0.65rem] text-warning mt-0.5">Min: <?= $p['stock_min'] ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-primary-light text-primary font-mono"><?= e($p['bin_location'] ?: '—') ?></span>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold <?= $p['status'] === 'active' ? 'bg-success-light text-success' : 'bg-slate-100 text-slate-500' ?>">
                            <?= $p['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td class="py-3 pr-5 pl-3 border-b border-slate-100 align-middle text-right">
                        <div class="flex justify-end gap-1.5">
                            <a href="<?= BASE_URL ?>/pages/detail-produk.php?id=<?= $p['id'] ?>" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-border text-primary hover:border-primary transition-all" title="Detail"><i data-lucide="eye" style="width:14px;height:14px;"></i></a>
                            <a href="<?= BASE_URL ?>/pages/ubah-produk.php?id=<?= $p['id'] ?>" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-border text-secondary hover:border-secondary transition-all" title="Edit"><i data-lucide="pencil" style="width:14px;height:14px;"></i></a>
                            <?php if (($_SESSION['user_role'] ?? 'admin') === 'admin'): ?>
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-danger-light text-danger border border-danger/20 hover:bg-danger hover:text-white transition-all cursor-pointer" title="Hapus" data-hapus-url="<?= BASE_URL ?>/actions/hapus-produk.php?id=<?= $p['id'] ?>" data-hapus-nama="<?= e($p['name']) ?>"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalHal > 1): ?>
    <div id="area-paginasi" class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
        <p class="text-[0.78rem] text-slate-500">
            Menampilkan <?= $offset+1 ?>–<?= min($offset+$perHalaman,$total) ?> dari <?= $total ?> produk
        </p>
        <div class="flex items-center gap-1">
            <?php if ($halaman > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['hal' => $halaman-1])) ?>"
               class="w-8 h-8 flex items-center justify-center rounded-lg text-[0.8rem] text-muted border border-border bg-white hover:border-primary hover:text-primary transition-all">‹</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalHal; $i++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['hal' => $i])) ?>"
               class="w-8 h-8 flex items-center justify-center rounded-lg text-[0.8rem] border transition-all <?= $i == $halaman ? 'bg-primary text-white border-primary font-semibold' : 'text-muted border-border bg-white hover:border-primary hover:text-primary' ?>">
               <?= $i ?>
            </a>
            <?php endfor; ?>
            <?php if ($halaman < $totalHal): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['hal' => $halaman+1])) ?>"
               class="w-8 h-8 flex items-center justify-center rounded-lg text-[0.8rem] text-muted border border-border bg-white hover:border-primary hover:text-primary transition-all">›</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

</main>
<?php require_once __DIR__ . '/../components/footer.php'; ?>
