<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /fashion-ims/login.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    die("ID Produk tidak valid.");
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name AS nama_kategori
    FROM produk p
    LEFT JOIN kategori c ON c.id = p.kategori_id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    die("Produk tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Label — <?= htmlspecialchars($p['sku']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #f1f5f9;
            font-family: 'Courier New', Courier, monospace;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        
        .controls {
            margin-bottom: 2rem;
            background: white;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #cbd5e1;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .btn-print {
            padding: 6px 16px;
            background: #0A4174;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-family: sans-serif;
            font-size: 0.85rem;
        }

        
        .label-container {
            background: white;
            width: 60mm;
            height: 40mm;
            border: 2px solid #000;
            padding: 3mm 4mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .label-brand {
            font-size: 6pt;
            font-weight: bold;
            letter-spacing: 0.1em;
            border-bottom: 1px solid #000;
            width: 100%;
            padding-bottom: 2px;
            text-transform: uppercase;
        }

        .label-name {
            font-size: 7.5pt;
            font-weight: bold;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        .label-variant {
            font-size: 7pt;
            margin-top: 1px;
        }

        
        .barcode-graphic {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            height: 8mm;
            margin: 2px 0;
            width: 100%;
        }
        .barcode-bar {
            background-color: black;
            height: 100%;
            margin-right: 1px;
        }
        
        .bar-1 { width: 1px; }
        .bar-2 { width: 2px; }
        .bar-3 { width: 3px; }
        .bar-0 { width: 0.5px; opacity: 0.3; } 

        .label-sku {
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 0.05em;
        }

        .label-footer {
            display: flex;
            justify-content: space-between;
            width: 100%;
            border-top: 1px dashed #000;
            padding-top: 2px;
            font-size: 6pt;
            font-weight: bold;
        }

        
        @media print {
            body {
                background: white;
                padding: 0;
                min-height: auto;
            }
            .controls {
                display: none !important;
            }
            .label-container {
                border: 2px solid #000 !important;
                box-shadow: none !important;
                margin: 0 !important;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="controls">
        <span style="font-family: sans-serif; font-size: 0.85rem; color:#0A4174;">Format Kertas Label: <strong>60mm x 40mm</strong></span>
        <button class="btn-print" onclick="window.print()">Cetak Label</button>
    </div>

    
    <div class="label-container">
        <div class="label-brand">FashionIMS — Inventory</div>
        <div class="label-name"><?= e($p['name']) ?></div>
        <div class="label-variant">
            <?= e($p['color'] ?: '—') ?> / <?= e($p['size'] ?: '—') ?>
        </div>
        
        
        <div class="barcode-graphic">
            
            <?php
                $skuLength = strlen($p['sku']);
                for ($i = 0; $i < 24; $i++) {
                    $barType = ($i + $skuLength) % 4; 
                    echo "<div class='barcode-bar bar-$barType'></div>";
                }
            ?>
        </div>
        
        <div class="label-sku"><?= e($p['sku']) ?></div>
        
        <div class="label-footer">
            <span>RAK: <?= e($p['bin_location'] ?: 'UMUM') ?></span>
            <span>Rp <?= number_format($p['price_sell'], 0, ',', '.') ?></span>
        </div>
    </div>

    <script>
        
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
