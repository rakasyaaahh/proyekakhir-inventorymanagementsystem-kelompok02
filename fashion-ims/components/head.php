<?php
$judul = isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — FashionIMS' : 'FashionIMS';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FashionIMS — Sistem Manajemen Inventori Fashion">
    <title><?= $judul ?></title>
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="bg-surface text-textmain font-sans text-sm leading-relaxed">
<div class="flex min-h-screen">
