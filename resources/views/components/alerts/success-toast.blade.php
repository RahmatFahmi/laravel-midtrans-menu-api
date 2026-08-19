<style>
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
</style>

<script>
    // Fungsi sakti untuk memunculkan Toast buatanmu
    function launchToast(message) {
        // Buat elemen div baru
        const toast = document.createElement('div');
        toast.className = 'custom-toast animate__animated animate__fadeInDown';
        toast.innerHTML = `<b>✅</b> ${message}`;

        document.body.appendChild(toast);

        // Hapus otomatis setelah 3 detik
        setTimeout(() => {
            toast.classList.replace('animate__fadeInDown', 'animate__fadeOutUp');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }

    // 1. CEK DARI SESSION (Controller Biasa)
    @if (session('success'))
        launchToast("{{ session('success') }}");
    @endif

    // 2. CEK DARI LIVEWIRE (Volt)
    window.addEventListener('swal:success', event => {
        // Kita gunakan event yang sama, tapi panggil fungsi toastmu
        launchToast(event.detail.text);
    });
</script>
