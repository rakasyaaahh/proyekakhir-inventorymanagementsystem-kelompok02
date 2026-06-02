<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /fashion-ims/login.php'); exit; }
require_once __DIR__ . '/../config/database.php';

$pageTitle    = 'Tambah Produk';
$pageSubtitle = 'Isi formulir di bawah untuk menambah produk baru';

$semuaSupplier = $pdo->query("SELECT id, name FROM supplier ORDER BY name")->fetchAll();
$semuaKategori = $pdo->query("SELECT id, name, slug FROM kategori ORDER BY name")->fetchAll();

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
            <span class="text-[0.65rem]">›</span><span>Tambah Baru</span>
        </div>
        <h1 class="font-display text-[1.35rem] font-extrabold text-textmain leading-tight">Tambah Produk Baru</h1>
    </div>
    <a href="<?= BASE_URL ?>/pages/produk.php"
       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
        <i data-lucide="arrow-left" style="width:15px;height:15px;"></i> Kembali
    </a>
</div>

<form method="POST" action="<?= BASE_URL ?>/actions/simpan-produk.php" enctype="multipart/form-data">
    <input type="hidden" name="aksi" value="tambah">

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-5 items-start">

        <div class="flex flex-col gap-4">

            <div class="bg-white border border-border rounded-lg p-5 shadow-card">
                <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                    <i data-lucide="file-text" style="width:17px;height:17px;" class="text-primary"></i> Informasi Produk
                </p>
                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Nama Produk/Model *</label>
                    <input type="text" name="name" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" placeholder="Contoh: Kemeja Flanel Premium" required>
                </div>

                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Kode Model *</label>
                    <input type="text" id="input-model-code" name="model_code" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none font-mono form-input" placeholder="Contoh: KMJ-FLN" required>
                    <p class="text-[0.72rem] text-slate-400 mt-1">Kode dasar model baju (misal: KMJ-FLN). Otomatis terisi saat mengisi nama produk.</p>
                </div>
                <div>
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Deskripsi Produk</label>
                    <textarea name="description" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-textarea resize-y min-h-[90px]" placeholder="Jelaskan bahan, keunggulan, dan detail produk..."></textarea>
                </div>
            </div>

            <div class="bg-white border border-border rounded-lg p-5 shadow-card">
                <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                    <i data-lucide="coins" style="width:17px;height:17px;" class="text-primary"></i> Harga & Stok
                </p>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Harga Beli (HPP) *</label>
                        <input type="number" name="price_buy" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" placeholder="0" min="0" required>
                    </div>
                    <div>
                        <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Harga Jual Standar *</label>
                        <input type="number" name="price_sell" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" placeholder="0" min="0" required>
                    </div>
                    <div>
                        <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Stok Awal Standar *</label>
                        <input type="number" name="stock" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" placeholder="0" min="0" value="0" required>
                    </div>
                    <div>
                        <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Stok Minimum *</label>
                        <input type="number" name="stock_min" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" placeholder="5" min="0" value="5" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Lokasi Rak Gudang *</label>
                    <input type="text" name="bin_location" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" placeholder="Contoh: Rak A-01, Rak B-02" required>
                </div>
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                    <div>
                        <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Ukuran Tunggal (Opsional)</label>
                        <input type="text" name="size" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" placeholder="Contoh: XL, L, 32">
                        <p class="text-[0.68rem] text-slate-400 mt-1">Diisi hanya jika Anda menambah produk tunggal tanpa multi-varian matrix.</p>
                    </div>
                    <div>
                        <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Warna Tunggal (Opsional)</label>
                        <input type="text" name="color" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" placeholder="Contoh: Merah, Hitam">
                        <p class="text-[0.68rem] text-slate-400 mt-1">Diisi hanya jika Anda menambah produk tunggal tanpa multi-varian matrix.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-border rounded-lg p-5 shadow-card">
                <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                    <i data-lucide="ruler" style="width:17px;height:17px;" class="text-primary"></i> Multi-Varian Matrix Input
                </p>

                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-2">Pilih Ukuran (Size Matrix) *</label>
                    <div class="bg-slate-50 border border-border rounded-lg p-4 flex flex-col gap-3">
                        <?php foreach ([
                            'Baju / Atasan' => ['XS','S','M','L','XL','XXL','XXXL','All Size','One Size'],
                            'Celana / Bawahan' => ['26','27','28','29','30','31','32','33','34','36','38','40'],
                            'Sepatu / Alas Kaki' => ['36','37','38','39','40','41','42','43','44','45'],
                        ] as $group => $sizes): ?>
                        <div>
                            <p class="text-[0.72rem] font-bold text-primary uppercase tracking-wide mb-1.5"><?= $group ?></p>
                            <div class="flex flex-wrap gap-1.5">
                                <?php foreach ($sizes as $sz): ?>
                                <label class="inline-flex items-center gap-1.5 px-2 py-1 bg-white border border-border rounded-md text-[0.72rem] cursor-pointer hover:border-primary hover:text-primary transition-all">
                                    <input type="checkbox" class="chk-ukuran" value="<?= $sz ?>" <?= in_array($sz, ['S','M','L','XL']) ? 'checked' : '' ?>>
                                    <?= $sz ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Warna Tersedia (Pisahkan dengan koma) *</label>
                    <input type="text" id="input-warna" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" placeholder="Contoh: Hitam, Putih, Merah, Navy" value="Hitam, Putih">
                    <p class="text-[0.68rem] text-slate-400 mt-1">Masukkan warna dipisahkan koma untuk digenerate secara otomatis.</p>
                </div>

                <div class="text-right mb-4">
                    <button type="button" id="btn-generate-matrix"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[0.78rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all cursor-pointer">
                        <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i> Generate Matrix Varian
                    </button>
                </div>

                <div id="matrix-preview-area" class="hidden">
                    <p class="text-[0.78rem] font-semibold text-primary border-t border-border pt-3 mb-2">Daftar Varian yang akan Dibuat:</p>
                    <div class="overflow-x-auto border border-border rounded-lg">
                        <table class="w-full border-collapse text-[0.75rem]">
                            <thead>
                                <tr>
                                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase py-2 px-3 text-left border-b border-border">Varian</th>
                                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase py-2 px-3 text-left border-b border-border">Harga Jual (Rp)</th>
                                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase py-2 px-3 text-left border-b border-border">Stok Awal</th>
                                </tr>
                            </thead>
                            <tbody id="matrix-tbody"></tbody>
                        </table>
                    </div>
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
                    <img id="preview-gambar" class="w-full object-cover rounded-lg hidden max-h-48" src="" alt="Preview">
                    <div id="placeholder-upload" class="flex flex-col items-center gap-1.5">
                        <i data-lucide="upload-cloud" style="width:36px;height:36px;" class="text-slate-300"></i>
                        <p class="text-[0.85rem] text-slate-500">Klik atau seret foto ke sini</p>
                        <p class="text-[0.72rem] text-slate-400">JPG, PNG, WEBP — Maks 2MB</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-border rounded-lg p-5 shadow-card">
                <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                    <i data-lucide="tag" style="width:17px;height:17px;" class="text-primary"></i> Klasifikasi
                </p>
                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Kategori *</label>
                    <select name="kategori_id" id="pilihan-kategori" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-select" required>
                        <option value="">— Pilih Kategori —</option>
                        <?php foreach ($semuaKategori as $kat): ?>
                        <option value="<?= $kat['id'] ?>" data-prefix="<?= e($kat['slug']) ?>"><?= e($kat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Supplier</label>
                    <select name="supplier_id" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-select">
                        <option value="">— Pilih Supplier —</option>
                        <?php foreach ($semuaSupplier as $sup): ?>
                        <option value="<?= $sup['id'] ?>"><?= e($sup['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="bg-white border border-border rounded-lg p-5 shadow-card">
                <p class="font-display text-[0.875rem] font-bold text-textmain mb-3 flex items-center gap-2">
                    <i data-lucide="toggle-left" style="width:17px;height:17px;" class="text-primary"></i> Status
                </p>
                <div class="flex flex-col gap-2">
                    <label class="flex items-center gap-2.5 cursor-pointer px-3 py-2 rounded-lg border-2 border-primary bg-primary-light">
                        <input type="radio" name="status" value="active" checked>
                        <div>
                            <p class="text-[0.85rem] font-semibold text-textmain">Aktif</p>
                            <p class="text-[0.68rem] text-muted">Produk tampil dan tersedia</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer px-3 py-2 rounded-lg border border-border">
                        <input type="radio" name="status" value="inactive">
                        <div>
                            <p class="text-[0.85rem] font-medium text-muted">Nonaktif</p>
                            <p class="text-[0.68rem] text-slate-400">Produk disembunyikan</p>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all cursor-pointer">
                <i data-lucide="save" style="width:15px;height:15px;"></i> Simpan Produk
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
document.addEventListener('DOMContentLoaded', function () {
    const inputModelCode = document.getElementById('input-model-code');
    const inputName = document.getElementsByName('name')[0];

    if (inputModelCode && inputName) {
        let isManuallyEdited = false;
        inputModelCode.addEventListener('input', function() {
            isManuallyEdited = inputModelCode.value.trim() !== '';
        });
        inputName.addEventListener('input', function () {
            if (isManuallyEdited) return;
            const nama = inputName.value.trim();
            if (!nama) { inputModelCode.value = ''; return; }
            const kata = nama.split(/\s+/).slice(0, 3);
            let code = kata.map(k => k.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 3)).join('-');
            if (!code) code = 'PRD';
            inputModelCode.value = code;
        });
    }

    const btnMatrix = document.getElementById('btn-generate-matrix');
    const matrixPreview = document.getElementById('matrix-preview-area');
    const matrixTbody = document.getElementById('matrix-tbody');
    const inputWarna = document.getElementById('input-warna');
    const inputPriceSell = document.getElementsByName('price_sell')[0];
    const inputStock = document.getElementsByName('stock')[0];
    const choicesKateg = document.getElementById('pilihan-kategori');

    function updateMatrix() {
        const modelCode = inputModelCode.value.trim().toUpperCase();
        if (!modelCode) { tampilkanToast('warning', 'Isi Kode Model terlebih dahulu!'); return; }
        let catPrefix = 'PRD';
        if (choicesKateg && choicesKateg.selectedIndex > 0) {
            catPrefix = choicesKateg.options[choicesKateg.selectedIndex].getAttribute('data-prefix') || 'PRD';
        }
        const warnaList = inputWarna.value.split(',').map(w => w.trim()).filter(w => w.length > 0);
        const ukuranList = Array.from(document.querySelectorAll('.chk-ukuran:checked')).map(cb => cb.value);
        if (warnaList.length === 0 || ukuranList.length === 0) { tampilkanToast('warning', 'Pilih minimal satu ukuran dan masukkan satu warna!'); return; }
        const defaultPrice = inputPriceSell ? inputPriceSell.value || 0 : 0;
        const defaultStock = inputStock ? inputStock.value || 0 : 0;
        matrixTbody.innerHTML = '';
        const warnaPopuler = {'HITAM':'BLK','PUTIH':'WHT','MERAH':'RED','BIRU':'BLU','KUNING':'YLW','HIJAU':'GRN','ABU':'GRY','COKLAT':'BRN','KREM':'CRM','NAVY':'NVY','MERAHMUDA':'PNK','PINK':'PNK'};
        warnaList.forEach(w => {
            let cleanWarna = w.replace(/[^a-zA-Z]/g, '').toUpperCase();
            let colorCode = warnaPopuler[cleanWarna] || (cleanWarna.slice(0,3).padEnd(3,'X'));
            ukuranList.forEach(u => {
                const skuVariant = `${modelCode}-${catPrefix}-${colorCode}-${u.replace(/\s+/g,'')}`;
                const row = document.createElement('tr');
                row.className = 'hover:bg-slate-50';
                row.innerHTML = `
                    <td class="py-2 px-3 border-b border-slate-100 font-semibold text-primary">${w} — Size ${u}
                        <input type="hidden" name="variants[color][]" value="${w}">
                        <input type="hidden" name="variants[size][]" value="${u}">
                        <input type="hidden" name="variants[sku][]" value="${skuVariant}">
                    </td>
                    <td class="py-2 px-3 border-b border-slate-100">
                        <input type="number" name="variants[price_sell][]" value="${defaultPrice}" class="w-full border border-border rounded px-2 py-1 text-[0.75rem] outline-none form-input" min="0" required>
                    </td>
                    <td class="py-2 px-3 border-b border-slate-100">
                        <input type="number" name="variants[stock][]" value="${defaultStock}" class="w-full border border-border rounded px-2 py-1 text-[0.75rem] outline-none form-input" min="0" required>
                    </td>
                `;
                matrixTbody.appendChild(row);
            });
        });
        matrixPreview.classList.remove('hidden');
        tampilkanToast('sukses', `Berhasil membuat matriks ${warnaList.length * ukuranList.length} varian produk!`);
    }

    if (btnMatrix) btnMatrix.addEventListener('click', updateMatrix);

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

    const formElement = document.querySelector('form');
    if (formElement) {
        formElement.addEventListener('submit', function(e) {
            const hasVariants = matrixTbody && matrixTbody.children.length > 0;
            if (hasVariants) {
                const variantPrices = document.getElementsByName('variants[price_sell][]');
                const variantStocks = document.getElementsByName('variants[stock][]');
                const priceBuy = parseFloat(document.getElementsByName('price_buy')[0].value || 0);
                for (let i = 0; i < variantPrices.length; i++) {
                    if (parseFloat(variantPrices[i].value) < 0 || parseInt(variantStocks[i].value) < 0) {
                        e.preventDefault(); tampilkanToast('error', 'Jumlah tidak boleh kurang dari 0'); return;
                    }
                    if (priceBuy > parseFloat(variantPrices[i].value)) {
                        e.preventDefault(); tampilkanToast('error', 'Harga beli tidak boleh lebih mahal dari harga jual varian!'); return;
                    }
                }
            } else {
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
            }
        });
    }
});
</script>
