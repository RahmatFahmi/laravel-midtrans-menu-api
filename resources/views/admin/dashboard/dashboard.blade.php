<x-layouts.main title="Dashboard">
    <div class="col">

        <div class="h-100">
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1">Selamat Datang, {{ auth()->user()->name ?? 'Admin' }}!</h4>
                            <p class="text-muted mb-0">Berikut ringkasan aktivitas cafe hari ini.</p>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <a href="#" class="btn btn-soft-success material-shadow-none">
                                <i class="ri-add-circle-line align-middle me-1"></i>
                                Tambah Menu
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================================================
                 1. KARTU RINGKASAN ATAS
            ================================================== --}}
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                        Pendapatan Hari Ini</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5
                                        class="{{ $persenPendapatan >= 0 ? 'text-success' : 'text-danger' }} fs-14 mb-0">
                                        <i
                                            class="ri-arrow-right-{{ $persenPendapatan >= 0 ? 'up' : 'down' }}-line fs-13 align-middle"></i>
                                        {{ $persenPendapatan }} %
                                    </h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">Rp <span class="counter-value"
                                            data-target="{{ $totalPendapatanHariIni }}">0</span>
                                    </h4>
                                    <a href="#" class="text-decoration-underline">Lihat detail</a>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-success-subtle rounded fs-3">
                                        <i class="bx bx-dollar-circle text-success"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                        Pesanan Hari Ini</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="{{ $persenPesanan >= 0 ? 'text-success' : 'text-danger' }} fs-14 mb-0">
                                        <i
                                            class="ri-arrow-right-{{ $persenPesanan >= 0 ? 'up' : 'down' }}-line fs-13 align-middle"></i>
                                        {{ $persenPesanan }} %
                                    </h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                            data-target="{{ $totalPesananHariIni }}">0</span></h4>
                                    <a href="#" class="text-decoration-underline">Lihat semua
                                        pesanan</a>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-info-subtle rounded fs-3">
                                        <i class="bx bx-shopping-bag text-info"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                        Total Pelanggan</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                            data-target="{{ $totalPelanggan }}">0</span>
                                    </h4>
                                    <a href="#" class="text-decoration-underline">Lihat detail</a>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-warning-subtle rounded fs-3">
                                        <i class="bx bx-user-circle text-warning"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                        Meja Aktif</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                            data-target="{{ $mejaAktif }}">0</span>
                                    </h4>
                                    <a href="#" class="text-decoration-underline">Lihat antrean dapur</a>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-primary-subtle rounded fs-3">
                                        <i class="bx bx-table text-primary"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================================================
                 2. CHART PENDAPATAN & MEJA TERLARIS
            ================================================== --}}
            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Pendapatan 7 Hari Terakhir</h4>
                        </div>

                        <div class="card-header p-0 border-0 bg-light-subtle">
                            <div class="row g-0 text-center">
                                <div class="col-6 col-sm-3">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1"><span class="counter-value"
                                                data-target="{{ $totalOrderMingguIni }}">0</span></h5>
                                        <p class="text-muted mb-0">Pesanan</p>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1">Rp <span class="counter-value"
                                                data-target="{{ $totalPendapatanMingguIni }}">0</span></h5>
                                        <p class="text-muted mb-0">Pendapatan</p>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1"><span class="counter-value"
                                                data-target="{{ $totalBatalMingguIni }}">0</span></h5>
                                        <p class="text-muted mb-0">Dibatalkan</p>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="p-3 border border-dashed border-start-0 border-end-0">
                                        <h5 class="mb-1 text-success"><span class="counter-value"
                                                data-target="{{ $rasioSelesai }}">0</span>%</h5>
                                        <p class="text-muted mb-0">Rasio Selesai</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0 pb-2">
                            <div class="w-100">
                                <div id="revenue_chart" style="height: 320px" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Meja Paling Ramai</h4>
                        </div>

                        <div class="card-body">
                            @forelse ($mejaTerlaris as $meja)
                                @php
                                    $persen = round(($meja->total_order / $maxOrderMeja) * 100);
                                @endphp
                                <p class="mb-1">{{ $meja->table->name ?? 'Meja #' . $meja->table_id }}
                                    <span class="float-end">{{ $meja->total_order }} pesanan</span>
                                </p>
                                <div class="progress mt-2 mb-3" style="height: 6px;">
                                    <div class="progress-bar progress-bar-striped bg-primary" role="progressbar"
                                        style="width: {{ $persen }}%" aria-valuenow="{{ $persen }}"
                                        aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted text-center mb-0">Belum ada data pesanan.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================================================
                 3. MENU TERLARIS & MENU RATING TERTINGGI
            ================================================== --}}
            <div class="row">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Menu Terlaris</h4>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-hover table-centered align-middle table-nowrap mb-0">
                                    <tbody>
                                        @forelse ($menuTerlaris as $menu)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-light rounded p-1 me-2">
                                                            <img src="{{ $menu->image ? asset('storage/' . $menu->image) : asset('assets/images/products/img-1.png') }}"
                                                                alt="" class="img-fluid d-block rounded" />
                                                        </div>
                                                        <div>
                                                            <h5 class="fs-14 my-1">{{ $menu->name }}</h5>
                                                            <span class="text-muted">Rp
                                                                {{ number_format($menu->price, 0, ',', '.') }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 my-1 fw-normal">{{ $menu->total_terjual }}</h5>
                                                    <span class="text-muted">Terjual</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 my-1 fw-normal">Rp
                                                        {{ number_format($menu->total_pendapatan, 0, ',', '.') }}</h5>
                                                    <span class="text-muted">Pendapatan</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted py-4">Belum ada menu terjual.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Menu Rating Tertinggi</h4>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-centered table-hover align-middle table-nowrap mb-0">
                                    <tbody>
                                        @forelse ($menuRatingTertinggi as $menu)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            <img src="{{ $menu->image ? asset('storage/' . $menu->image) : asset('assets/images/products/img-1.png') }}"
                                                                alt="" class="avatar-sm p-1 rounded" />
                                                        </div>
                                                        <div>
                                                            <h5 class="fs-14 my-1 fw-medium">{{ $menu->name }}</h5>
                                                            <span
                                                                class="text-muted">{{ $menu->category->name ?? '-' }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-muted">{{ $menu->jumlah_rating }} rating</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 mb-0">{{ number_format($menu->rata_rata, 1) }}
                                                        <i
                                                            class="ri-star-fill text-warning fs-16 align-middle ms-1"></i>
                                                    </h5>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted py-4">Belum ada rating masuk.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================================================
                 4. METODE PEMBAYARAN & PESANAN TERBARU
            ================================================== --}}
            <div class="row">
                <div class="col-xl-4">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Metode Pembayaran</h4>
                        </div>
                        <div class="card-body">
                            <div id="payment_method_chart" style="height: 260px" dir="ltr"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Pesanan Terbaru</h4>
                            <div class="flex-shrink-0">
                                <a href="#" class="btn btn-soft-info btn-sm material-shadow-none">
                                    <i class="ri-file-list-3-line align-middle"></i> Lihat Semua
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                    <thead class="text-muted table-light">
                                        <tr>
                                            <th scope="col">Order ID</th>
                                            <th scope="col">Pelanggan</th>
                                            <th scope="col">Menu</th>
                                            <th scope="col">Meja</th>
                                            <th scope="col">Total</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentOrders as $order)
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
                                                <td>
                                                    <span
                                                        class="fw-medium link-primary">#ORD-{{ $order->id }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1">{{ $order->user->name ?? '-' }}</div>
                                                    </div>
                                                </td>
                                                <td>
                                                    {{ $order->menuItems->pluck('pivot.nama')->filter()->take(2)->implode(', ') }}
                                                    @if ($order->menuItems->count() > 2)
                                                        <span class="text-muted">+{{ $order->menuItems->count() - 2 }}
                                                            lainnya</span>
                                                    @endif
                                                </td>
                                                <td>{{ $order->table->name ?? '-' }}</td>
                                                <td>
                                                    <span class="text-success">Rp
                                                        {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge {{ $statusBadge }}">{{ ucfirst($order->status) }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">Belum ada
                                                    pesanan masuk.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                var revenueOptions = {
                    chart: {
                        height: 320,
                        type: 'area',
                        toolbar: {
                            show: false
                        }
                    },
                    series: [{
                        name: 'Pendapatan',
                        data: @json($chartData)
                    }],
                    xaxis: {
                        categories: @json($chartLabels)
                    },
                    colors: ['#405189'],
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: 0.4,
                            opacityTo: 0.1
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(val) {
                                return 'Rp ' + val.toLocaleString('id-ID');
                            }
                        }
                    }
                };
                var revenueChart = new ApexCharts(document.querySelector("#revenue_chart"), revenueOptions);
                revenueChart.render();

                var paymentLabels = @json($metodePembayaran->pluck('payment_method'));
                var paymentData = @json($metodePembayaran->pluck('total'));

                var paymentOptions = {
                    chart: {
                        height: 260,
                        type: 'donut'
                    },
                    series: paymentData,
                    labels: paymentLabels,
                    colors: ['#0ab39c', '#f06548', '#405189'],
                    legend: {
                        position: 'bottom'
                    }
                };
                var paymentChart = new ApexCharts(document.querySelector("#payment_method_chart"), paymentOptions);
                paymentChart.render();

            });
        </script>
    @endpush
</x-layouts.main>
