<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /fashion-ims/login.php'); exit; }
require_once __DIR__ . '/../config/database.php';

$pageTitle    = 'Kategori Produk';
$pageSubtitle = 'Kelola klasifikasi dan prefix SKU produk';
$editData     = null;

if (isset($_GET['hapus'])) {
    if (($_SESSION['user_role'] ?? 'admin') !== 'admin') { setFlash('error', 'Akses ditolak.'); header('Location: ' . BASE_URL . '/pages/kategori.php'); exit; }
    $hapusId = (int)$_GET['hapus'];
    $cek = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE kategori_id = ?"); $cek->execute([$hapusId]);
    if ($cek->fetchColumn() > 0) { setFlash('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh produk.'); }
    else { $pdo->prepare("DELETE FROM kategori WHERE id = ?")->execute([$hapusId]); setFlash('success', 'Kategori berhasil dihapus.'); }
    header('Location: ' . BASE_URL . '/pages/kategori.php'); exit;
}

if (isset($_GET['edit'])) {
    if (($_SESSION['user_role'] ?? 'admin') !== 'admin') { setFlash('error', 'Akses ditolak.'); header('Location: ' . BASE_URL . '/pages/kategori.php'); exit; }
    $q = $pdo->prepare("SELECT * FROM kategori WHERE id = ?"); $q->execute([(int)$_GET['edit']]); $editData = $q->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_SESSION['user_role'] ?? 'admin') !== 'admin') { setFlash('error', 'Akses ditolak.'); header('Location: ' . BASE_URL . '/pages/kategori.php'); exit; }
    $katId = (int)($_POST['id'] ?? 0); $nama = trim($_POST['name'] ?? ''); $slug = trim(strtoupper($_POST['slug'] ?? '')); $desc = trim($_POST['description'] ?? '');
    if (!$nama || !$slug) { setFlash('error', 'Nama kategori dan Prefix SKU tidak boleh kosong.'); }
    else {
        try {
            if (!preg_match('/^[A-Z0-9]+$/', $slug)) throw new Exception('Prefix SKU hanya boleh berisi huruf kapital dan angka tanpa spasi.');
            if ($katId) { $pdo->prepare("UPDATE kategori SET name = ?, slug = ?, description = ? WHERE id = ?")->execute([$nama, $slug, $desc, $katId]); setFlash('success', "Kategori \"$nama\" berhasil diperbarui."); }
            else { $pdo->prepare("INSERT INTO kategori (name, slug, description) VALUES (?, ?, ?)")->execute([$nama, $slug, $desc]); setFlash('success', "Kategori \"$nama\" berhasil ditambahkan."); }
        } catch (Exception $e) { setFlash('error', 'Gagal: ' . $e->getMessage()); }
    }
    header('Location: ' . BASE_URL . '/pages/kategori.php'); exit;
}

$kategori = $pdo->query("SELECT k.*, COUNT(p.id) AS jml_produk FROM kategori k LEFT JOIN produk p ON p.kategori_id = k.id GROUP BY k.id ORDER BY k.name")->fetchAll();

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
            <span class="text-[0.65rem]">›</span><span>Kategori</span>
        </div>
        <h1 class="font-display text-[1.35rem] font-extrabold text-textmain leading-tight">Manajemen Kategori</h1>
        <p class="text-[0.78rem] text-muted mt-0.5"><?= count($kategori) ?> kategori terdaftar</p>
    </div>
    <?php if (!$editData && ($_SESSION['user_role'] ?? 'admin') === 'admin'): ?>
    <button id="btn-toggle-form"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all cursor-pointer">
        <i data-lucide="plus" style="width:15px;height:15px;"></i> Tambah Baru
    </button>
    <?php endif; ?>
</div>

