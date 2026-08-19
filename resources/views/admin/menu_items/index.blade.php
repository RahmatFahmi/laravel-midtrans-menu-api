<x-layouts.main title="Manajemen Menu">
    <div class="card shadow-sm border-0">
        <div class="card-header border-0 align-items-center d-flex">
            <h4 class="card-title mb-0 flex-grow-1">Data Menu</h4>
            <div class="flex-shrink-0">
                <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">

                    <form action="{{ route('admin.menu.index') }}" method="GET" id="search-form"
                        class="d-flex align-items-center flex-grow-1">
                        <div class="position-relative flex-grow-1">
                            <input type="text" name="search" id="search-input" value="{{ request('search') }}"
                                class="form-control form-control-sm ps-4" placeholder="Cari menu..."
                                oninput="doSearch()" autocomplete="off">
                            <i
                                class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-2 text-muted"></i>
                        </div>

                        @if (request('search'))
                            <a href="{{ route('admin.menu.index') }}" class="btn btn-light btn-sm ms-2">
                                <i class="ri-refresh-line"></i>
                            </a>
                        @endif
                    </form>

                    <a href="{{ route('admin.menu.create') }}" class="btn btn-primary btn-sm shadow-sm text-nowrap">
                        <i class="ri-add-line align-middle"></i>
                        <span class="d-inline">Tambah Menu</span>
                    </a>

                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="min-height: 180px;">
                <table class="table table-hover align-middle table-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 50px;">No</th>
                            <th scope="col">Foto</th>
                            <th scope="col">Menu & Kategori</th>
                            <th scope="col">Harga</th>
                            <th scope="col">Waktu Prep</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menus as $index => $menu)
                            <tr>
                                <td>{{ $menus->firstItem() + $index }}</td>
                                <td>
                                    <img src="{{ $menu->image_url }}" alt="" class="rounded" width="50"
                                        height="50" style="object-fit: cover;">
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold">{{ $menu->name }}</span>
                                        <small class="text-muted">{{ $menu->category->name }}</small>
                                    </div>
                                </td>
                                <td>Rp {{ number_format($menu->price, 0, ',', '.') }}</td>
                                <td>{{ $menu->preparation_time ?? '-' }} mnt</td>
                                <td>
                                    @if ($menu->is_available)
                                        <span class="badge bg-success-subtle text-success">Tersedia</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Habis</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                            data-bs-toggle="dropdown">
                                            <i class="ri-more-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow">
                                            <li>
                                                <a class="dropdown-item py-2"
                                                    href="{{ route('admin.menu.show', $menu->id) }}">
                                                    <i class="ri-eye-line align-bottom me-2 text-primary"></i> Lihat
                                                    Detail
                                                </a>
                                            </li>
                                            <li><a class="dropdown-item"
                                                    href="{{ route('admin.menu.edit', $menu->id) }}"><i
                                                        class="ri-pencil-fill align-bottom me-2 text-muted"></i>
                                                    Edit</a></li>
                                            <li>
                                                <form action="{{ route('admin.menu.destroy', $menu->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                        class="dropdown-item text-danger py-2 btn-delete">
                                                        <i class="ri-delete-bin-line me-2"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Data menu tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $menus->links() }}
            </div>
        </div>
    </div>

    <script>
        let searchTimer;

        function doSearch() {
            const form = document.getElementById('search-form');
            const input = document.getElementById('search-input');

            // Hapus timer sebelumnya jika user masih mengetik
            clearTimeout(searchTimer);

            // Set timer baru (tunggu 700ms setelah berhenti mengetik)
            searchTimer = setTimeout(() => {
                // Jangan submit kalau input kosong (opsional, tergantung keinginan)
                form.submit();
            }, 700);
        }

        // Biarkan kursor tetap di akhir teks setelah reload
        const searchInput = document.getElementById('search-input');
        if (searchInput.value.length > 0) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }
    </script>
</x-layouts.main>
