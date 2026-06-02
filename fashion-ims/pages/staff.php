<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /fashion-ims/login.php'); exit; }
require_once __DIR__ . '/../config/database.php';
if (($_SESSION['user_role'] ?? 'admin') !== 'admin') { setFlash('error', 'Akses ditolak. Halaman ini hanya untuk Administrator.'); redirect('/pages/dasbor.php'); }

$pageTitle    = 'Kelola Staff';
$pageSubtitle = 'Manajemen akun login Staff Gudang';
$editData     = null;

if (isset($_GET['hapus'])) {
    $hapusId = (int)$_GET['hapus'];
    if ($hapusId === (int)$_SESSION['user_id']) { setFlash('error', 'Anda tidak dapat menghapus akun Anda sendiri.'); }
    else {
        $cek = $pdo->prepare("SELECT role, name FROM user WHERE id = ?"); $cek->execute([$hapusId]); $u = $cek->fetch();
        if ($u && $u['role'] === 'staff') { $pdo->prepare("DELETE FROM user WHERE id = ?")->execute([$hapusId]); setFlash('success', "Akun staff \"{$u['name']}\" berhasil dihapus."); }
        else { setFlash('error', 'Akun tidak ditemukan atau bukan akun staff.'); }
    }
    redirect('/pages/staff.php');
}

if (isset($_GET['edit'])) {
    $q = $pdo->prepare("SELECT * FROM user WHERE id = ? AND role = 'staff'"); $q->execute([(int)$_GET['edit']]); $editData = $q->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffId = (int)($_POST['id'] ?? 0); $nama = trim($_POST['nama'] ?? ''); $email = trim($_POST['email'] ?? ''); $password = trim($_POST['password'] ?? '');
    if (!$nama || !$email) { setFlash('error', 'Nama dan email wajib diisi.'); }
    elseif (!preg_match('/^[a-zA-Z\s]+$/', $nama)) { setFlash('error', 'Nama staff hanya boleh berisi huruf dan spasi.'); }
    else {
        try {
            if ($staffId) {
                $cekEmail = $pdo->prepare("SELECT id FROM user WHERE email = ? AND id != ?"); $cekEmail->execute([$email, $staffId]);
                if ($cekEmail->fetch()) { setFlash('error', 'Email sudah digunakan oleh akun lain.'); }
                else {
                    if (!empty($password)) { $pdo->prepare("UPDATE user SET name = ?, email = ?, password = ? WHERE id = ? AND role = 'staff'")->execute([$nama, $email, password_hash($password, PASSWORD_DEFAULT), $staffId]); }
                    else { $pdo->prepare("UPDATE user SET name = ?, email = ? WHERE id = ? AND role = 'staff'")->execute([$nama, $email, $staffId]); }
                    setFlash('success', "Akun staff \"$nama\" berhasil diperbarui."); redirect('/pages/staff.php');
                }
            } else {
                if (empty($password)) { setFlash('error', 'Password wajib diisi untuk akun staff baru.'); }
                else {
                    $cekEmail = $pdo->prepare("SELECT id FROM user WHERE email = ?"); $cekEmail->execute([$email]);
                    if ($cekEmail->fetch()) { setFlash('error', 'Email sudah digunakan oleh akun lain.'); }
                    else { $pdo->prepare("INSERT INTO user (name, email, password, role) VALUES (?, ?, ?, 'staff')")->execute([$nama, $email, password_hash($password, PASSWORD_DEFAULT)]); setFlash('success', "Akun staff \"$nama\" berhasil ditambahkan."); redirect('/pages/staff.php'); }
                }
            }
        } catch (PDOException $e) { setFlash('error', 'Gagal menyimpan data staff: ' . $e->getMessage()); }
    }
}

$semuaStaff = $pdo->query("SELECT * FROM user WHERE role = 'staff' ORDER BY name")->fetchAll();

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
            <span class="text-[0.65rem]">›</span><span>Kelola Staff</span>
        </div>
        <h1 class="font-display text-[1.35rem] font-extrabold text-textmain leading-tight">Kelola Akun Staff</h1>
        <p class="text-[0.78rem] text-muted mt-0.5"><?= count($semuaStaff) ?> akun staff aktif terdaftar</p>
    </div>
    <?php if (!$editData): ?>
    <button id="btn-toggle-form" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all cursor-pointer">
        <i data-lucide="user-plus" style="width:15px;height:15px;"></i> Tambah Staff Baru
    </button>
    <?php endif; ?>
</div>

