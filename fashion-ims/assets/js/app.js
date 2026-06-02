document.addEventListener('DOMContentLoaded', function () {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    const flash = document.getElementById('flash-data');
    if (flash) {
        tampilkanToast(flash.dataset.type, flash.dataset.message);
    }

    const btnToggleSidebar = document.getElementById('btn-toggle-sidebar');
    const btnCloseSidebar  = document.getElementById('btn-close-sidebar');
    const sidebar          = document.getElementById('sidebar');
    const sidebarBackdrop  = document.getElementById('sidebar-backdrop');

    if (btnToggleSidebar && sidebar) {
        btnToggleSidebar.addEventListener('click', function () {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            if (sidebarBackdrop) sidebarBackdrop.classList.remove('hidden');
        });
    }

    const tutupSidebarMenu = function () {
        if (sidebar) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
        }
        if (sidebarBackdrop) sidebarBackdrop.classList.add('hidden');
    };

    if (btnCloseSidebar) {
        btnCloseSidebar.addEventListener('click', tutupSidebarMenu);
    }
    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', tutupSidebarMenu);
    }

    const formFilter = document.getElementById('form-filter');
    const inputCari  = document.getElementById('input-cari');
    
    if (formFilter) {
        const tbody     = document.getElementById('tabel-produk');
        const pagArea   = document.getElementById('area-paginasi');
        const subtitle  = document.getElementById('total-produk');
        
        const performSearch = () => {
            const formData = new FormData(formFilter);
            const queryParams = new URLSearchParams(formData).toString();
            
            fetch(`produk.php?ajax=1&${queryParams}`)
                .then(res => res.json())
                .then(data => {
                    if (tbody) tbody.innerHTML = data.tbody;
                    if (pagArea) {
                        if (data.paginasi && data.paginasi.trim()) {
                            pagArea.innerHTML = data.paginasi;
                            pagArea.style.display = 'flex';
                        } else {
                            pagArea.style.display = 'none';
                        }
                    }
                    if (subtitle && data.total) {
                        subtitle.textContent = data.total;
                    }
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                })
                .catch(err => console.error(err));
        };
        
        let jeda;
        if (inputCari) {
            inputCari.addEventListener('input', function () {
                clearTimeout(jeda);
                jeda = setTimeout(performSearch, 300);
            });
        }
        
        formFilter.querySelectorAll('.form-select').forEach(select => {
            select.addEventListener('change', function (e) {
                e.preventDefault();
                performSearch();
            });
        });
    }

    const modalHapus = document.getElementById('modal-hapus');
    let urlHapus = '';

    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-hapus-url]');
        if (btn) {
            e.preventDefault();
            urlHapus = btn.dataset.hapusUrl;
            const nama = btn.dataset.hapusNama || 'item ini';
            const modalNama = document.getElementById('modal-nama-item');
            if (modalNama) modalNama.textContent = nama;
            bukaModal('modal-hapus');
        }
    });

    const btnKonfirmasiHapus = document.getElementById('btn-konfirmasi-hapus');
    if (btnKonfirmasiHapus) {
        btnKonfirmasiHapus.addEventListener('click', function () {
            if (urlHapus) window.location.href = urlHapus;
        });
    }

    const inputGambar   = document.getElementById('input-gambar');
    const previewGambar = document.getElementById('preview-gambar');
    const placeholderUpload = document.getElementById('placeholder-upload');

    if (inputGambar && previewGambar) {
        inputGambar.addEventListener('change', function () {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewGambar.src = e.target.result;
                    previewGambar.style.display = 'block';
                    if (placeholderUpload) placeholderUpload.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        const areaUpload = document.querySelector('.upload-area');
        if (areaUpload) {
            areaUpload.addEventListener('click', () => inputGambar.click());
            areaUpload.addEventListener('dragover', e => {
                e.preventDefault();
                areaUpload.classList.add('drag-over');
            });
            areaUpload.addEventListener('dragleave', () => {
                areaUpload.classList.remove('drag-over');
            });
            areaUpload.addEventListener('drop', function (e) {
                e.preventDefault();
                areaUpload.classList.remove('drag-over');
                const file = e.dataTransfer.files[0];
                if (file) {
                    inputGambar.files = e.dataTransfer.files;
                    inputGambar.dispatchEvent(new Event('change'));
                }
            });
        }
    }

    const btnSKU       = document.getElementById('btn-generate-sku');
    const inputSKU     = document.getElementById('input-sku');
    const pilihanKateg = document.getElementById('pilihan-kategori');

    const prefixKateg = {
        '1': 'ATS',
        '2': 'BWH',
        '3': 'GWN',
        '4': 'OUT',
        '5': 'AKS',
        '6': 'SPT',
    };

    if (btnSKU && inputSKU) {
        btnSKU.addEventListener('click', function () {
            const idKateg  = pilihanKateg ? pilihanKateg.value : '';
            const prefix   = prefixKateg[idKateg] || 'PRD';
            const angka    = Math.floor(Math.random() * 900) + 100;
            inputSKU.value = 'FMS-' + prefix + '-' + angka;
            inputSKU.style.borderColor = 'var(--amber)';
            setTimeout(() => inputSKU.style.borderColor = '', 700);
        });
    }

    const btnToggleForm = document.getElementById('btn-toggle-form');
    const panelForm     = document.getElementById('panel-form');

    if (btnToggleForm && panelForm) {
        const originalHTML = btnToggleForm.innerHTML;
        btnToggleForm.addEventListener('click', function () {
            const sedangBuka = panelForm.style.display !== 'none';
            panelForm.style.display = sedangBuka ? 'none' : 'block';
            this.innerHTML = sedangBuka ? originalHTML : '<i data-lucide="x"></i> Tutup Form';
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    }

    document.querySelectorAll('.modal-bg').forEach(function (bg) {
        bg.addEventListener('click', function (e) {
            if (e.target === this) tutupModal(this.id);
        });
    });

    document.querySelectorAll('[data-tutup-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            tutupModal(this.dataset.tutupModal);
        });
    });
});

function bukaModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('buka'); document.body.style.overflow = 'hidden'; }
}

function tutupModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('buka'); document.body.style.overflow = ''; }
}

function tampilkanToast(tipe, pesan) {
    let area = document.getElementById('toast-area');
    if (!area) {
        area = document.createElement('div');
        area.id = 'toast-area';
        document.body.appendChild(area);
    }

    const ikon = {
        sukses:  '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        success: '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        error:   '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
        warning: '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        info:    '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
    };

    const kelasCSS = tipe === 'success' ? 'sukses' : tipe;

    const toast = document.createElement('div');
    toast.className = 'toast ' + kelasCSS;
    toast.innerHTML = '<span style="display:inline-flex;align-items:center;justify-content:center;">' + (ikon[tipe] || ikon['info']) + '</span> ' + pesan +
        ' <button onclick="this.parentElement.classList.add(\'keluar\');setTimeout(()=>this.parentElement.remove(),300)" ' +
        'style="margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;padding:0;display:inline-flex;align-items:center;justify-content:center;opacity:0.75;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.75">' +
        '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>';

    area.appendChild(toast);
    setTimeout(function () {
        toast.classList.add('keluar');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
