<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /fashion-ims/login.php'); exit; }
require_once __DIR__ . '/../config/database.php';

$pageTitle    = 'Buat Nota Keluar';
$pageSubtitle = 'Form pencatatan barang keluar & penjualan';

$semuaProduk = $pdo->query("
    SELECT p.id, p.name, p.sku, p.size, p.color, p.price_sell, p.stock, c.name AS nama_kategori
    FROM produk p LEFT JOIN kategori c ON c.id = p.kategori_id
    WHERE p.status = 'active' AND p.stock > 0
    ORDER BY p.name ASC, p.sku ASC
")->fetchAll();

$autoInvNo = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invNo    = trim($_POST['invoice_no']    ?? '');
    $custName = trim($_POST['customer_name'] ?? '');
    $items    = $_POST['items'] ?? [];

    if (!$invNo || !$custName || empty($items['produk_id'])) {
        setFlash('error', 'Nomor invoice, nama pelanggan, dan minimal satu barang wajib diisi!');
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $custName)) {
        setFlash('error', 'Nama pelanggan hanya boleh berisi huruf dan spasi.');
    } else {
        try {
            $pdo->beginTransaction();
            $totalPrice = 0;
            for ($i = 0; $i < count($items['produk_id']); $i++) {
                $totalPrice += ((int)$items['qty'][$i] * (float)$items['price'][$i]);
            }
            $stmt = $pdo->prepare("INSERT INTO barang_keluar (invoice_no, customer_name, total_price, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$invNo, $custName, $totalPrice]);
            $outboundId = $pdo->lastInsertId();
            for ($i = 0; $i < count($items['produk_id']); $i++) {
                $pId = (int)$items['produk_id'][$i]; $qty = (int)$items['qty'][$i]; $price = (float)$items['price'][$i];
                if ($pId && $qty > 0) {
                    $pdo->prepare("INSERT INTO detail_barang_keluar (barang_keluar_id, produk_id, qty, price) VALUES (?, ?, ?, ?)")->execute([$outboundId, $pId, $qty, $price]);
                }
            }
            $pdo->commit();
            setFlash('success', "Nota $invNo berhasil dibuat! Lakukan verifikasi Quality Control (QC) untuk memproses barang keluar.");
            redirect("/pages/detail-barang-keluar.php?id=$outboundId");
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            setFlash('error', 'Gagal membuat nota barang keluar: ' . $e->getMessage());
        }
    }
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
            <a href="<?= BASE_URL ?>/pages/barang-keluar.php" class="hover:text-primary transition-colors">Barang Keluar</a>
            <span class="text-[0.65rem]">›</span><span>Buat Nota</span>
        </div>
        <h1 class="font-display text-[1.35rem] font-extrabold text-textmain leading-tight">Buat Nota Baru (Invoice)</h1>
    </div>
    <a href="<?= BASE_URL ?>/pages/barang-keluar.php"
       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
        <i data-lucide="arrow-left" style="width:15px;height:15px;"></i> Kembali
    </a>
</div>

<form method="POST" action="">
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-5 items-start">

        <div class="bg-white border border-border rounded-lg p-5 shadow-card">
            <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                <i data-lucide="shopping-bag" style="width:17px;height:17px;" class="text-danger"></i>
                Daftar Barang Keluar / Terjual
            </p>
            <div class="overflow-x-auto border border-border rounded-lg">
                <table class="w-full border-collapse text-[0.78rem]">
                    <thead>
                        <tr>
                            <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-2.5 px-3 text-left border-b border-border w-[50%]">Pilih Produk & Varian (Sisa Stok)</th>
                            <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-2.5 px-3 text-left border-b border-border w-[20%]">Kuantitas (Pcs)</th>
                            <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-2.5 px-3 text-left border-b border-border w-[20%]">Harga Jual (Rp)</th>
                            <th class="bg-slate-50 border-b border-border w-[10%]"></th>
                        </tr>
                    </thead>
                    <tbody id="outbound-tbody">
                        <tr class="outbound-row">
                            <td class="p-2 border-b border-slate-100 align-middle">
                                <select name="items[produk_id][]" class="w-full border border-border rounded-lg px-2 py-1.5 text-sm bg-white outline-none select-product form-select" required onchange="updatePrice(this)">
                                    <option value="">— Pilih Varian Produk —</option>
                                    <?php foreach ($semuaProduk as $p): ?>
                                    <?php $label = $p['name'] . " (" . ($p['color'] ?: 'No Color') . " - " . ($p['size'] ?: 'No Size') . ") — Stok: " . $p['stock'] . " unit"; ?>
                                    <option value="<?= $p['id'] ?>" data-price="<?= $p['price_sell'] ?>" data-max="<?= $p['stock'] ?>"><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="p-2 border-b border-slate-100 align-middle">
                                <input type="number" name="items[qty][]" class="w-full border border-border rounded-lg px-2 py-1.5 text-sm outline-none form-input field-qty" min="1" value="1" required onchange="validateStock(this)">
                            </td>
                            <td class="p-2 border-b border-slate-100 align-middle">
                                <input type="number" name="items[price][]" class="w-full border border-border rounded-lg px-2 py-1.5 text-sm outline-none form-input field-price" min="0" placeholder="0" required>
                            </td>
                            <td class="p-2 border-b border-slate-100 align-middle text-right">
                                <button type="button" class="w-7 h-7 flex items-center justify-center rounded-lg bg-danger-light text-danger border border-danger/20 hover:bg-danger hover:text-white transition-all cursor-pointer mx-auto" onclick="hapusBaris(this)">
                                    <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <button type="button" id="btn-tambah-baris"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[0.78rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all cursor-pointer">
                    <i data-lucide="plus" style="width:13px;height:13px;"></i> Tambah Baris Produk
                </button>
                <div class="text-[0.9rem] font-bold text-primary">
                    Total Estimasi: <span id="total-estimasi">Rp 0</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="bg-white border border-border rounded-lg p-5 shadow-card">
                <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                    <i data-lucide="info" style="width:17px;height:17px;" class="text-primary"></i> Informasi Transaksi
                </p>
                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Nomor Nota / Invoice *</label>
                    <input type="text" name="invoice_no" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none font-mono font-semibold form-input" value="<?= $autoInvNo ?>" required>
                </div>
                <div>
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Nama Pelanggan / Outlet *</label>
                    <input type="text" name="customer_name" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input"
                           placeholder="Contoh: Butik Cantik Indah" required
                           pattern="[a-zA-Z\s]+" title="Nama pelanggan hanya boleh berisi huruf dan spasi."
                           oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                </div>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all cursor-pointer">
                <i data-lucide="save" style="width:15px;height:15px;"></i> Buat Nota (Draft)
            </button>
            <a href="<?= BASE_URL ?>/pages/barang-keluar.php" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
                Batal
            </a>
        </div>

    </div>
</form>

</main>
<?php require_once __DIR__ . '/../components/footer.php'; ?>

<script>
function updatePrice(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const price = selectedOption.dataset.price || 0;
    const row = selectElement.closest('tr');
    const priceField = row.querySelector('.field-price');
    const qtyField = row.querySelector('.field-qty');
    if (priceField) priceField.value = price;
    validateStock(qtyField);
    hitungTotalEstimasi();
}

function validateStock(qtyElement) {
    const row = qtyElement.closest('tr');
    const select = row.querySelector('.select-product');
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption && selectedOption.value) {
        const maxStock = parseInt(selectedOption.dataset.max) || 0;
        const currentQty = parseInt(qtyElement.value) || 0;
        if (currentQty > maxStock) {
            tampilkanToast('warning', `Stok tidak mencukupi! Sisa stok hanya ${maxStock} unit.`);
            qtyElement.value = maxStock;
        }
    }
    hitungTotalEstimasi();
}

function hitungTotalEstimasi() {
    let total = 0;
    document.querySelectorAll('.outbound-row').forEach(row => {
        const qty = parseInt(row.querySelector('.field-qty').value) || 0;
        const price = parseFloat(row.querySelector('.field-price').value) || 0;
        total += (qty * price);
    });
    const formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(total);
    document.getElementById('total-estimasi').textContent = formatted.replace('IDR', 'Rp');
}

document.getElementById('btn-tambah-baris').addEventListener('click', function() {
    const tbody = document.getElementById('outbound-tbody');
    const firstRow = tbody.querySelector('.outbound-row');
    const newRow = firstRow.cloneNode(true);
    newRow.querySelector('.select-product').value = '';
    newRow.querySelector('.field-qty').value = '1';
    newRow.querySelector('.field-price').value = '';
    tbody.appendChild(newRow);
    if (typeof lucide !== 'undefined') lucide.createIcons();
});

function hapusBaris(buttonElement) {
    const tbody = document.getElementById('outbound-tbody');
    if (tbody.querySelectorAll('.outbound-row').length > 1) {
        buttonElement.closest('tr').remove();
        hitungTotalEstimasi();
    } else {
        tampilkanToast('warning', 'Minimal harus ada satu produk dalam nota!');
    }
}
</script>
