<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /fashion-ims/pages/dasbor.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — FashionIMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary:   { DEFAULT: '#5D6B6B', dark: '#475353', light: '#E5EFEF' },
                    secondary: { DEFAULT: '#B0D3D3', dark: '#5D6B6B' },
                    accent:    '#F7CBCA',
                    danger:    { DEFAULT: '#C05C5C', light: '#FDF2F2' },
                    surface:   '#F1F7F7',
                    border:    '#D5E5E5',
                    textmain:  '#2C3535',
                    muted:     '#7F9090',
                },
                fontFamily: {
                    sans:    ['Inter', 'sans-serif'],
                    display: ['"Plus Jakarta Sans"', 'sans-serif'],
                },
            }
        }
    }
    </script>
    <link rel="stylesheet" href="/fashion-ims/assets/css/custom.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-surface font-sans text-textmain m-0">

<div class="min-h-screen flex">

    <div class="bg-primary flex-1 p-12 flex flex-col justify-center relative overflow-hidden max-lg:hidden">

        <div class="flex items-center gap-3 mb-10 relative z-10">
            <div class="w-12 h-12 bg-secondary rounded-xl flex items-center justify-center text-white border border-white/15">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.38 3.46L16 6.14V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2.14L3.62 3.46a2 2 0 0 0-2.38.68l-1 1.5a2 2 0 0 0 .33 2.6L5 11v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8l4.38-2.76a2 2 0 0 0 .33-2.6l-1-1.5a2 2 0 0 0-2.33-.68z"></path>
                </svg>
            </div>
            <div>
                <h1 class="font-display text-xl font-extrabold text-white leading-tight">InventoryKita</h1>
                <small class="text-[0.68rem] text-white/55 block">Inventory Management System</small>
            </div>
        </div>

        <h2 class="font-display text-4xl font-extrabold text-white leading-tight mb-4 relative z-10">
            Kelola Inventori<br>
            <span class="text-accent">Fashion</span> Anda
        </h2>
        <p class="text-[0.9rem] text-white/65 max-w-sm leading-relaxed relative z-10">
            Platform manajemen inventori fashion yang modern, efisien, dan mudah digunakan untuk bisnis Anda.
        </p>

        <ul class="mt-8 space-y-3 relative z-10">
            <?php foreach ([
                'CRUD produk lengkap dengan upload foto',
                'Dashboard statistik inventori real-time',
                'Peringatan otomatis saat stok rendah',
                'Pencarian dan filter produk canggih',
                'Manajemen kategori dan supplier',
            ] as $f): ?>
            <li class="flex items-center gap-3 text-[0.85rem] text-white/75">
                <span class="w-5 h-5 bg-secondary rounded-full flex items-center justify-center text-[0.68rem] font-bold text-white shrink-0">✓</span>
                <?= $f ?>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="absolute -bottom-20 -right-20 w-72 h-72 bg-secondary/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -top-10 -left-10 w-48 h-48 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <div class="w-full lg:w-[460px] bg-white flex items-center justify-center p-8">
        <div class="w-full max-w-[360px]">

            <h2 class="font-display text-2xl font-extrabold text-slate-800 mb-1">Selamat Datang</h2>
            <p class="text-[0.875rem] text-slate-500 mb-7">Masuk untuk mengelola inventori Anda</p>

            <?php if ($flash && $flash['type'] === 'error'): ?>
            <div class="bg-danger-light border border-danger/30 rounded-lg px-4 py-3 mb-5 text-[0.85rem] text-danger flex items-center gap-2">
                <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
                <?= e($flash['message']) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="/fashion-ims/actions/auth-login.php" class="space-y-4">

                <div>
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5" for="email">Email</label>
                    <input type="email" id="email" name="email"
                           class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm font-[Inter] text-textmain transition-colors outline-none placeholder:text-slate-300 form-input"
                           placeholder="admin@fashionims.com"
                           required>
                </div>

                <div>
                    <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5" for="password">Password</label>
                    <input type="password" id="password" name="password"
                           class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm font-[Inter] text-textmain transition-colors outline-none placeholder:text-slate-300 form-input"
                           placeholder="••••••••"
                           required>
                </div>

                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-primary hover:bg-primary-dark text-white rounded-lg py-[0.7rem] text-[0.9rem] font-medium transition-all duration-150 mt-1 cursor-pointer">
                    <i data-lucide="log-in" style="width:16px;height:16px;"></i>
                    Masuk ke Dashboard
                </button>

            </form>
        </div>
    </div>

</div>

<script>
lucide.createIcons();

document.getElementById('demo-admin').addEventListener('click', function() {
    document.getElementById('email').value = 'admin@fashionims.com';
    document.getElementById('password').value = 'admin123';
});
document.getElementById('demo-staff').addEventListener('click', function() {
    document.getElementById('email').value = 'staff@fashionims.com';
    document.getElementById('password').value = 'staff123';
});
</script>
</body>
</html>
