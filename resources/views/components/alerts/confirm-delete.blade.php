@once
    <style>
        /* Toast Sukses Buatanmu */
        .custom-toast {
            background: #ffffff;
            color: #222;
            padding: 1rem 1.25rem;
            border-left: 4px solid #28a745;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            border-radius: 6px;
            max-width: 300px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        /* Style SweetAlert Custom */
        .swal2-popup.toast-delete-style {
            border-left: 4px solid #dc3545 !important;
            padding: 1rem 1.25rem;
            font-size: 14px;
            border-radius: 8px;
            max-width: 360px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        @media (prefers-color-scheme: dark) {
            .swal2-popup.toast-delete-style {
                background: #1f1f1f !important;
                color: #f1f1f1 !important;
            }

            .swal2-title,
            .swal2-html-container {
                color: #fff !important;
            }
        }
    </style>

    <script>
        // 1. FUNGSI TOAST GLOBAL
        function launchToast(message) {
            const toast = document.createElement('div');
            toast.className = 'custom-toast animate__animated animate__fadeInDown';
            toast.innerHTML = `<b>✅</b> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.classList.replace('animate__fadeInDown', 'animate__fadeOutUp');
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }

        // Tangkap Flash Message (Controller) & Livewire Success
        @if (session('success'))
            launchToast("{{ session('success') }}");
        @endif
        window.addEventListener('swal:success', e => launchToast(e.detail.text));

        // 2. FUNGSI KONFIRMASI HAPUS GLOBAL
        function confirmDeleteAction(title, message, callback) {
            Swal.fire({
                title: title || 'Yakin ingin menghapus?',
                html: message || "🗑️ Data yang dihapus tidak bisa dikembalikan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'toast-delete-style animate__animated animate__fadeInDown'
                }
            }).then((result) => {
                if (result.isConfirmed) callback();
            });
        }

        // Handle klik tombol .btn-delete (Controller Biasa)
        document.addEventListener('click', e => {
            const btn = e.target.closest('.btn-delete');
            if (btn) {
                e.preventDefault();
                confirmDeleteAction(null, null, () => btn.closest('form').submit());
            }
        });

        // Handle Event dari Livewire
        window.addEventListener('confirm-delete', e => {
            confirmDeleteAction('Hapus ' + e.detail.name + '?', null, () => {
                // Kita kirim balik event ke window untuk ditangkap komponen
                window.dispatchEvent(new CustomEvent('execute-delete', {
                    detail: {
                        id: e.detail.id
                    }
                }));
            });
        });
    </script>
@endonce
