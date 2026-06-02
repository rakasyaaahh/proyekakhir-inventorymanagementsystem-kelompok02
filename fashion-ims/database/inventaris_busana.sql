SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS inventaris_busana
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE inventaris_busana;

DROP TABLE IF EXISTS detail_barang_keluar;
DROP TABLE IF EXISTS barang_keluar;
DROP TABLE IF EXISTS detail_barang_masuk;
DROP TABLE IF EXISTS barang_masuk;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS pemasok;
DROP TABLE IF EXISTS user;
DROP TABLE IF EXISTS users;

CREATE TABLE IF NOT EXISTS user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','staff') DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS supplier (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(100),
  phone VARCHAR(20),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO supplier (id, name, email, phone, address) VALUES
(1, 'PT Garment Nusantara',  'order@garnusa.co.id',    '0215551234',   'Jl. Industri Raya No. 88, Tangerang, Banten'),
(2, 'CV Tekstil Modern',     'info@tekstilmodern.com', '0224449876',   'Jl. Leuwipanjang No. 12, Bandung, Jawa Barat'),
(3, 'Batik Alam Indo',       'batik@alamindo.id',      '0274776655',   'Jl. Malioboro No. 45, Yogyakarta, DIY')
ON DUPLICATE KEY UPDATE name=VALUES(name), email=VALUES(email), phone=VALUES(phone), address=VALUES(address);

