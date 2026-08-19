<x-layouts.main title="Detail Menu: {{ $menu->name }}">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Detail Produk</h4>
                <div class="page-title-right">
                    <a href="{{ route('admin.menu.index') }}" class="btn btn-soft-secondary btn-sm">
                        <i class="ri-arrow-left-line align-bottom me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row">
                <div class="col-xl-4 col-lg-5">
                    <div class="p-3 bg-light rounded text-center">
                        <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="img-fluid rounded shadow-sm"
                            style="max-height: 300px; object-fit: cover;">
                    </div>
                </div>

                <div class="col-xl-8 col-lg-7">
                    <div class="ps-lg-4 mt-4 mt-lg-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4 class="mb-1">{{ $menu->name }}
                                    <span
                                        class="badge {{ $menu->is_available ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} fs-12 align-middle ms-2">
                                        {{ $menu->is_available ? 'Ready' : 'Sold Out' }}
                                    </span>
                                </h4>
                                <p class="text-muted mb-4">{{ $menu->category->name }}</p>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-light btn-icon" type="button" data-bs-toggle="dropdown">
                                    <i class="ri-more-2-fill"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('admin.menu.edit', $menu->id) }}"><i
                                                class="ri-pencil-fill me-2 text-muted"></i> Edit</a></li>
                                    <li>
                                        <form action="{{ route('admin.menu.destroy', $menu->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="button" class="dropdown-item text-danger btn-delete"><i
                                                    class="ri-delete-bin-fill me-2"></i> Hapus</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-3">
                            <h5 class="fs-14 text-muted">Harga:</h5>
                            <h3 class="text-primary fw-bold">Rp {{ number_format($menu->price, 0, ',', '.') }}</h3>
                        </div>

                        <div class="mt-4">
                            <h5 class="fs-14 text-muted mb-2">Deskripsi Menu:</h5>
                            <p class="text-muted leading-relaxed">
                                {{ $menu->description ?: 'Tidak ada deskripsi untuk menu ini.' }}
                            </p>
                        </div>

                        <div class="row mt-4">
                            <div class="col-sm-6">
                                <div class="p-3 border border-dashed rounded text-center">
                                    <h5 class="fs-13 text-muted mb-1">Waktu Persiapan:</h5>
                                    <p class="mb-0 fw-medium">{{ $menu->preparation_time ?: '5' }} Menit</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border border-dashed rounded text-center">
                                    <h5 class="fs-13 text-muted mb-1">Ditambahkan Pada:</h5>
                                    <p class="mb-0 fw-medium">{{ $menu->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <livewire:discount-table :menuId="$menu->id" />
    </div>
</x-layouts.main>
