<x-layouts.main title="Laporan Pesanan">
    <div class="col">
        <div class="h-100">

            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1">Laporan Pesanan</h4>
                            <p class="text-muted mb-0">Pantau dan filter seluruh pesanan pelanggan.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================================================
                 RINGKASAN STATUS
            ================================================== --}}
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-muted mb-2">Pending</p>
                            <h4 class="fs-22 fw-semibold mb-0 text-warning">{{ $ringkasanStatus->pending ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-muted mb-2">Diproses</p>
                            <h4 class="fs-22 fw-semibold mb-0 text-info">{{ $ringkasanStatus->proses ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-muted mb-2">Selesai</p>
                            <h4 class="fs-22 fw-semibold mb-0 text-success">{{ $ringkasanStatus->selesai ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-muted mb-2">Dibatalkan</p>
                            <h4 class="fs-22 fw-semibold mb-0 text-danger">{{ $ringkasanStatus->batal ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================================================
                 FORM FILTER
            ================================================== --}}
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.orders.index') }}">
                        <div class="row g-3 align-items-end">

                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                        Pending</option>
                                    <option value="proses" {{ request('status') == 'proses' ? 'selected' : '' }}>
                                        Diproses</option>
                                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>
                                        Selesai</option>
                                    <option value="batal" {{ request('status') == 'batal' ? 'selected' : '' }}>
                                        Dibatalkan</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control"
                                    value="{{ request('tanggal_mulai') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control"
                                    value="{{ request('tanggal_selesai') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Meja</label>
                                <select name="table_id" class="form-select">
                                    <option value="">Semua Meja</option>
                                    @foreach ($tables as $table)
                                        <option value="{{ $table->id }}"
                                            {{ request('table_id') == $table->id ? 'selected' : '' }}>
                                            {{ $table->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Cari Pelanggan / ID</label>
                                <input type="text" name="search" class="form-control" placeholder="Nama atau ID..."
                                    value="{{ request('search') }}">
                            </div>

                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ri-filter-3-line align-middle"></i> Filter
                                </button>
                            </div>
                        </div>

                        @if (request()->anyFilled(['status', 'tanggal_mulai', 'tanggal_selesai', 'table_id', 'search']))
                            <div class="mt-3">
                                <a href="{{ route('admin.orders.index') }}"
                                    class="text-muted text-decoration-underline">
                                    <i class="ri-close-circle-line align-middle"></i> Reset Filter
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- ==================================================
                 TABEL PESANAN
            ================================================== --}}
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Daftar Pesanan</h4>
                    <span class="text-muted">Total: {{ $orders->total() }} pesanan</span>
                </div>

                <div class="card-body">
                    <div class="table-responsive table-card">
                        <table class="table table-hover table-centered align-middle table-nowrap mb-0">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Pelanggan</th>
                                    <th>Menu</th>
                                    <th>Meja</th>
                                    <th>Metode Bayar</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    @php
                                        $statusBadge = match ($order->status) {
                                            'pending' => 'bg-warning-subtle text-warning',
                                            'proses' => 'bg-info-subtle text-info',
                                            'selesai' => 'bg-success-subtle text-success',
                                            'batal' => 'bg-danger-subtle text-danger',
                                            default => 'bg-secondary-subtle text-secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td><span class="fw-medium">#ORD-{{ $order->id }}</span></td>
                                        <td>{{ $order->user->name ?? '-' }}</td>
                                        <td>
                                            {{ $order->menuItems->pluck('pivot.nama')->filter()->take(2)->implode(', ') }}
                                            @if ($order->menuItems->count() > 2)
                                                <span class="text-muted">+{{ $order->menuItems->count() - 2 }}
                                                    lainnya</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->table->name ?? '-' }}</td>
                                        <td>{{ $order->payment_method ? strtoupper($order->payment_method) : '-' }}
                                        </td>
                                        <td class="text-success">Rp
                                            {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                        <td><span
                                                class="badge {{ $statusBadge }}">{{ ucfirst($order->status) }}</span>
                                        </td>
                                        <td>{{ $order->created_at->format('d M Y, H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order->id) }}"
                                                class="btn btn-soft-info btn-sm">
                                                <i class="ri-eye-line"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            Tidak ada pesanan yang sesuai dengan filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-layouts.main>
