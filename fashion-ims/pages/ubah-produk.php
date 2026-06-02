<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /fashion-ims/login.php'); exit; }
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('/pages/produk.php');

$query = $pdo->prepare("SELECT * FROM produk WHERE id = ?"); $query->execute([$id]); $p = $query->fetch();
if (!$p) { setFlash('error', 'Produk tidak ditemukan.'); redirect('/pages/produk.php'); }

$pageTitle    = 'Edit Produk';
$pageSubtitle = 'Mengubah: ' . $p['name'];

$semuaSupplier = $pdo->query("SELECT id, name FROM supplier ORDER BY name")->fetchAll();
$semuaKategori = $pdo->query("SELECT id, name FROM kategori ORDER BY name")->fetchAll();

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
            <span class="text-[0.65rem]">›</span><span>Edit</span>
        </div>
        <h1 class="font-display text-[1.35rem] font-extrabold text-textmain leading-tight">Edit Produk</h1>
    </div>
    <div class="flex gap-2">
        <a href="<?= BASE_URL ?>/pages/detail-produk.php?id=<?= $p['id'] ?>"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
            <i data-lucide="eye" style="width:15px;height:15px;"></i> Detail
        </a>
        <a href="<?= BASE_URL ?>/pages/produk.php"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
            <i data-lucide="arrow-left" style="width:15px;height:15px;"></i> Kembali
        </a>
    </div>
</div>

