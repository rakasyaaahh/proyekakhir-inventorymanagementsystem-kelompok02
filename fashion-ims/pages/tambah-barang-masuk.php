<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /fashion-ims/login.php'); exit; }
require_once __DIR__ . '/../config/database.php';

$pageTitle    = 'Catat Barang Masuk';
$pageSubtitle = 'Form penerimaan stok barang dari Supplier';

$semuaSupplier = $pdo->query("SELECT id, name FROM supplier ORDER BY name")->fetchAll();
$semuaProduk   = $pdo->query("
    SELECT p.id, p.name, p.sku, p.size, p.color, p.price_buy, p.price_sell, c.name AS nama_kategori
    FROM produk p LEFT JOIN kategori c ON c.id = p.kategori_id
    WHERE p.status = 'active' ORDER BY p.name ASC, p.sku ASC
")->fetchAll();

$autoRecNo = 'REC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recNo      = trim($_POST['receive_no']   ?? '');
    $supId      = (int)($_POST['supplier_id']  ?? 0) ?: null;
    $receivedBy = trim($_POST['received_by']   ?? '');
    $recDate    = trim($_POST['received_date'] ?? date('Y-m-d'));
    $notes      = trim($_POST['notes']         ?? '');
    $items      = $_POST['items'] ?? [];

    if (!$recNo || !$receivedBy || empty($items['produk_id'])) {
        setFlash('error', 'Nomor penerimaan, penerima, dan minimal satu barang wajib diisi!');
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $receivedBy)) {
        setFlash('error', 'Nama penerima hanya boleh berisi huruf dan spasi!');
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO barang_masuk (receive_no, supplier_id, received_by, received_date, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$recNo, $supId, $receivedBy, $recDate, $notes]);
            $inboundId = $pdo->lastInsertId();
            for ($i = 0; $i < count($items['produk_id']); $i++) {
                $pId = (int)$items['produk_id'][$i]; $qty = (int)$items['qty'][$i]; $hBuy = (float)$items['price_buy'][$i];
                if ($pId && $qty > 0) {
                    $chkProd = $pdo->prepare("SELECT price_sell, name FROM produk WHERE id = ?");
                    $chkProd->execute([$pId]);
                    $prodData = $chkProd->fetch();
                    if ($prodData && $hBuy > (float)$prodData['price_sell']) {
                        throw new Exception("Harga beli untuk produk " . $prodData['name'] . " tidak boleh lebih mahal dari harga jual (" . rupiah($prodData['price_sell']) . ")!");
                    }
                    $pdo->prepare("INSERT INTO detail_barang_masuk (barang_masuk_id, produk_id, qty, price_buy) VALUES (?, ?, ?, ?)")->execute([$inboundId, $pId, $qty, $hBuy]);
                    $pdo->prepare("UPDATE produk SET stock = stock + ?, price_buy = ? WHERE id = ?")->execute([$qty, $hBuy, $pId]);
                }
            }
            $pdo->commit();
            setFlash('success', "Barang masuk dengan nomor $recNo berhasil dicatat & stok telah ditambahkan!");
            redirect('/pages/barang-masuk.php');
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            setFlash('error', 'Gagal mencatat barang masuk: ' . $e->getMessage());
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
            <a href="<?= BASE_URL ?>/pages/barang-masuk.php" class="hover:text-primary transition-colors">Barang Masuk</a>
            <span class="text-[0.65rem]">›</span><span>Catat Baru</span>
        </div>
        <h1 class="font-display text-[1.35rem] font-extrabold text-textmain leading-tight">Penerimaan Stok Baru</h1>
    </div>
    <a href="<?= BASE_URL ?>/pages/barang-masuk.php"
       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
        <i data-lucide="arrow-left" style="width:15px;height:15px;"></i> Kembali
    </a>
</div>

