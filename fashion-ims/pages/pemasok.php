<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /fashion-ims/login.php'); exit; }
require_once __DIR__ . '/../config/database.php';

if (($_SESSION['user_role'] ?? 'admin') !== 'admin') { setFlash('error', 'Akses ditolak.'); header('Location: ' . BASE_URL . '/pages/dasbor.php'); exit; }

$pageTitle    = 'Supplier';
$pageSubtitle = 'Kelola data supplier produk fashion';
$editData     = null;

if (isset($_GET['hapus'])) {
    $hapusId = (int)$_GET['hapus'];
    $cek = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE supplier_id = ?"); $cek->execute([$hapusId]);
    if ($cek->fetchColumn() > 0) { setFlash('error', 'Supplier tidak bisa dihapus karena masih terhubung dengan produk.'); }
    else { $pdo->prepare("DELETE FROM supplier WHERE id = ?")->execute([$hapusId]); setFlash('success', 'Supplier berhasil dihapus.'); }
    header('Location: ' . BASE_URL . '/pages/pemasok.php'); exit;
}

if (isset($_GET['edit'])) {
    $q = $pdo->prepare("SELECT * FROM supplier WHERE id = ?"); $q->execute([(int)$_GET['edit']]); $editData = $q->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supId = (int)($_POST['id'] ?? 0); $nama = trim($_POST['nama'] ?? ''); $email = trim($_POST['email'] ?? ''); $telp = trim($_POST['telp'] ?? ''); $alamat = trim($_POST['alamat'] ?? '');
    if (!$nama || !$email || !$telp || !$alamat) { setFlash('error', 'Semua bidang wajib diisi.'); }
    elseif (!preg_match('/^[0-9]+$/', $telp)) { setFlash('error', 'Nomor telepon hanya boleh berisi angka.'); }
    else {
        try {
            if ($supId) { $pdo->prepare("UPDATE supplier SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?")->execute([$nama, $email, $telp, $alamat, $supId]); setFlash('success', "Supplier \"$nama\" berhasil diperbarui."); }
            else { $pdo->prepare("INSERT INTO supplier (name, email, phone, address) VALUES (?, ?, ?, ?)")->execute([$nama, $email, $telp, $alamat]); setFlash('success', "Supplier \"$nama\" berhasil ditambahkan."); }
        } catch (PDOException $e) { setFlash('error', 'Gagal menyimpan data supplier.'); }
    }
    header('Location: ' . BASE_URL . '/pages/pemasok.php'); exit;
}

$supplier = $pdo->query("SELECT s.*, COUNT(p.id) AS jml_produk FROM supplier s LEFT JOIN produk p ON p.supplier_id = s.id GROUP BY s.id ORDER BY s.name")->fetchAll();

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
            <span class="text-[0.65rem]">›</span><span>Supplier</span>
        </div>
        <h1 class="font-display text-[1.35rem] font-extrabold text-textmain leading-tight">Manajemen Supplier</h1>
        <p class="text-[0.78rem] text-muted mt-0.5"><?= count($supplier) ?> supplier terdaftar</p>
    </div>
    <?php if (!$editData): ?>
    <button id="btn-toggle-form" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all cursor-pointer">
        <i data-lucide="plus" style="width:15px;height:15px;"></i> Tambah Baru
    </button>
    <?php endif; ?>
</div>

<div id="panel-form" class="bg-white border border-border rounded-lg p-5 mb-5 shadow-card <?= $editData ? '' : 'hidden' ?>">
    <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
        <?php if ($editData): ?>
            <i data-lucide="pencil" style="width:17px;height:17px;" class="text-secondary"></i> Edit Supplier
        <?php else: ?>
            <i data-lucide="plus-circle" style="width:17px;height:17px;" class="text-primary"></i> Tambah Supplier Baru
        <?php endif; ?>
    </p>
    <form method="POST" action="">
        <?php if ($editData): ?><input type="hidden" name="id" value="<?= $editData['id'] ?>"><?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Nama Supplier *</label>
                <input type="text" name="nama" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= e($editData['name'] ?? '') ?>" placeholder="PT / CV Nama Supplier" required>
            </div>
            <div>
                <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Email *</label>
                <input type="email" name="email" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= e($editData['email'] ?? '') ?>" placeholder="email@supplier.com" required>
            </div>
            <div>
                <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">No. Telepon *</label>
                <input type="text" name="telp" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= e($editData['phone'] ?? '') ?>" placeholder="08123456789" required pattern="[0-9]+" title="Hanya angka" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Alamat *</label>
            <input type="text" name="alamat" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= e($editData['address'] ?? '') ?>" placeholder="Jl. Nama Jalan No. X, Kota" required>
        </div>
        <div class="flex gap-2 mt-4">
            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all cursor-pointer">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                <?= $editData ? 'Simpan Perubahan' : 'Tambah Supplier' ?>
            </button>
            <a href="<?= BASE_URL ?>/pages/pemasok.php" class="inline-flex items-center px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">Batal</a>
        </div>
    </form>
</div>

<div class="bg-white border border-border rounded-lg shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 pl-5 pr-3 text-left border-b border-border">#</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Nama Supplier</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Email</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Telepon</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Alamat</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Produk</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 pr-5 pl-3 text-right border-b border-border">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($supplier)): ?>
                <tr><td colspan="7" class="py-12 text-center text-muted">
                    <i data-lucide="truck" style="width:48px;height:48px;opacity:0.3;margin:0 auto 0.75rem;display:block;"></i>
                    <p>Belum ada supplier. Tambahkan supplier pertama!</p>
                </td></tr>
                <?php else: ?>
                <?php foreach ($supplier as $i => $s): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 pl-5 pr-3 border-b border-slate-100 align-middle text-slate-400"><?= $i + 1 ?></td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 bg-primary-light rounded-lg flex items-center justify-center shrink-0 text-primary">
                                <i data-lucide="building-2" style="width:15px;height:15px;"></i>
                            </div>
                            <p class="font-medium text-textmain"><?= e($s['name']) ?></p>
                        </div>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.82rem] text-slate-500"><?= e($s['email'] ?: '—') ?></td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.82rem] text-slate-500"><?= e($s['phone'] ?: '—') ?></td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.78rem] text-slate-500 max-w-[200px] truncate"><?= e($s['address'] ?: '—') ?></td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-primary-light text-primary"><?= $s['jml_produk'] ?> produk</span>
                    </td>
                    <td class="py-3 pr-5 pl-3 border-b border-slate-100 align-middle text-right">
                        <div class="flex justify-end gap-1.5">
                            <a href="?edit=<?= $s['id'] ?>" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-border text-secondary hover:border-secondary transition-all" title="Edit"><i data-lucide="pencil" style="width:13px;height:13px;"></i></a>
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-danger-light text-danger border border-danger/20 hover:bg-danger hover:text-white transition-all cursor-pointer" title="Hapus" data-hapus-url="<?= BASE_URL ?>/pages/pemasok.php?hapus=<?= $s['id'] ?>" data-hapus-nama="Supplier: <?= e($s['name']) ?>"><i data-lucide="trash-2" style="width:13px;height:13px;"></i></button>
                        </div>
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
