<?php

use App\Models\Table;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    // Properti Tabel
    public $search = '';
    protected $paginationTheme = 'bootstrap';

    // Properti Form
    public $tableId, $name, $code;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'name' => 'required|min:2',
            'code' => 'required|unique:tables,code,' . $this->tableId,
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->isEdit = false;
        $this->reset(['tableId', 'name', 'code']);
        $this->dispatch('open-modal');
    }

    public function edit($id)
    {
        $this->isEdit = true;
        $this->tableId = $id;
        $table = Table::findOrFail($id);

        $this->name = $table->name;
        $this->code = $table->code;

        $this->dispatch('open-modal');
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'code' => $this->code,
            ];

            if ($this->isEdit) {
                Table::find($this->tableId)->update($data);
                $message = 'Meja ' . $this->name . ' berhasil diperbarui!';
            } else {
                Table::create($data);
                $message = 'Meja baru berhasil ditambahkan!';
            }

            $this->dispatch('close-modal');
            $this->dispatch('swal:success', text: $message);
            $this->reset(['name', 'code', 'isEdit', 'tableId']);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', text: 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        Table::findOrFail($id)->delete();
        $this->dispatch('swal:success', text: 'Meja berhasil dihapus!');
    }

    public function with(): array
    {
        return [
            'tables' => Table::query()
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(5),
        ];
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <button type="button" wire:click="create" class="btn btn-primary d-inline-flex align-items-center shadow-sm">
            <i class="ri-add-line me-1"></i> Tambah
        </button>

        <div class="position-relative" style="width: 280px;">
            <input wire:model.live="search" type="text" class="form-control border-2"
                placeholder="Cari meja atau kode..." style="padding-right: 40px;">
            <span class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted">
                <i class="ri-search-2-line"></i>
            </span>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive" style="min-height: 180px;">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" width="80">No.</th>
                        <th>Nama Meja</th>
                        <th>Kode Meja</th>
                        <th>Dibuat Pada</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tables as $index => $item)
                        <tr wire:key="table-{{ $item->id }}">
                            <td class="ps-3">{{ $tables->firstItem() + $index }}</td>
                            <td class="fw-medium">{{ $item->name }}</td>
                            <td><span class="badge bg-soft-primary text-primary">{{ $item->code }}</span></td>
                            <td>{{ $item->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-soft-secondary btn-sm" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li>
                                            <a href="javascript:void(0)" wire:click="edit({{ $item->id }})"
                                                class="dropdown-item">
                                                <i class="ri-pencil-line me-2 text-info"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)"
                                                wire:click="$dispatch('confirm-delete', { id: {{ $item->id }}, name: '{{ $item->name }}' })"
                                                class="dropdown-item text-danger">
                                                <i class="ri-delete-bin-line me-2"></i> Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Tidak ada data meja.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $tables->links() }}
    </div>

    <div wire:ignore.self class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header {{ $isEdit ? 'bg-info' : 'bg-primary' }} py-3">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="{{ $isEdit ? 'ri-edit-line' : 'ri-add-box-line' }} me-2"></i>
                        {{ $isEdit ? 'Edit Data Meja' : 'Tambah Meja Baru' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Meja</label>
                            <input type="text" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Contoh: Meja Teras 01">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kode Meja (Unique)</label>
                            <input type="text" wire:model="code"
                                class="form-control @error('code') is-invalid @enderror" placeholder="Contoh: T-01">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-outline-secondary px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn {{ $isEdit ? 'btn-info text-white' : 'btn-primary' }} px-4">
                            <i class="ri-save-line me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('open-modal', event => {
            const modal = new bootstrap.Modal(document.getElementById('modalForm'));
            modal.show();
        });

        window.addEventListener('close-modal', event => {
            const modalElement = document.getElementById('modalForm');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
        });

        window.addEventListener('execute-delete', event => {
            @this.call('destroy', event.detail.id);
        });
    </script>
</div>