<form method="POST" action="">
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-5 items-start">

        <div class="bg-white border border-border rounded-lg p-5 shadow-card">
            <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                <i data-lucide="package-plus" style="width:17px;height:17px;" class="text-primary"></i>
                Daftar Barang yang Diterima
            </p>
            <div class="overflow-x-auto border border-border rounded-lg">
                <table class="w-full border-collapse text-[0.78rem]">
                    <thead>
                        <tr>
                            <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-2.5 px-3 text-left border-b border-border w-[50%]">Pilih Produk & Varian</th>
                            <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-2.5 px-3 text-left border-b border-border w-[20%]">Kuantitas (Pcs)</th>
                            <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-2.5 px-3 text-left border-b border-border w-[20%]">Harga Beli / HPP (Rp)</th>
                            <th class="bg-slate-50 border-b border-border w-[10%]"></th>
                        </tr>
                    </thead>
                    <tbody id="inbound-tbody">
                        <tr class="inbound-row">
                            <td class="p-2 border-b border-slate-100 align-middle">
                                <select name="items[produk_id][]" class="w-full border border-border rounded-lg px-2 py-1.5 text-sm bg-white outline-none select-product form-select" required onchange="updateHpp(this)">
                                    <option value="">— Pilih Varian Produk —</option>
                                    <?php foreach ($semuaProduk as $p): ?>
                                    <?php $label = $p['name'] . " (" . ($p['color'] ?: 'No Color') . " - " . ($p['size'] ?: 'No Size') . ")"; ?>
                                    <option value="<?= $p['id'] ?>" data-hpp="<?= $p['price_buy'] ?>" data-sell="<?= $p['price_sell'] ?>"><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="p-2 border-b border-slate-100 align-middle">
                                <input type="number" name="items[qty][]" class="w-full border border-border rounded-lg px-2 py-1.5 text-sm outline-none form-input" min="1" value="10" required>
                            </td>
                            <td class="p-2 border-b border-slate-100 align-middle">
                                <input type="number" name="items[price_buy][]" class="w-full border border-border rounded-lg px-2 py-1.5 text-sm outline-none form-input field-hpp" min="0" placeholder="0" required>
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
            <div class="mt-3">
                <button type="button" id="btn-tambah-baris"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[0.78rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all cursor-pointer">
                    <i data-lucide="plus" style="width:13px;height:13px;"></i> Tambah Baris Produk
                </button>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="bg-white border border-border rounded-lg p-5 shadow-card">
                <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                    <i data-lucide="info" style="width:17px;height:17px;" class="text-primary"></i> Informasi Logistik
                </p>

                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Nomor Terima / PO *</label>
                    <input type="text" name="receive_no" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none font-mono font-semibold form-input" value="<?= $autoRecNo ?>" required>
                </div>
                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Supplier Pengirim</label>
                    <select name="supplier_id" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-select">
                        <option value="">— Pilih Supplier —</option>
                        <?php foreach ($semuaSupplier as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Tanggal Diterima *</label>
                    <input type="date" name="received_date" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Diterima Oleh *</label>
                    <input type="text" name="received_by" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input"
                           value="<?= e($_SESSION['user_name'] ?? 'Staff Gudang') ?>"
                           pattern="[A-Za-z\s]+" title="Nama penerima hanya boleh berisi huruf dan spasi" required>
                </div>
                <div>
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Catatan Tambahan</label>
                    <textarea name="notes" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-textarea resize-y min-h-[90px]" placeholder="Contoh: Kurir J&T Cargo, kondisi kardus aman."></textarea>
                </div>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all cursor-pointer">
                <i data-lucide="save" style="width:15px;height:15px;"></i> Simpan Penerimaan
            </button>
            <a href="<?= BASE_URL ?>/pages/barang-masuk.php" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
                Batal
            </a>
        </div>

    </div>
</form>

</main>
<?php require_once __DIR__ . '/../components/footer.php'; ?>

<script>
function updateHpp(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const hpp = selectedOption.dataset.hpp || 0;
    const row = selectElement.closest('tr');
    const hppField = row.querySelector('.field-hpp');
    if (hppField) hppField.value = hpp;
}

document.getElementById('btn-tambah-baris').addEventListener('click', function() {
    const tbody = document.getElementById('inbound-tbody');
    const firstRow = tbody.querySelector('.inbound-row');
    const newRow = firstRow.cloneNode(true);
    newRow.querySelector('.select-product').value = '';
    newRow.querySelector('input[type="number"]').value = '10';
    newRow.querySelector('.field-hpp').value = '';
    tbody.appendChild(newRow);
    if (typeof lucide !== 'undefined') lucide.createIcons();
});

function hapusBaris(buttonElement) {
    const tbody = document.getElementById('inbound-tbody');
    if (tbody.querySelectorAll('.inbound-row').length > 1) {
        buttonElement.closest('tr').remove();
    } else {
        tampilkanToast('warning', 'Minimal harus ada satu baris produk yang diterima!');
    }
}

document.querySelector('form').addEventListener('submit', function(e) {
    const receivedByInput = document.getElementsByName('received_by')[0];
    if (receivedByInput) {
        const value = receivedByInput.value.trim();
        if (!/^[a-zA-Z\s]+$/.test(value)) {
            e.preventDefault();
            tampilkanToast('error', 'Nama penerima hanya boleh berisi huruf dan spasi!');
            receivedByInput.focus();
            return;
        }
    }
    
    const rows = document.querySelectorAll('.inbound-row');
    for (let row of rows) {
        const select = row.querySelector('.select-product');
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const sellPrice = parseFloat(selectedOption.dataset.sell || 0);
            const buyPrice = parseFloat(row.querySelector('.field-hpp').value || 0);
            if (buyPrice > sellPrice) {
                e.preventDefault();
                tampilkanToast('error', `Harga beli untuk ${selectedOption.text.split(' (')[0]} tidak boleh lebih mahal dari harga jual!`);
                row.querySelector('.field-hpp').focus();
                return;
            }
        }
    }
});
</script>
