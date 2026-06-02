<?php
$jmlStokRendah = 0;
try {
    $jmlStokRendah = $pdo->query(
        "SELECT COUNT(*) FROM produk WHERE stock <= stock_min AND status = 'active'"
    )->fetchColumn();
} catch (Exception $e) {
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<header class="h-[60px] bg-white border-b border-border px-6 flex items-center justify-between sticky top-0 z-40">
    <div class="flex items-center gap-3">
        <button id="btn-toggle-sidebar" class="lg:hidden p-1.5 -ml-1.5 text-muted hover:text-primary transition-colors">
            <i data-lucide="menu" style="width:20px;height:20px;"></i>
        </button>
        <div>
            <h2 class="font-display text-[1rem] font-bold text-textmain"><?= e($pageTitle ?? 'Dashboard') ?></h2>
            <?php if (!empty($pageSubtitle)): ?>
                <p class="text-[0.72rem] text-muted"><?= e($pageSubtitle) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <a href="<?= BASE_URL ?>/pages/produk.php?filter=stokrendah"
           class="relative w-9 h-9 bg-surface border border-border rounded-lg flex items-center justify-center text-muted hover:border-primary hover:text-primary transition-all duration-150"
           title="Produk Stok Rendah">
            <i data-lucide="bell" style="width:16px;height:16px;"></i>
            <?php if ($jmlStokRendah > 0): ?>
                <span class="absolute -top-1.5 -right-1.5 bg-danger text-white text-[0.6rem] font-bold w-4 h-4 rounded-full flex items-center justify-center border-2 border-white">
                    <?= $jmlStokRendah ?>
                </span>
            <?php endif; ?>
        </a>

        <div class="text-[0.75rem] text-muted bg-surface border border-border rounded-lg px-3 py-[0.4rem]">
            <?= date('d M Y') ?>
        </div>
    </div>
</header>

<?php if ($flash): ?>
    <div id="flash-data"
         data-type="<?= e($flash['type']) ?>"
         data-message="<?= e($flash['message']) ?>"
         class="hidden">
    </div>
<?php endif; ?>
