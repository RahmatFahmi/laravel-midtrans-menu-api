<?php

use App\Models\Category;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    // Properti Tabel
    public $search = '';
    protected $paginationTheme = 'bootstrap';

    // Properti Form
    public $categoryId, $name;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'name' => 'required|min:3|unique:categories,name,' . $this->categoryId,
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->isEdit = false;
        $this->reset(['categoryId', 'name']);
        $this->dispatch('open-modal');
    }

    public function edit($id)
    {
        $this->isEdit = true;
        $this->categoryId = $id;
        $category = Category::findOrFail($id);

        $this->name = $category->name;

        $this->dispatch('open-modal');
    }

    public function save()
    {
        $this->validate();

        try {
            $data = ['name' => $this->name];

            if ($this->isEdit) {
                Category::find($this->categoryId)->update($data);
                $message = 'Kategori ' . $this->name . ' berhasil diperbarui!';
            } else {
                Category::create($data);
                $message = 'Kategori ' . $this->name . ' berhasil ditambahkan!';
            }

            $this->dispatch('close-modal');
            $this->dispatch('swal:success', text: $message);
            $this->reset(['name', 'isEdit', 'categoryId']);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', text: 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete();
        $this->dispatch('swal:success', text: 'Kategori berhasil dihapus!');
    }

    public function with(): array
    {
        return [
            'categories' => Category::query()
                ->where('name', 'like', '%' . $this->search . '%')
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
            <input wire:model.live="search" type="text" class="form-control border-2" placeholder="Cari kategori..."
                style="padding-right: 40px;">
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
                        <th>Nama Kategori</th>
                        <th>Dibuat Pada</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $category)
                        <tr wire:key="cat-{{ $category->id }}">
                            <td class="ps-3">{{ $categories->firstItem() + $index }}</td>
                            <td class="fw-medium">{{ $category->name }}</td>
                            <td>{{ $category->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-soft-secondary btn-sm" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li>
                                            <a href="javascript:void(0)" wire:click="edit({{ $category->id }})"
                                                class="dropdown-item">
                                                <i class="ri-pencil-line me-2 text-info"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)"
                                                wire:click="$dispatch('confirm-delete', { id: {{ $category->id }}, name: '{{ $category->name }}' })"
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
                            <td colspan="4" class="text-center py-4 text-muted">Tidak ada data kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $categories->links() }}
    </div>

    <div wire:ignore.self class="modal fade" id="modalKaryawan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header {{ $isEdit ? 'bg-info' : 'bg-primary' }} py-3">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="{{ $isEdit ? 'ri-edit-line' : 'ri-add-box-line' }} me-2"></i>
                        {{ $isEdit ? 'Edit Kategori' : 'Kategori Baru' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Kategori</label>
                            <input type="text" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Masukan nama kategori...">
                            @error('name')
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
            const modal = new bootstrap.Modal(document.getElementById('modalKaryawan'));
            modal.show();
        });

        window.addEventListener('close-modal', event => {
            const modalElement = document.getElementById('modalKaryawan');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
        });

        window.addEventListener('execute-delete', event => {
            @this.call('destroy', event.detail.id);
        });
    </script>
</div>