<div id="panel-form" class="bg-white border border-border rounded-lg p-5 mb-5 shadow-card <?= $editData ? '' : 'hidden' ?>">
    <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
        <?php if ($editData): ?>
            <i data-lucide="pencil" style="width:17px;height:17px;" class="text-secondary"></i> Edit Kategori
        <?php else: ?>
            <i data-lucide="plus-circle" style="width:17px;height:17px;" class="text-primary"></i> Tambah Kategori Baru
        <?php endif; ?>
    </p>
    <form method="POST" action="">
        <?php if ($editData): ?><input type="hidden" name="id" value="<?= $editData['id'] ?>"><?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Nama Kategori *</label>
                <input type="text" name="name" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= e($editData['name'] ?? '') ?>" placeholder="Contoh: Baju / Atasan" required>
            </div>
            <div>
                <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Prefix SKU *</label>
                <input type="text" name="slug" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none font-mono form-input" value="<?= e($editData['slug'] ?? '') ?>" placeholder="Contoh: ATS" required>
                <p class="text-[0.72rem] text-slate-400 mt-1">Prefix digunakan untuk generate SKU varian otomatis (maks 10 karakter).</p>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Deskripsi</label>
            <input type="text" name="description" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= e($editData['description'] ?? '') ?>" placeholder="Catatan/deskripsi mengenai kategori">
        </div>
        <div class="flex gap-2 mt-4">
            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all cursor-pointer">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                <?= $editData ? 'Simpan Perubahan' : 'Tambah Kategori' ?>
            </button>
            <a href="<?= BASE_URL ?>/pages/kategori.php" class="inline-flex items-center px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">Batal</a>
        </div>
    </form>
</div>

<div class="grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-5">
    <?php if (empty($kategori)): ?>
    <div class="col-span-full bg-white border border-border rounded-lg py-16 px-4 text-center shadow-card">
        <i data-lucide="tags" style="width:48px;height:48px;" class="text-slate-300 mx-auto mb-3"></i>
        <h3 class="text-lg font-semibold text-slate-700 mb-2">Belum ada kategori</h3>
        <p class="text-slate-500 text-[0.9rem]">Tambahkan kategori pertama untuk mulai mengklasifikasikan produk Anda!</p>
    </div>
    <?php else: ?>
    <?php
    $borderColors = ['border-l-primary', 'border-l-secondary', 'border-l-success', 'border-l-danger'];
    $iconBg       = ['bg-primary-light text-primary', 'bg-secondary-light text-secondary', 'bg-success-light text-success', 'bg-danger-light text-danger'];
    $badgeCls     = ['bg-primary-light text-primary', 'bg-secondary-light text-secondary', 'bg-success-light text-success', 'bg-danger-light text-danger'];
    foreach ($kategori as $index => $k):
        $ci = $index % 4;
    ?>
    <div class="bg-white border border-border border-l-4 <?= $borderColors[$ci] ?> rounded-lg p-5 shadow-card fade-in hover:-translate-y-0.5 transition-transform duration-200 flex flex-col justify-between min-h-[200px]">
        <div>
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-[0.72rem] text-muted flex items-center gap-1 mb-1">
                        Prefix SKU:
                        <span class="font-mono bg-black/5 px-1.5 py-0.5 rounded font-semibold"><?= e($k['slug']) ?></span>
                    </p>
                    <p class="font-display text-lg font-extrabold text-textmain leading-tight"><?= e($k['name']) ?></p>
                </div>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 <?= $iconBg[$ci] ?>">
                    <i data-lucide="tag" style="width:18px;height:18px;"></i>
                </div>
            </div>
            <p class="text-[0.8rem] text-muted leading-relaxed line-clamp-2"><?= e($k['description'] ?: 'Tidak ada deskripsi.') ?></p>
        </div>
        <div class="flex items-center justify-between mt-4 pt-3 border-t border-border">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[0.72rem] font-semibold <?= $badgeCls[$ci] ?>">
                <i data-lucide="package" style="width:12px;height:12px;"></i>
                <?= $k['jml_produk'] ?> Produk
            </span>
            <?php if (($_SESSION['user_role'] ?? 'admin') === 'admin'): ?>
            <div class="flex gap-1.5">
                <a href="?edit=<?= $k['id'] ?>" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-border text-secondary hover:border-secondary transition-all" title="Edit">
                    <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                </a>
                <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-danger-light text-danger border border-danger/20 hover:bg-danger hover:text-white transition-all cursor-pointer" title="Hapus"
                        data-hapus-url="<?= BASE_URL ?>/pages/kategori.php?hapus=<?= $k['id'] ?>"
                        data-hapus-nama="Kategori: <?= e($k['name']) ?>">
                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

</main>
<?php require_once __DIR__ . '/../components/footer.php'; ?>