<div id="panel-form" class="bg-white border border-border rounded-lg p-5 mb-5 shadow-card <?= $editData ? '' : 'hidden' ?>">
    <p class="font-display text-[0.875rem] font-bold text-textmain mb-4 flex items-center gap-2">
        <?php if ($editData): ?>
            <i data-lucide="pencil" style="width:17px;height:17px;" class="text-secondary"></i> Edit Akun Staff
        <?php else: ?>
            <i data-lucide="user-plus" style="width:17px;height:17px;" class="text-primary"></i> Tambah Akun Staff Baru
        <?php endif; ?>
    </p>
    <form method="POST" action="">
        <?php if ($editData): ?><input type="hidden" name="id" value="<?= $editData['id'] ?>"><?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Nama Lengkap *</label>
                <input type="text" name="nama" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input"
                       value="<?= e($editData['name'] ?? '') ?>" placeholder="Nama lengkap staff"
                       pattern="[a-zA-Z\s]+" title="Nama hanya boleh berisi huruf dan spasi." required>
            </div>
            <div>
                <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Alamat Email *</label>
                <input type="email" name="email" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" value="<?= e($editData['email'] ?? '') ?>" placeholder="emailstaff@fashionims.com" required>
            </div>
            <div>
                <label class="block text-[0.78rem] font-semibold text-textmain mb-1.5">Password <?= $editData ? '(Kosongkan jika tidak diubah)' : '*' ?></label>
                <input type="password" name="password" class="w-full bg-white border border-border rounded-lg px-3.5 py-[0.58rem] text-sm outline-none form-input" placeholder="••••••••" <?= $editData ? '' : 'required' ?>>
            </div>
        </div>
        <div class="flex gap-2 mt-4">
            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-primary text-white hover:bg-primary-dark transition-all cursor-pointer">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                <?= $editData ? 'Simpan Perubahan' : 'Buat Akun Staff' ?>
            </button>
            <a href="<?= BASE_URL ?>/pages/staff.php" class="inline-flex items-center px-4 py-2 rounded-lg text-[0.85rem] font-medium bg-white border border-border text-textmain hover:border-primary hover:text-primary transition-all">Batal</a>
        </div>
    </form>
</div>

<div class="bg-white border border-border rounded-lg shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 pl-5 pr-3 text-left border-b border-border">#</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Nama Staff</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Email</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Hak Akses</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 px-3 text-left border-b border-border">Tanggal Dibuat</th>
                    <th class="bg-slate-50 text-muted text-[0.72rem] font-semibold uppercase tracking-wide py-3 pr-5 pl-3 text-right border-b border-border">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($semuaStaff)): ?>
                <tr><td colspan="6" class="py-12 text-center text-muted">
                    <i data-lucide="users" style="width:48px;height:48px;opacity:0.3;margin:0 auto 0.75rem;display:block;"></i>
                    <p>Belum ada akun staff terdaftar. Buat akun staff pertama!</p>
                </td></tr>
                <?php else: ?>
                <?php foreach ($semuaStaff as $i => $s): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 pl-5 pr-3 border-b border-slate-100 align-middle text-slate-400"><?= $i + 1 ?></td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 bg-primary-light rounded-lg flex items-center justify-center shrink-0 text-primary">
                                <i data-lucide="user" style="width:15px;height:15px;"></i>
                            </div>
                            <p class="font-medium text-textmain"><?= e($s['name']) ?></p>
                        </div>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.82rem] text-slate-500"><?= e($s['email']) ?></td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-primary-light text-primary">Staff Gudang</span>
                    </td>
                    <td class="py-3 px-3 border-b border-slate-100 align-middle text-[0.82rem] text-slate-500"><?= date('d M Y H:i', strtotime($s['created_at'])) ?></td>
                    <td class="py-3 pr-5 pl-3 border-b border-slate-100 align-middle text-right">
                        <div class="flex justify-end gap-1.5">
                            <a href="?edit=<?= $s['id'] ?>" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-border text-secondary hover:border-secondary transition-all" title="Edit Akun"><i data-lucide="pencil" style="width:13px;height:13px;"></i></a>
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-danger-light text-danger border border-danger/20 hover:bg-danger hover:text-white transition-all cursor-pointer" title="Hapus Akun" data-hapus-url="<?= BASE_URL ?>/pages/staff.php?hapus=<?= $s['id'] ?>" data-hapus-nama="Staff: <?= e($s['name']) ?>"><i data-lucide="trash-2" style="width:13px;height:13px;"></i></button>
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
