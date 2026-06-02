<?php ?>
    </main>
</div>
</div>

<div id="modal-hapus"
     class="fixed inset-0 bg-black/45 z-[200] flex items-center justify-center opacity-0 invisible transition-all duration-200">
    <div class="bg-white rounded-2xl p-8 w-[90%] max-w-sm text-center border border-border shadow-2xl scale-95 transition-transform duration-200">
        <div class="w-14 h-14 bg-danger-light text-danger rounded-full flex items-center justify-center mx-auto mb-5">
            <i data-lucide="trash-2" style="width:26px;height:26px;"></i>
        </div>
        <h3 class="font-display text-xl font-extrabold text-textmain mb-2">Hapus Data?</h3>
        <p class="text-[0.85rem] text-muted mb-1">Anda akan menghapus:</p>
        <p id="modal-nama-item" class="text-base font-bold text-textmain mb-2 break-all">—</p>
        <p class="text-[0.78rem] font-semibold text-danger mb-6">Tindakan ini tidak bisa dibatalkan!</p>
        <div class="flex justify-center gap-3">
            <button data-tutup-modal="modal-hapus"
                    class="inline-flex items-center justify-center gap-1.5 px-6 py-2 rounded-lg text-sm font-medium border border-border bg-white text-textmain hover:border-primary hover:text-primary transition-all cursor-pointer min-w-[110px]">
                Batal
            </button>
            <button id="btn-konfirmasi-hapus" data-id=""
                    class="inline-flex items-center justify-center gap-1.5 px-6 py-2 rounded-lg text-sm font-medium bg-danger text-white hover:bg-danger-dark transition-all cursor-pointer min-w-[110px]">
                <i data-lucide="trash-2" style="width:15px;height:15px;"></i>
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

<style>
#modal-hapus.buka { opacity: 1; visibility: visible; }
#modal-hapus.buka > div { transform: scale(1); }
</style>

<div id="toast-area" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>