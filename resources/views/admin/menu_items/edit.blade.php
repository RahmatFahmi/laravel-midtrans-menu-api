<x-layouts.main title="Edit Menu">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info py-3">
                    <h5 class="card-title mb-0 text-white">Edit Menu: {{ $menu->name }}</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.menu.update', $menu->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold d-block">Foto Menu Saat Ini</label>
                                <img src="{{ $menu->image_url }}" class="rounded mb-2 border p-1" width="120"
                                    height="120" style="object-fit: cover;">
                                <input type="file" name="image"
                                    class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                <small class="text-muted">Kosongkan jika tidak ingin mengganti foto.</small>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nama Menu</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $menu->name) }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Kategori</label>
                                <select name="category_id"
                                    class="form-select @error('category_id') is-invalid @enderror">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id', $menu->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Harga (Rp)</label>
                                <input type="number" name="price"
                                    class="form-control @error('price') is-invalid @enderror"
                                    value="{{ old('price', (int) $menu->price) }}">
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Waktu Persiapan (Menit)</label>
                                <input type="text" name="preparation_time" class="form-control"
                                    value="{{ old('preparation_time', $menu->preparation_time) }}">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $menu->description) }}</textarea>
                            </div>

                            <div class="col-md-12 mb-4">
                                <div class="form-check form-switch form-switch-lg">
                                    <input type="hidden" name="is_available" value="0">
                                    <input type="checkbox" name="is_available" value="1" class="form-check-input"
                                        id="is_available" {{ $menu->is_available ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_available">Tersedia untuk dipesan</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end border-top pt-4">
                            <a href="{{ route('admin.menu.index') }}" class="btn btn-light px-4">Batal</a>
                            <button type="submit" class="btn btn-info text-white px-4">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.main>