CREATE TABLE IF NOT EXISTS kategori (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(50) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO kategori (id, name, slug, description) VALUES
(1, 'Atasan', 'ATS', 'Koleksi pakaian bagian atas seperti kaos, kemeja, dan sweater.'),
(2, 'Bawahan', 'BWH', 'Koleksi celana, rok, dan jeans berkualitas.'),
(3, 'Gaun', 'GWN', 'Gaun terusan elegan untuk acara kasual maupun formal.'),
(4, 'Outerwear', 'OUT', 'Pakaian luar hangat seperti jaket, blazer, dan cardigan.'),
(5, 'Aksesoris', 'AKS', 'Perhiasan, tas, sabuk, topi, dan pernak-pernik pendukung.'),
(6, 'Sepatu', 'SPT', 'Sneakers, selop, flatshoes, dan alas kaki lainnya.')
ON DUPLICATE KEY UPDATE name=VALUES(name), slug=VALUES(slug), description=VALUES(description);

CREATE TABLE IF NOT EXISTS produk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  sku VARCHAR(50) NOT NULL UNIQUE,
  kategori_id INT NULL,
  supplier_id INT NULL,
  size VARCHAR(100),
  color VARCHAR(200),
  price_buy DECIMAL(15,2) NOT NULL DEFAULT 0,
  price_sell DECIMAL(15,2) NOT NULL DEFAULT 0,
  stock INT DEFAULT 0,
  stock_min INT DEFAULT 5,
  image VARCHAR(255),
  description TEXT,
  status ENUM('active','inactive') DEFAULT 'active',
  bin_location VARCHAR(50) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL,
  FOREIGN KEY (supplier_id) REFERENCES supplier(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO produk (id, name, sku, kategori_id, supplier_id, size, color, price_buy, price_sell, stock, stock_min, description, status, bin_location) VALUES
(1, 'Basic White Tee',           'FMS-ATS-001', 1, 1, 'S,M,L,XL',    'Putih,Abu,Hitam',       25000,  75000,  52, 10, 'Kaos polos basic premium bahan combed 30s, cocok untuk style sehari-hari.', 'active', 'A-01'),
(2, 'Kemeja Flanel Kotak',       'FMS-ATS-002', 1, 1, 'M,L,XL',      'Merah,Biru,Hijau',      85000,  215000, 28, 8,  'Kemeja flanel motif kotak tebal dan hangat, cocok untuk musim dingin.', 'active', 'A-02'),
(3, 'Oversized Knit Sweater',    'FMS-ATS-003', 1, 2, 'M,L,XL,XXL',  'Krem,Coklat,Navy',      120000, 290000, 15, 5,  'Sweater rajut oversized nyaman and stylish untuk tampilan kasual.', 'active', 'A-03'),
(4, 'Polo Shirt Classic',        'FMS-ATS-004', 1, 1, 'S,M,L,XL',    'Putih,Navy,Hitam,Abu',  55000,  145000, 40, 10, 'Polo shirt klasik berbahan pique cotton, cocok untuk smart casual.', 'active', 'A-04'),
(5, 'Slim Fit Chinos',           'FMS-BWH-001', 2, 1, '28,30,32,34', 'Khaki,Navy,Abu,Putih',  90000,  225000, 3,  8,  'Celana chinos slim fit bahan katun stretch, elegan dan nyaman dipakai.', 'active', 'B-01'),
(6, 'Denim Skinny Pants',        'FMS-BWH-002', 2, 2, '26,28,30,32', 'Biru,Hitam,Abu',        105000, 265000, 12, 5,  'Celana jeans skinny fit premium denim, tampil stylish kapan saja.', 'active', 'B-02'),
(7, 'Pleated Midi Skirt',        'FMS-BWH-003', 2, 2, 'S,M,L',       'Hitam,Krem,Dusty Rose', 75000,  195000, 2,  5,  'Rok midi plisket elegan cocok untuk tampilan feminin dan kasual.', 'active', 'B-03'),
(8, 'Floral Wrap Dress',         'FMS-GWN-001', 3, 2, 'S,M,L',       'Floral Pink,Floral Blue',95000, 250000, 18, 5,  'Dress wrap motif bunga elegan, cocok untuk acara kasual maupun semi formal.', 'active', 'C-01'),
(9, 'Bodycon Mini Dress',        'FMS-GWN-002', 3, 1, 'XS,S,M,L',    'Hitam,Merah,Navy',      80000,  220000, 9,  5,  'Mini dress bodycon modern, tampil percaya diri and stylish.', 'active', 'C-02'),
(10, 'Denim Jacket Classic',     'FMS-OUT-001', 4, 1, 'S,M,L,XL',    'Biru Muda,Biru Tua,Hitam',115000,310000, 22, 8,  'Jaket denim klasik yang tidak lekang waktu, cocok dipadukan dengan apapun.', 'active', 'D-01'),
(11, 'Blazer Structured',        'FMS-OUT-002', 4, 2, 'S,M,L,XL',    'Hitam,Abu,Cream',       145000, 395000, 4,  5,  'Blazer structured elegan untuk tampilan profesional dan stylish.', 'active', 'D-02'),
(12, 'Knit Cardigan Long',       'FMS-OUT-003', 4, 2, 'S,M,L',       'Krem,Coklat,Sage Green',98000,  265000, 7,  5,  'Cardigan rajut panjang lembut dan hangat, cocok untuk outer musim dingin.', 'active', 'D-03'),
(13, 'Woven Tote Bag',           'FMS-AKS-001', 5, 3, 'One Size',    'Natural,Hitam',         55000,  185000, 30, 5,  'Tote bag anyaman premium, spacious dan stylish untuk kegiatan sehari-hari.', 'active', 'E-01'),
(14, 'Snapback Cap',             'FMS-AKS-002', 5, 1, 'One Size',    'Hitam,Putih,Navy',      35000,  95000,  45, 10, 'Topi snapback dengan material berkualitas, cocok untuk gaya streetwear.', 'active', 'E-02'),
(15, 'Slip-On Canvas Sneakers',  'FMS-SPT-001', 6, 1, '37,38,39,40,41,42', 'Putih,Hitam,Abu', 85000,  225000, 1,  5,  'Sneakers slip-on kanvas ringan dan nyaman untuk aktivitas sehari-hari.', 'active', 'F-01')
ON DUPLICATE KEY UPDATE name=VALUES(name), sku=VALUES(sku), kategori_id=VALUES(kategori_id), supplier_id=VALUES(supplier_id), size=VALUES(size), color=VALUES(color), price_buy=VALUES(price_buy), price_sell=VALUES(price_sell), stock=VALUES(stock), stock_min=VALUES(stock_min), description=VALUES(description), status=VALUES(status), bin_location=VALUES(bin_location);

CREATE TABLE IF NOT EXISTS barang_masuk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  receive_no VARCHAR(50) NOT NULL UNIQUE,
  supplier_id INT NULL,
  received_by VARCHAR(100) NOT NULL,
  received_date DATE NOT NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES supplier(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS detail_barang_masuk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  barang_masuk_id INT NOT NULL,
  produk_id INT NOT NULL,
  qty INT NOT NULL,
  price_buy DECIMAL(15,2) NOT NULL,
  FOREIGN KEY (barang_masuk_id) REFERENCES barang_masuk(id) ON DELETE CASCADE,
  FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS barang_keluar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(50) NOT NULL UNIQUE,
  customer_name VARCHAR(100) NOT NULL,
  total_price DECIMAL(15,2) NOT NULL DEFAULT 0,
  status ENUM('pending', 'qc_passed', 'shipped', 'cancelled') DEFAULT 'pending',
  qc_by VARCHAR(100) NULL,
  qc_date DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS detail_barang_keluar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  barang_keluar_id INT NOT NULL,
  produk_id INT NOT NULL,
  qty INT NOT NULL,
  price DECIMAL(15,2) NOT NULL,
  FOREIGN KEY (barang_keluar_id) REFERENCES barang_keluar(id) ON DELETE CASCADE,
  FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO barang_masuk (id, receive_no, supplier_id, received_by, received_date, notes) VALUES
(1, 'BM-20260530-001', 1, 'Administrator', '2026-05-30', 'Restock kaos basic dan kemeja flanel'),
(2, 'BM-20260531-002', 2, 'Administrator', '2026-05-31', 'Pengiriman dari CV Tekstil Modern')
ON DUPLICATE KEY UPDATE receive_no=VALUES(receive_no), supplier_id=VALUES(supplier_id), received_by=VALUES(received_by), received_date=VALUES(received_date), notes=VALUES(notes);

INSERT INTO detail_barang_masuk (id, barang_masuk_id, produk_id, qty, price_buy) VALUES
(1, 1, 1, 20, 25000.00),
(2, 1, 2, 10, 85000.00),
(3, 2, 3, 5, 120000.00)
ON DUPLICATE KEY UPDATE barang_masuk_id=VALUES(barang_masuk_id), produk_id=VALUES(produk_id), qty=VALUES(qty), price_buy=VALUES(price_buy);

INSERT INTO barang_keluar (id, invoice_no, customer_name, total_price, status, qc_by, qc_date) VALUES
(1, 'INV-20260531-001', 'Budi Santoso', 290000.00, 'shipped', 'Administrator', '2026-05-31 10:15:00'),
(2, 'INV-20260531-002', 'Siti Rahma', 225000.00, 'qc_passed', 'Administrator', '2026-05-31 14:30:00'),
(3, 'INV-20260531-003', 'Rian Hidayat', 75000.00, 'pending', NULL, NULL)
ON DUPLICATE KEY UPDATE invoice_no=VALUES(invoice_no), customer_name=VALUES(customer_name), total_price=VALUES(total_price), status=VALUES(status), qc_by=VALUES(qc_by), qc_date=VALUES(qc_date);

INSERT INTO detail_barang_keluar (id, barang_keluar_id, produk_id, qty, price) VALUES
(1, 1, 3, 1, 290000.00),
(2, 2, 5, 1, 225000.00),
(3, 3, 1, 1, 75000.00)
ON DUPLICATE KEY UPDATE barang_keluar_id=VALUES(barang_keluar_id), produk_id=VALUES(produk_id), qty=VALUES(qty), price=VALUES(price);

SET FOREIGN_KEY_CHECKS = 1;