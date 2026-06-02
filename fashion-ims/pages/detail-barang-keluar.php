<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /fashion-ims/login.php'); exit; }
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('/pages/barang-keluar.php');

$stmt = $pdo->prepare("SELECT * FROM barang_keluar WHERE id = ?"); $stmt->execute([$id]); $order = $stmt->fetch();
if (!$order) { setFlash('error', 'Nota tidak ditemukan.'); redirect('/pages/barang-keluar.php'); }

$pageTitle    = 'Detail Nota & QC';
$pageSubtitle = 'Nota: ' . $order['invoice_no'];

$stmtItems = $pdo->prepare("
    SELECT oi.*, p.name, p.sku, p.size, p.color, p.stock AS stok_sekarang, p.bin_location, c.name AS kategori
    FROM detail_barang_keluar oi
    LEFT JOIN produk p ON p.id = oi.produk_id
    LEFT JOIN kategori c ON c.id = p.kategori_id
    WHERE oi.barang_keluar_id = ?
");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_qc'])) {
    if ($order['status'] !== 'pending') { setFlash('error', 'Nota ini sudah diproses sebelumnya!'); }
    else {
        try {
            $pdo->beginTransaction();
            $tempStock = [];
            foreach ($items as $item) {
                $pId = (int)$item['produk_id'];
                if (!isset($tempStock[$pId])) {
                    $stmtCheck = $pdo->prepare("SELECT stock, name FROM produk WHERE id = ? FOR UPDATE");
                    $stmtCheck->execute([$pId]);
                    $dbProd = $stmtCheck->fetch();
                    $tempStock[$pId] = [
                        'stock' => (int)($dbProd['stock'] ?? 0),
                        'name'  => $dbProd['name'] ?? 'Produk'
                    ];
                }
                if ($tempStock[$pId]['stock'] < $item['qty']) {
                    throw new Exception("Stok untuk produk " . $tempStock[$pId]['name'] . " tidak mencukupi! Dibutuhkan: " . $item['qty'] . ", Sisa: " . $tempStock[$pId]['stock'] . " pcs.");
                }
                $tempStock[$pId]['stock'] -= $item['qty'];
            }
            foreach ($items as $item) { $pdo->prepare("UPDATE produk SET stock = stock - ? WHERE id = ?")->execute([$item['qty'], $item['produk_id']]); }
            $qcBy = trim($_POST['qc_by'] ?? $_SESSION['user_name']);
            $pdo->prepare("UPDATE barang_keluar SET status = 'shipped', qc_by = ?, qc_date = ? WHERE id = ?")->execute([$qcBy, date('Y-m-d H:i:s'), $id]);
            $pdo->commit();
            setFlash('success', "Quality Control (QC) Pass Berhasil! Stok varian produk telah dipotong dan barang siap dikirim!");
            redirect("/pages/detail-barang-keluar.php?id=$id");
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            setFlash('error', $e->getMessage());
            redirect("/pages/detail-barang-keluar.php?id=$id");
        }
    }
}

