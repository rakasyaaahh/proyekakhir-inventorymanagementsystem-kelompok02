<?php
$halamanAktif = basename($_SERVER['PHP_SELF']);

$menu = [
    [
        'ikon'   => 'layout-dashboard',
        'label'  => 'Dashboard',
        'url'    => BASE_URL . '/pages/dasbor.php',
        'aktif'  => ['dasbor.php'],
    ],
    [
        'ikon'   => 'package',
        'label'  => 'Produk',
        'url'    => BASE_URL . '/pages/produk.php',
        'aktif'  => ['produk.php', 'tambah-produk.php', 'ubah-produk.php', 'detail-produk.php'],
    ],
    [
        'ikon'   => 'tags',
        'label'  => 'Kategori',
        'url'    => BASE_URL . '/pages/kategori.php',
        'aktif'  => ['kategori.php'],
    ],
    [
        'ikon'   => 'arrow-down-left',
        'label'  => 'Barang Masuk',
        'url'    => BASE_URL . '/pages/barang-masuk.php',
        'aktif'  => ['barang-masuk.php', 'tambah-barang-masuk.php'],
    ],
    [
        'ikon'   => 'arrow-up-right',
        'label'  => 'Barang Keluar',
        'url'    => BASE_URL . '/pages/barang-keluar.php',
        'aktif'  => ['barang-keluar.php', 'tambah-barang-keluar.php', 'detail-barang-keluar.php'],
    ],
    [
        'ikon'   => 'truck',
        'label'  => 'Supplier',
        'url'    => BASE_URL . '/pages/pemasok.php',
        'aktif'  => ['pemasok.php'],
        'role'   => ['admin'],
    ],
    [
        'ikon'   => 'users',
        'label'  => 'Kelola Staff',
        'url'    => BASE_URL . '/pages/staff.php',
        'aktif'  => ['staff.php'],
        'role'   => ['admin'],
    ],
];
?>

<aside class="w-[240px] bg-primary fixed top-0 left-0 h-screen flex flex-col z-50 shrink-0 -translate-x-full lg:translate-x-0 transition-transform duration-200" id="sidebar">
    <div class="px-4 py-5 border-b border-white/10 flex items-center gap-3">
        <div class="w-9 h-9 bg-secondary rounded-lg flex items-center justify-center shrink-0 border border-white/15 text-white">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.38 3.46L16 6.14V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2.14L3.62 3.46a2 2 0 0 0-2.38.68l-1 1.5a2 2 0 0 0 .33 2.6L5 11v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8l4.38-2.76a2 2 0 0 0 .33-2.6l-1-1.5a2 2 0 0 0-2.33-.68z"></path>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <h1 class="font-display text-[0.95rem] font-extrabold text-white leading-tight">InventoryKita</h1>
            <small class="text-[0.64rem] text-white/50 block">Inventory System</small>
        </div>
        <button id="btn-close-sidebar" class="lg:hidden text-white/70 hover:text-white p-1">
            <i data-lucide="x" style="width:20px;height:20px;"></i>
        </button>
    </div>

    <nav class="flex-1 px-3 py-4 overflow-y-auto">
        <p class="text-[0.64rem] font-semibold uppercase tracking-widest text-white/35 px-2 mb-2">Menu</p>
        <?php foreach ($menu as $item): ?>
            <?php
            if (isset($item['role']) && !in_array($_SESSION['user_role'] ?? 'admin', $item['role'])) {
                continue;
            }
            $aktif = in_array($halamanAktif, $item['aktif']);
            ?>
            <a href="<?= $item['url'] ?>"
               class="flex items-center gap-3 px-3 py-[0.55rem] rounded-lg text-[0.855rem] font-medium transition-all duration-150 mb-0.5
                      <?= $aktif
                          ? 'bg-secondary text-white font-semibold'
                          : 'text-white/70 hover:bg-white/10 hover:text-white' ?>">
                <i data-lucide="<?= $item['ikon'] ?>" style="width:17px;height:17px;flex-shrink:0;"></i>
                <?= $item['label'] ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="p-3 border-t border-white/10">
        <div class="flex items-center gap-3 px-3 py-2 bg-white/8 rounded-lg">
            <div class="w-8 h-8 bg-secondary rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[0.8rem] font-medium text-white truncate"><?= e($_SESSION['user_name'] ?? 'Admin') ?></p>
                <p class="text-[0.64rem] text-white/45 capitalize"><?= e($_SESSION['user_role'] ?? 'admin') ?></p>
            </div>
            <a href="<?= BASE_URL ?>/logout.php"
               class="text-white/40 hover:text-red-300 transition-colors p-1 flex"
               title="Keluar">
                <i data-lucide="log-out" style="width:15px;height:15px;"></i>
            </a>
        </div>
    </div>
</aside>

<div id="sidebar-backdrop" class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden"></div>
