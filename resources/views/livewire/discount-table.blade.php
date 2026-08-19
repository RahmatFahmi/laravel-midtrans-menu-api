<?php

use App\Models\Discount;
use App\Models\MenuItem;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $menuId;

    // Properti Tabel
    public $search = '';
    protected $paginationTheme = 'bootstrap';

    // Properti Form - Fokus pada Persentase
    public $discountId,
        $menu_item_id,
        $name,
        $amount,
        $status = 'active';
    public $isEdit = false;

    public function mount($menuId = null)
    {
        $this->menuId = $menuId;
        $this->menu_item_id = $menuId;
    }

    protected function rules()
    {
        return [
            'menu_item_id' => 'required|exists:menu_items,id',
            'name' => 'required|min:3',
            'amount' => 'required|numeric|min:1|max:100', // Validasi 1-100%
            'status' => 'required|in:active,inactive',
        ];
    }

    public function create()
    {
        $this->isEdit = false;
        $this->reset(['discountId', 'name', 'amount', 'status']);
        $this->menu_item_id = $this->menuId;
        $this->status = 'active';
        $this->dispatch('open-modal');
    }

    public function edit($id)
    {
        $this->isEdit = true;
        $this->discountId = $id;
        $discount = Discount::findOrFail($id);

        $this->menu_item_id = $discount->menu_item_id;
        $this->name = $discount->name;
        $this->amount = $discount->amount;
        $this->status = $discount->status;

        $this->dispatch('open-modal');
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'menu_item_id' => $this->menu_item_id,
                'name' => $this->name,
                'amount' => $this->amount,
                'status' => $this->status,
                'type' => 'percentage', // Selalu set ke percentage
            ];

            if ($this->isEdit) {
                Discount::find($this->discountId)->update($data);
                $msg = 'Diskon diperbarui!';
            } else {
                Discount::create($data);
                $msg = 'Diskon berhasil dibuat!';
            }

            $this->dispatch('close-modal');
            $this->dispatch('swal:success', text: $msg);
            $this->reset(['discountId', 'name', 'amount', 'status']);
            $this->menu_item_id = $this->menuId;
        } catch (\Exception $e) {
            $this->dispatch('swal:error', text: 'Gagal: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        Discount::findOrFail($id)->delete();
        $this->dispatch('swal:success', text: 'Diskon dihapus!');
    }

    public function with(): array
    {
        return [
            'discounts' => Discount::where('menu_item_id', $this->menuId)
                ->where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(5),
            'currentMenu' => MenuItem::find($this->menuId),
        ];
    }
}; ?>

<div class="p-3">
    <div class="d-flex justify-content-between align-items-center mb-4 px-1">
        <button wire:click="create" class="btn btn-primary shadow-sm px-4">
            <i class="ri-percent-line me-1"></i> Tambah Diskon
        </button>

        <div class="position-relative" style="width: 300px;">
            <input wire:model.live="search" type="text" class="form-control border-2 ps-3 pe-5"
                placeholder="Cari nama promo...">
            <span class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted">
                <i class="ri-search-line fs-5"></i>
            </span>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 fw-bold text-muted" style="font-size: 0.85rem;">NAMA PROMO</th>
                        <th class="py-3 fw-bold text-muted" style="font-size: 0.85rem;">POTONGAN</th>
                        <th class="py-3 fw-bold text-muted text-center" style="font-size: 0.85rem;">STATUS</th>
                        <th class="py-3 fw-bold text-muted text-center" style="font-size: 0.85rem;">DIBUAT</th>
                        <th class="py-3 fw-bold text-muted text-end pe-4" style="font-size: 0.85rem;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($discounts as $item)
                        <tr>
                            <td class="ps-4 fw-medium">{{ $item->name }}</td>
                            <td>
                                <span class="badge bg-soft-danger text-danger px-3 py-2 fw-bold">
                                    {{ $item->amount }}% OFF
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($item->status == 'active')
                                    <span
                                        class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3">Active</span>
                                @else
                                    <span
                                        class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-3">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center text-muted small">{{ $item->created_at->format('d M Y') }}</td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm shadow-sm border rounded-pill px-3"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                                        <li>
                                            <h6 class="dropdown-header small text-uppercase text-muted">Opsi Promo</h6>
                                        </li>
                                        <li>
                                            <button class="dropdown-item py-2" wire:click="edit({{ $item->id }})">
                                                <i class="ri-pencil-line me-2 text-warning"></i> Edit Diskon
                                            </button>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <button class="dropdown-item py-2 text-danger"
                                                wire:click="$dispatch('confirm-delete', { id: {{ $item->id }}, name: '{{ $item->name }}' })">
                                                <i class="ri-delete-bin-line me-2"></i> Hapus Permanen
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="ri-coupon-2-line fs-1 d-block mb-2 opacity-25"></i>
                                Belum ada promo untuk menu ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 px-1">
        {{ $discounts->links() }}
    </div>

    <div wire:ignore.self class="modal fade" id="modalForm" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="ri-price-tag-3-line me-2"></i>
                        {{ $isEdit ? 'Update Diskon' : 'Tambah Diskon' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-4 p-3 bg-light rounded-3 border border-dashed text-center">
                            <small class="text-muted d-block mb-1 text-uppercase fw-bold"
                                style="letter-spacing: 1px;">Item Menu</small>
                            <span class="fw-bold fs-6 text-dark">{{ $currentMenu->name ?? '-' }}</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Nama Promo</label>
                            <input type="text" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Contoh: Diskon Spesial Akhir Pekan">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Besar Potongan (%)</label>
                            <div class="input-group input-group-lg">
                                <input type="number" wire:model="amount"
                                    class="form-control @error('amount') is-invalid @enderror" placeholder="10">
                                <span class="input-group-text bg-primary text-white border-0 px-4 fw-bold">%</span>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted mt-1 d-block italic">Masukkan angka antara 1 sampai 100.</small>
                        </div>

                        <div class="mt-4">
                            <label class="form-label fw-bold small text-muted text-uppercase d-block mb-2">Status
                                Aktivasi</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="status" value="active"
                                        id="stActive">
                                    <label class="form-check-label fw-medium" for="stActive text-success">Aktif</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="status"
                                        value="inactive" id="stInactive">
                                    <label class="form-check-label fw-medium"
                                        for="stInactive text-muted">Non-Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 p-4 pt-0">
                        <button type="button" class="btn btn-link text-decoration-none text-muted px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-5 shadow-sm rounded-pill">
                            <i class="ri-save-line me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .bg-soft-danger {
            background-color: rgba(239, 68, 68, 0.1);
        }

        .bg-success-subtle {
            background-color: #e6f4ea;
        }

        .bg-secondary-subtle {
            background-color: #f1f3f4;
        }

        .btn-white {
            background-color: #fff;
            border-color: #eee;
        }

        .btn-white:hover {
            background-color: #f8f9fa;
            border-color: #ddd;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }

        .modal-content {
            border-radius: 20px;
        }

        .form-control {
            border-radius: 10px;
        }
    </style>

    <script>
        window.addEventListener('open-modal', () => {
            let modal = new bootstrap.Modal(document.getElementById('modalForm'));
            modal.show();
        });
        window.addEventListener('close-modal', () => {
            let modalElement = document.getElementById('modalForm');
            let modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
        });
        window.addEventListener('execute-delete', e => @this.call('destroy', e.detail.id));
    </script>
</div>