if ($order['status'] === 'pending')       { $badge = 'bg-warning-light text-warning'; $label = 'Pending QC'; }
elseif ($order['status'] === 'shipped')   { $badge = 'bg-success-light text-success'; $label = 'QC Passed & Dikirim'; }
else                                      { $badge = 'bg-danger-light text-danger';   $label = 'Dibatalkan'; }

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
            <span class="text-[0.65rem]">›</span><span>Detail Nota</span>
        </div>
        <h1 class="font-display text-[1.35rem] font-extrabold text-textmain leading-tight">Nota Keluar & Verifikasi QC</h1>
        <p class="text-[0.78rem] text-muted mt-0.5">Pemeriksaan kualitas & transaksi stok keluar</p>
    </div>
    <a href="<?= BASE_URL ?>/pages/barang-keluar.php"
       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">
        <i data-lucide="arrow-left" style="width:15px;height:15px;"></i> Kembali
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-5 items-start">

    <div class="flex flex-col gap-5">

        <div class="bg-white border border-border rounded-lg p-5 shadow-card">
            <div class="flex justify-between items-start pb-4 mb-4 border-b border-border">
                <div>
                    <p class="text-[0.7rem] text-muted uppercase tracking-wide">Nomor Invoice</p>
                    <h2 class="font-mono text-2xl font-extrabold text-danger"><?= e($order['invoice_no']) ?></h2>
                </div>
                <div class="text-right">
                    <p class="text-[0.7rem] text-muted uppercase tracking-wide block mb-1">Status</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.85rem] font-semibold <?= $badge ?>"><?= $label ?></span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[0.72rem] text-muted uppercase tracking-wide mb-1">Pelanggan / Outlet</p>
                    <p class="font-bold text-[0.95rem] text-textmain"><?= e($order['customer_name']) ?></p>
                </div>
                <div>
                    <p class="text-[0.72rem] text-muted uppercase tracking-wide mb-1">Tanggal Dibuat</p>
                    <p class="font-semibold text-[0.9rem] text-textmain"><?= date('d M Y H:i', strtotime($order['created_at'])) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-border rounded-lg p-5 shadow-card">
            <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
                <i data-lucide="list" style="width:17px;height:17px;" class="text-danger"></i> Item Barang Keluar
            </p>
            <div class="overflow-x-auto border border-border rounded-lg">
                <table class="w-full border-collapse text-[0.8rem]">
                    <thead>
                        <tr>
                            <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-2.5 pl-5 pr-3 text-left border-b border-border">Barang & Varian</th>
                            <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-2.5 px-3 text-left border-b border-border">Lokasi Rak</th>
                            <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-2.5 px-3 text-left border-b border-border">Harga Jual</th>
                            <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-2.5 px-3 text-left border-b border-border">Kuantitas</th>
                            <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-2.5 px-3 text-left border-b border-border">Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 pl-5 pr-3 border-b border-slate-100 align-middle">
                                <p class="font-semibold text-textmain"><?= e($item['name']) ?></p>
                                <span class="inline-block px-1.5 py-0.5 rounded text-[0.68rem] font-semibold bg-surface border border-border text-muted m-px"><?= e($item['color'] ?: 'No Color') ?></span>
                                <span class="inline-block px-1.5 py-0.5 rounded text-[0.68rem] font-semibold bg-surface border border-border text-muted m-px"><?= e($item['size'] ?: 'No Size') ?></span>
                            </td>
                            <td class="py-3 px-3 border-b border-slate-100 align-middle">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-primary-light text-primary font-mono"><?= e($item['bin_location'] ?: 'Rak Umum') ?></span>
                            </td>
                            <td class="py-3 px-3 border-b border-slate-100 align-middle text-textmain"><?= rupiah($item['price']) ?></td>
                            <td class="py-3 px-3 border-b border-slate-100 align-middle font-semibold text-textmain"><?= $item['qty'] ?> pcs</td>
                            <td class="py-3 px-3 border-b border-slate-100 align-middle font-bold text-primary"><?= rupiah($item['qty'] * $item['price']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="bg-slate-50 font-bold">
                            <td colspan="4" class="text-right pr-4 py-3 text-[0.9rem] text-textmain">Total Nota:</td>
                            <td class="py-3 px-3 text-base text-danger"><?= rupiah($order['total_price']) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="flex flex-col gap-4">

        <?php if ($order['status'] === 'pending'): ?>

        <div class="bg-white border border-border border-l-4 border-l-warning rounded-lg p-5 shadow-card">
            <p class="font-display text-[0.875rem] font-bold text-warning mb-1 flex items-center gap-2">
                <i data-lucide="shield-check" style="width:17px;height:17px;"></i> Quality Control (QC) Pass
            </p>
            <p class="text-[0.72rem] text-muted mb-4">Centang pemeriksaan kondisi fisik pakaian untuk memverifikasi kelayakan kirim:</p>

            <form method="POST" action="">

                <div class="flex flex-col gap-3 mb-5">
                    <?php foreach ([
                        ['Kondisi Fisik Bersih', 'Bebas noda kotor, robek, atau kancing lepas.'],
                        ['Kesesuaian SKU & Varian', 'Ukuran (Size) & Warna sesuai dengan yang tertera di nota.'],
                        ['Kuantitas Sesuai', 'Jumlah fisik barang sama dengan kuantitas order.'],
                        ['Lokasi Rak Fisik Benar', 'Barang diambil dari Rak Lokasi Bin yang tepat.'],
                    ] as [$title, $desc]): ?>
                    <label class="flex items-start gap-3 cursor-pointer text-[0.8rem]">
                        <input type="checkbox" class="qc-check mt-0.5" required>
                        <div>
                            <p class="font-semibold text-textmain"><?= $title ?></p>
                            <p class="text-[0.68rem] text-muted"><?= $desc ?></p>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div class="mb-4">
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Diverifikasi Oleh *</label>
                    <input type="text" name="qc_by" class="w-full bg-white border border-border rounded-lg px-3 py-[0.58rem] text-[0.8rem] outline-none form-input"
                           value="<?= e($_SESSION['user_name'] ?? 'Staff QC') ?>" required>
                </div>

                <input type="hidden" name="proses_qc" value="1">
                <button type="submit" id="btn-submit-qc" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-[0.85rem] font-semibold border border-warning text-warning bg-white hover:bg-primary hover:text-white hover:border-primary transition-all duration-150 cursor-pointer disabled:bg-slate-100 disabled:text-slate-400 disabled:border-slate-200 disabled:cursor-not-allowed" disabled>
                    <i data-lucide="truck" style="width:15px;height:15px;"></i>
                    Lolos QC & Kirim Barang
                </button>
            </form>
        </div>

        <?php else: ?>

        <div class="bg-white border border-border border-l-4 border-l-success rounded-lg p-5 shadow-card">
            <p class="font-display text-[0.875rem] font-bold text-success mb-2 flex items-center gap-2">
                <i data-lucide="check-circle" style="width:17px;height:17px;"></i> QC Passed & Shipped
            </p>
            <p class="text-[0.78rem] text-muted leading-relaxed mb-4">
                Nota barang keluar ini telah berhasil divalidasi melalui proses Quality Control fisik.
            </p>
            <div class="bg-slate-50 border border-border rounded-lg p-3 text-[0.78rem] space-y-2">
                <div>
                    <p class="font-semibold text-textmain">Verifikator:</p>
                    <p class="text-muted"><?= e($order['qc_by'] ?: 'Staff QC') ?></p>
                </div>
                <div>
                    <p class="font-semibold text-textmain">Waktu Verifikasi:</p>
                    <p class="text-muted"><?= date('d M Y H:i', strtotime($order['qc_date'])) ?></p>
                </div>
            </div>
        </div>

        <?php endif; ?>

    </div>
</div>

</main>
<?php require_once __DIR__ . '/../components/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.qc-check');
    const submitBtn = document.getElementById('btn-submit-qc');

    if (checkboxes.length > 0 && submitBtn) {
        checkboxes.forEach(chk => {
            chk.addEventListener('change', function() {
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                submitBtn.disabled = !allChecked;
            });
        });
    }
});
</script>