<form method="POST" action="<?= BASE_URL ?>/actions/simpan-produk.php" enctype="multipart/form-data">
    <input type="hidden" name="aksi" value="ubah">
    <input type="hidden" name="id" value="<?= $p['id'] ?>">
    <input type="hidden" name="foto_lama" value="<?= e($p['image'] ?? '') ?>">

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-5 items-start">

        <div class="flex flex-col gap-4">

            <div class="bg-white border border-border rounded-lg p-5 shadow-card">
                <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                    <i data-lucide="file-text" style="width:17px;height:17px;" class="text-primary"></i> Informasi Produk
                </p>
                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Nama Produk *</label>
                    <input type="text" name="name" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= e($p['name']) ?>" required>
                </div>
                <input type="hidden" name="sku" value="<?= e($p['sku']) ?>">
                <div>
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Deskripsi</label>
                    <textarea name="description" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-textarea resize-y min-h-[90px]"><?= e($p['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="bg-white border border-border rounded-lg p-5 shadow-card">
                <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                    <i data-lucide="coins" style="width:17px;height:17px;" class="text-primary"></i> Harga & Stok
                </p>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Harga Beli (Rp) *</label>
                        <input type="number" name="price_buy" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= $p['price_buy'] ?>" min="0" required>
                    </div>
                    <div>
                        <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Harga Jual (Rp) *</label>
                        <input type="number" name="price_sell" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= $p['price_sell'] ?>" min="0" required>
                    </div>
                    <div>
                        <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Stok *</label>
                        <input type="number" name="stock" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= $p['stock'] ?>" min="0" required>
                    </div>
                    <div>
                        <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Stok Minimum *</label>
                        <input type="number" name="stock_min" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= $p['stock_min'] ?>" min="0" required>
                    </div>
                </div>
                <div>
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Lokasi Rak Gudang *</label>
                    <input type="text" name="bin_location" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= e($p['bin_location'] ?? '') ?>" placeholder="Contoh: Rak A-01" required>
                </div>
            </div>

            <div class="bg-white border border-border rounded-lg p-5 shadow-card">
                <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                    <i data-lucide="ruler" style="width:17px;height:17px;" class="text-primary"></i> Ukuran & Warna
                </p>
                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Ukuran</label>
                    <input type="text" name="size" id="input-ukuran" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input mb-2" value="<?= e($p['size'] ?? '') ?>" placeholder="S, M, L, XL">
                    <div class="bg-slate-50 border border-border rounded-lg p-3 flex flex-col gap-2">
                        <?php foreach ([
                            'Baju / Atasan' => ['XS','S','M','L','XL','XXL','XXXL','All Size','One Size'],
                            'Celana / Bawahan' => ['26','27','28','29','30','31','32','33','34','36','38','40'],
                            'Sepatu / Alas Kaki' => ['36','37','38','39','40','41','42','43','44','45'],
                        ] as $group => $sizes): ?>
                        <div>
                            <p class="text-[0.72rem] font-bold text-primary uppercase tracking-wide mb-1"><?= $group ?></p>
                            <div class="flex flex-wrap gap-1">
                                <?php foreach ($sizes as $u): ?>
                                <button type="button" onclick="tambahUkuran('<?= $u ?>')"
                                        class="px-2 py-0.5 text-[0.72rem] rounded-md bg-white border border-slate-200 text-textmain hover:bg-primary-light hover:text-primary hover:border-primary transition-all cursor-pointer">
                                    <?= $u ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Warna</label>
                    <input type="text" name="color" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= e($p['color'] ?? '') ?>" placeholder="Putih, Hitam, Navy">
                </div>
            </div>

        </div>

        <div class="flex flex-col gap-4">

            <div class="bg-white border border-border rounded-lg p-5 shadow-card">
                <p class="font-display text-[0.875rem] font-bold text-textmain mb-3 flex items-center gap-2">
                    <i data-lucide="camera" style="width:17px;height:17px;" class="text-primary"></i> Foto Produk
                </p>
                <input type="file" id="input-gambar" name="image" accept="image/*" class="hidden">
                <div id="area-upload" class="upload-area border-2 border-dashed border-border rounded-xl p-5 text-center cursor-pointer hover:border-primary transition-all min-h-[160px] flex flex-col items-center justify-center gap-2">
                    <?php if ($p['image'] && file_exists(UPLOAD_PATH . $p['image'])): ?>
                        <img id="preview-gambar" class="w-full object-cover rounded-lg max-h-48" src="<?= UPLOAD_URL . e($p['image']) ?>" alt="Foto Produk">
                        <div id="placeholder-upload" class="hidden flex-col items-center gap-1.5">
                    <?php else: ?>
                        <img id="preview-gambar" class="w-full object-cover rounded-lg max-h-48 hidden" src="" alt="">
                        <div id="placeholder-upload" class="flex flex-col items-center gap-1.5">
                    <?php endif; ?>
                            <i data-lucide="upload-cloud" style="width:36px;height:36px;" class="text-slate-300"></i>
                            <p class="text-[0.85rem] text-slate-500">Klik untuk ganti foto</p>
                        </div>
                </div>
                <?php if ($p['image']): ?>
                <p class="text-[0.7rem] text-slate-400 text-center mt-1">Biarkan kosong untuk tetap gunakan foto lama</p>
                <?php endif; ?>
            </div>

            <div class="bg-white border border-border rounded-lg p-5 shadow-card">
                <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                    <i data-lucide="tag" style="width:17px;height:17px;" class="text-primary"></i> Klasifikasi
                </p>
                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Kategori</label>
                    <select name="kategori_id" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-select">
                        <option value="">— Pilih Kategori —</option>
                        <?php foreach ($semuaKategori as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= $kat['id'] == $p['kategori_id'] ? 'selected' : '' ?>><?= e($kat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Supplier</label>
                    <select name="supplier_id" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-select">
                        <option value="">— Pilih Supplier —</option>
                        <?php foreach ($semuaSupplier as $sup): ?>
                        <option value="<?= $sup['id'] ?>" <?= $sup['id'] == $p['supplier_id'] ? 'selected' : '' ?>><?= e($sup['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="bg-white border border-border rounded-lg p-5 shadow-card">
                <p class="font-display text-[0.875rem] font-bold text-textmain mb-3 flex items-center gap-2">
                    <i data-lucide="toggle-left" style="width:17px;height:17px;" class="text-primary"></i> Status
                </p>
                <div class="flex flex-col gap-2">
                    <label class="flex items-center gap-2.5 cursor-pointer px-3 py-2 rounded-lg border <?= $p['status'] === 'active' ? 'border-2 border-primary bg-primary-light' : 'border-border' ?>">
                        <input type="radio" name="status" value="active" <?= $p['status'] === 'active' ? 'checked' : '' ?>>
                        <div>
                            <p class="text-[0.85rem] font-semibold text-textmain">Aktif</p>
                            <p class="text-[0.68rem] text-muted">Produk tampil dan tersedia</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer px-3 py-2 rounded-lg border <?= $p['status'] === 'inactive' ? 'border-2 border-primary bg-primary-light' : 'border-border' ?>">
                        <input type="radio" name="status" value="inactive" <?= $p['status'] === 'inactive' ? 'checked' : '' ?>>
                        <div>
                            <p class="text-[0.85rem] font-medium text-muted">Nonaktif</p>
                            <p class="text-[0.68rem] text-slate-400">Disembunyikan</p>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all cursor-pointer">
                <i data-lucide="save" style="width:15px;height:15px;"></i> Simpan Perubahan
            </button>
            <a href="<?= BASE_URL ?>/pages/produk.php" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
                Batal
            </a>

        </div>
    </div>

</form>

</main>
<?php require_once __DIR__ . '/../components/footer.php'; ?>

<script>
function tambahUkuran(nilai) {
    const input = document.getElementById('input-ukuran');
    const isiSekarang = input.value.split(',').map(s => s.trim()).filter(Boolean);
    if (!isiSekarang.includes(nilai)) {
        isiSekarang.push(nilai);
        input.value = isiSekarang.join(', ');
    }
}

const areaUpload = document.getElementById('area-upload');
const inputGambar = document.getElementById('input-gambar');
const previewGambar = document.getElementById('preview-gambar');
const placeholderUpload = document.getElementById('placeholder-upload');

if (areaUpload) {
    areaUpload.addEventListener('click', () => inputGambar.click());
    inputGambar.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                previewGambar.src = e.target.result;
                previewGambar.classList.remove('hidden');
                placeholderUpload.classList.add('hidden');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const formElement = document.querySelector('form');
    if (formElement) {
        formElement.addEventListener('submit', function(e) {
            const priceBuy = parseFloat(document.getElementsByName('price_buy')[0].value || 0);
            const priceSell = parseFloat(document.getElementsByName('price_sell')[0].value || 0);
            const stock = parseInt(document.getElementsByName('stock')[0].value || 0);
            const stockMin = parseInt(document.getElementsByName('stock_min')[0].value || 0);
            if (priceBuy < 0 || priceSell < 0 || stock < 0 || stockMin < 0) {
                e.preventDefault(); tampilkanToast('error', 'Jumlah tidak boleh kurang dari 0'); return;
            }
            if (priceBuy > priceSell) {
                e.preventDefault(); tampilkanToast('error', 'Harga beli tidak boleh lebih mahal dari harga jual!'); return;
            }
        });
    }
});
</script>
