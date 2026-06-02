<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /fashion-ims/login.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/pages/produk.php');

$aksi       = $_POST['aksi']        ?? 'tambah';   
$id         = (int)($_POST['id']    ?? 0);
$nama       = trim($_POST['name']   ?? '');
$sku        = trim($_POST['sku']    ?? '');
if ($aksi === 'tambah' && empty($sku)) {
    $sku = 'FMS-PRD-' . strtoupper(substr(uniqid(), -8));
}
$katId      = (int)($_POST['kategori_id'] ?? 0) ?: null;
$supId      = (int)($_POST['supplier_id'] ?? 0) ?: null;
$ukuran     = trim($_POST['size']        ?? '');
$warna      = trim($_POST['color']       ?? '');
$hargaBeli  = (float)($_POST['price_buy']  ?? 0);
$hargaJual  = (float)($_POST['price_sell'] ?? 0);
$stok       = (int)($_POST['stock']      ?? 0);
$stokMin    = (int)($_POST['stock_min']  ?? 5);
$deskripsi  = trim($_POST['description'] ?? '');
$status     = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';
$fotoLama   = trim($_POST['foto_lama']  ?? ''); 
$bin        = trim($_POST['bin_location'] ?? '');

if (!$nama) {
    setFlash('error', 'Nama produk/model tidak boleh kosong.');
    redirect($aksi === 'ubah' ? "/pages/ubah-produk.php?id=$id" : '/pages/tambah-produk.php');
}

if ($aksi === 'ubah' && !$sku) {
    setFlash('error', 'SKU tidak boleh kosong.');
    redirect("/pages/ubah-produk.php?id=$id");
}

if ($hargaBeli < 0 || $hargaJual < 0 || $stok < 0 || $stokMin < 0) {
    setFlash('error', 'Jumlah tidak boleh kurang dari 0');
    redirect($aksi === 'ubah' ? "/pages/ubah-produk.php?id=$id" : '/pages/tambah-produk.php');
}

if ($hargaBeli > $hargaJual) {
    setFlash('error', 'Harga beli tidak boleh lebih mahal dari harga jual.');
    redirect($aksi === 'ubah' ? "/pages/ubah-produk.php?id=$id" : '/pages/tambah-produk.php');
}

$variants = $_POST['variants'] ?? [];
if ($aksi !== 'ubah' && !empty($variants) && is_array($variants['sku'] ?? null)) {
    for ($i = 0; $i < count($variants['sku']); $i++) {
        $vPriceSell = (float)($variants['price_sell'][$i] ?? 0);
        $vStock = (int)($variants['stock'][$i] ?? 0);
        if ($vPriceSell < 0 || $vStock < 0) {
            setFlash('error', 'Jumlah tidak boleh kurang dari 0');
            redirect('/pages/tambah-produk.php');
        }
        if ($hargaBeli > $vPriceSell) {
            setFlash('error', 'Harga beli tidak boleh lebih mahal dari harga jual varian.');
            redirect('/pages/tambah-produk.php');
        }
    }
}

$namaFoto = $fotoLama; 

if (!empty($_FILES['image']['name'])) {
    $file       = $_FILES['image'];
    $ekstensi   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $formatOK   = in_array($ekstensi, ['jpg','jpeg','png','webp','gif']);
    $ukuranOK   = $file['size'] <= 2 * 1024 * 1024; 

    if (!$formatOK) {
        setFlash('error', 'Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.');
        redirect($aksi === 'ubah' ? "/pages/ubah-produk.php?id=$id" : '/pages/tambah-produk.php');
    }

    if (!$ukuranOK) {
        setFlash('error', 'Ukuran foto maksimal 2MB.');
        redirect($aksi === 'ubah' ? "/pages/ubah-produk.php?id=$id" : '/pages/tambah-produk.php');
    }

    
    $namaFoto = uniqid('prod_') . '.' . $ekstensi;

    if (!move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $namaFoto)) {
        setFlash('error', 'Gagal upload foto. Pastikan folder assets/uploads/ bisa ditulis.');
        redirect($aksi === 'ubah' ? "/pages/ubah-produk.php?id=$id" : '/pages/tambah-produk.php');
    }

    
    if ($aksi === 'ubah' && $fotoLama && file_exists(UPLOAD_PATH . $fotoLama)) {
        @unlink(UPLOAD_PATH . $fotoLama);
    }
}

