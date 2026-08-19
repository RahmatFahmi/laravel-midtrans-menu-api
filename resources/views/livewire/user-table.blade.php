<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    use WithPagination;

    // Properti Tabel
    public $search = '';
    protected $paginationTheme = 'bootstrap';

    // Properti Form
    public $userId, $name, $username, $email, $address, $password;
    public $role = 'karyawan';
    public $isEdit = false;

    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'username' => 'required|alpha_dash|unique:users,username,' . $this->userId,
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'address' => 'nullable|string|max:255',
            'password' => $this->isEdit ? 'nullable|min:6' : 'required|min:6',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->isEdit = false;
        $this->reset(['userId', 'name', 'username', 'email', 'address', 'password']);
        $this->dispatch('open-modal');
    }

    public function edit($id)
    {
        $this->isEdit = true;
        $this->userId = $id;
        $user = User::findOrFail($id);

        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->address = $user->address;
        $this->password = '';

        $this->dispatch('open-modal');
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
                'address' => $this->address,
            ];

            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }

            if ($this->isEdit) {
                User::find($this->userId)->update($data);
                $message = 'Data ' . $this->name . ' berhasil diperbarui!';
            } else {
                $data['role'] = $this->role;
                User::create($data);
                $message = 'Karyawan ' . $this->name . ' berhasil ditambahkan!';
            }

            $this->dispatch('close-modal');
            $this->dispatch('swal:success', text: $message);
            $this->reset(['name', 'username', 'email', 'address', 'password', 'isEdit', 'userId']);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', text: 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // FUNGSI EKSEKUSI HAPUS
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        $this->dispatch('swal:success', text: 'Data berhasil dihapus!');
    }

    public function with(): array
    {
        return [
            'users' => User::query()
                ->where('role', 'karyawan')
                ->where(function ($query) {
                    $query
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('username', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                })
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
                placeholder="Cari nama, user, atau email..." style="padding-right: 40px;">
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
                        <th class="ps-3">No.</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="ps-3">{{ $users->firstItem() + $index }}</td>
                            <td class="fw-medium">{{ $user->name }}</td>
                            <td><span class="text-primary">@</span>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="text-truncate" style="max-width: 150px;">{{ $user->address ?? '-' }}</td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-soft-secondary btn-sm" data-bs-toggle="dropdown"
                                        data-bs-boundary="viewport">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li>
                                            <a href="javascript:void(0)" wire:click="edit({{ $user->id }})"
                                                class="dropdown-item">
                                                <i class="ri-pencil-line me-2 text-info"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)"
                                                wire:click="$dispatch('confirm-delete', { id: {{ $user->id }}, name: '{{ $user->name }}' })"
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
                            <td colspan="7" class="text-center py-4 text-muted">Tidak ada data karyawan ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>

    <div wire:ignore.self class="modal fade" id="modalKaryawan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header {{ $isEdit ? 'bg-info' : 'bg-primary' }} py-3">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="{{ $isEdit ? 'ri-edit-line' : 'ri-user-add-line' }} me-2"></i>
                        {{ $isEdit ? 'Perbarui Data Karyawan' : 'Registrasi Karyawan Baru' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" wire:model="name"
                                    class="form-control @error('name') is-invalid @enderror">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="text" wire:model="username"
                                        class="form-control @error('username') is-invalid @enderror">
                                </div>
                                @error('username')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Alamat Email</label>
                                <input type="email" wire:model="email"
                                    class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password</label>
                                <input type="password" wire:model="password"
                                    class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Alamat Domisili</label>
                                <textarea wire:model="address" class="form-control @error('address') is-invalid @enderror" rows="2"></textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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
        // Trigger membuka modal
        window.addEventListener('open-modal', event => {
            const modalElement = document.getElementById('modalKaryawan');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        });

        // Trigger menutup modal
        window.addEventListener('close-modal', event => {
            const modalElement = document.getElementById('modalKaryawan');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
        });

        // --- JEMBATAN EKSEKUSI HAPUS (PENTING) ---
        window.addEventListener('execute-delete', event => {
            // Memanggil fungsi destroy di bagian PHP di atas
            @this.call('destroy', event.detail.id);
        });
    </script>
</div>
