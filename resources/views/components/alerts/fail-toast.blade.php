@if (session('error'))
    <style>
        #custom-toast-error {
            background: #fff;
            color: #b02a37;
            padding: 1rem 1.25rem;
            border-left: 4px solid #dc3545;
            /* merah bootstrap */
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            border-radius: 6px;
            max-width: 300px;
        }
    </style>

    <div id="custom-toast-error" class="position-fixed top-0 end-0 m-3 zindex-tooltip" style="z-index: 9999;">
        <b>❌ Gagal:</b> {{ session('error') }}
    </div>

    <script>
        setTimeout(() => {
            const toast = document.getElementById('custom-toast-error');
            if (toast) {
                toast.classList.add('animate__animated', 'animate__fadeOutUp');
                setTimeout(() => toast.remove(), 500);
            }
        }, 3000);
    </script>
@endif