try {
    if ($aksi === 'ubah' && $id) {
        
        $cek = $pdo->prepare("SELECT id FROM produk WHERE sku = ? AND id != ?");
        $cek->execute([$sku, $id]);
        if ($cek->fetch()) {
            throw new Exception("SKU \"$sku\" sudah digunakan oleh produk lain.");
        }
        
        $pdo->prepare("
            UPDATE produk SET
                name        = :nama,
                sku         = :sku,
                kategori_id = :kat,
                supplier_id = :sup,
                size        = :ukuran,
                color       = :warna,
                price_buy   = :hbeli,
                price_sell  = :hjual,
                stock       = :stok,
                stock_min   = :stokmin,
                image       = :foto,
                description = :deskr,
                status      = :status,
                bin_location= :bin
            WHERE id = :id
        ")->execute([
            ':nama'   => $nama,
            ':sku'    => $sku,
            ':kat'    => $katId,
            ':sup'    => $supId,
            ':ukuran' => $ukuran,
            ':warna'  => $warna,
            ':hbeli'  => $hargaBeli,
            ':hjual'  => $hargaJual,
            ':stok'   => $stok,
            ':stokmin'=> $stokMin,
            ':foto'   => $namaFoto,
            ':deskr'  => $deskripsi,
            ':status' => $status,
            ':bin'    => $bin,
            ':id'     => $id,
        ]);

        setFlash('success', "Produk \"$nama\" berhasil diperbarui!");
        redirect("/pages/detail-produk.php?id=$id");

    } else {
        
        $variants = $_POST['variants'] ?? [];
        if (!empty($variants) && is_array($variants['sku'])) {
            $insertedCount = 0;
            $pdo->beginTransaction();
            
            for ($i = 0; $i < count($variants['sku']); $i++) {
                $vSku = trim($variants['sku'][$i]);
                $vColor = trim($variants['color'][$i]);
                $vSize = trim($variants['size'][$i]);
                $vPriceSell = (float)$variants['price_sell'][$i];
                $vStock = (int)$variants['stock'][$i];
                $vBin = isset($variants['bin_location'][$i]) ? trim($variants['bin_location'][$i]) : $bin;
                
                
                $cek = $pdo->prepare("SELECT id FROM produk WHERE sku = ?");
                $cek->execute([$vSku]);
                if ($cek->fetch()) {
                    throw new Exception("SKU \"$vSku\" sudah digunakan oleh produk lain!");
                }
                
                
                $pdo->prepare("
                    INSERT INTO produk
                        (name, sku, kategori_id, supplier_id, size, color, price_buy, price_sell, stock, stock_min, image, description, status, bin_location)
                    VALUES
                        (:nama, :sku, :kat, :sup, :ukuran, :warna, :hbeli, :hjual, :stok, :stokmin, :foto, :deskr, :status, :bin)
                ")->execute([
                    ':nama'   => $nama,
                    ':sku'    => $vSku,
                    ':kat'    => $katId,
                    ':sup'    => $supId,
                    ':ukuran' => $vSize,
                    ':warna'  => $vColor,
                    ':hbeli'  => $hargaBeli,
                    ':hjual'  => $vPriceSell,
                    ':stok'   => $vStock,
                    ':stokmin'=> $stokMin,
                    ':foto'   => $namaFoto ?: null,
                    ':deskr'  => $deskripsi,
                    ':status' => $status,
                    ':bin'    => $vBin
                ]);
                $insertedCount++;
            }
            
            $pdo->commit();
            setFlash('success', "Berhasil menambahkan $insertedCount varian produk!");
            redirect("/pages/produk.php");
        } else {
            
            if (!$sku) {
                throw new Exception("SKU tidak boleh kosong untuk produk reguler.");
            }
            
            
            $cek = $pdo->prepare("SELECT id FROM produk WHERE sku = ?");
            $cek->execute([$sku]);
            if ($cek->fetch()) {
                throw new Exception("SKU \"$sku\" sudah digunakan.");
            }

            
            $pdo->prepare("
                INSERT INTO produk
                    (name, sku, kategori_id, supplier_id, size, color, price_buy, price_sell, stock, stock_min, image, description, status, bin_location)
                VALUES
                    (:nama, :sku, :kat, :sup, :ukuran, :warna, :hbeli, :hjual, :stok, :stokmin, :foto, :deskr, :status, :bin)
            ")->execute([
                ':nama'   => $nama,
                ':sku'    => $sku,
                ':kat'    => $katId,
                ':sup'    => $supId,
                ':ukuran' => $ukuran,
                ':warna'  => $warna,
                ':hbeli'  => $hargaBeli,
                ':hjual'  => $hargaJual,
                ':stok'   => $stok,
                ':stokmin'=> $stokMin,
                ':foto'   => $namaFoto ?: null,
                ':deskr'  => $deskripsi,
                ':status' => $status,
                ':bin'    => $bin
            ]);

            $idBaru = $pdo->lastInsertId();
            setFlash('success', "Produk \"$nama\" berhasil ditambahkan!");
            redirect("/pages/detail-produk.php?id=$idBaru");
        }
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlash('error', 'Terjadi kesalahan: ' . $e->getMessage());
    redirect($aksi === 'ubah' ? "/pages/ubah-produk.php?id=$id" : '/pages/tambah-produk.php');
}
