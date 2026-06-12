</div>
</main>
</div>

<script>
    // Global confirmation modal script
    (function () {
        // Insert modal markup
        const modalHtml = `
        <div id="confirmModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative">
                <h3 class="text-lg font-semibold mb-2">Konfirmasi</h3>
                <p id="confirmMessage" class="text-sm text-gray-700 mb-4">Apakah Anda yakin?</p>
                <div class="flex justify-end space-x-2">
                    <button id="confirmCancel" type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">Batal</button>
                    <button id="confirmOk" type="button" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Ya, Lanjutkan</button>
                </div>
                <button id="confirmCloseX" type="button" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
        </div>`;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const confirmModal = document.getElementById('confirmModal');
        const confirmMessage = document.getElementById('confirmMessage');
        const confirmOk = document.getElementById('confirmOk');
        const confirmCancel = document.getElementById('confirmCancel');
        const confirmCloseX = document.getElementById('confirmCloseX');

        let confirmResolve = null;

        function openConfirm(message) {
            confirmMessage.textContent = message || 'Apakah Anda yakin?';
            confirmModal.classList.remove('hidden');
            return new Promise((res) => { confirmResolve = res; });
        }

        function closeConfirm() {
            confirmModal.classList.add('hidden');
            if (confirmResolve) { confirmResolve(false); confirmResolve = null; }
        }

        confirmCancel.addEventListener('click', () => { closeConfirm(); });
        confirmCloseX.addEventListener('click', () => { closeConfirm(); });

        confirmOk.addEventListener('click', () => {
            if (confirmResolve) { confirmResolve(true); confirmResolve = null; }
            confirmModal.classList.add('hidden');
        });

        // Delegate clicks on anchors with .need-confirm
        document.addEventListener('click', function (e) {
            const el = e.target.closest && e.target.closest('.need-confirm');
            if (!el) return;
            e.preventDefault();
            const msg = el.getAttribute('data-confirm') || 'Yakin?';
            openConfirm(msg).then(function (ok) { if (ok) { window.location.href = el.getAttribute('href'); } });
        });

        // Intercept forms with .need-confirm-form
        document.addEventListener('submit', function (e) {
            const form = e.target.closest && e.target.closest('.need-confirm-form');
            if (!form) return;
            e.preventDefault();
            const msg = form.getAttribute('data-confirm') || 'Yakin?';
            openConfirm(msg).then(function (ok) { if (ok) { form.removeEventListener('submit', arguments.callee); form.submit(); } });
        }, true);
    })();
</script>
</body>

</html>