{{-- Form bersama Tambah & Edit Outlet. $outlet = null saat tambah. --}}
@php
    $edit = isset($outlet) && $outlet;
    $urlForm = $edit ? route('admin.outlet.update', $outlet->id_outlet) : route('admin.outlet.store');
@endphp

@push('styles')
    <style>
        .foto-box {
            border: 2px dashed #dfe3e8; border-radius: 14px; background: #fafbfc; min-height: 190px;
            display: flex; align-items: center; justify-content: center; overflow: hidden; color: #9aa1ad;
        }
        .foto-box img { width: 100%; max-height: 260px; object-fit: cover; display: block; }
        .foto-box .kosong { text-align: center; padding: 24px 12px; font-size: 13px; }
        .foto-box .kosong i { display: block; font-size: 2.2rem; margin-bottom: 6px; }
        .sticky-aksi { position: sticky; top: 90px; }
    </style>
@endpush

<form action="{{ $urlForm }}" method="POST" enctype="multipart/form-data" id="outlet-form">
    @csrf
    @if ($edit) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h3 class="card-title">Informasi Outlet</h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="nama_outlet" class="form-label required">Nama Outlet</label>
                            <input type="text" name="nama_outlet" id="nama_outlet"
                                class="form-control @error('nama_outlet') is-invalid @enderror"
                                value="{{ old('nama_outlet', $outlet->nama_outlet ?? '') }}"
                                maxlength="150" placeholder="Masukkan nama outlet" required>
                            @error('nama_outlet') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label for="alamat" class="form-label required">Alamat</label>
                            <textarea name="alamat" id="alamat" rows="3"
                                class="form-control @error('alamat') is-invalid @enderror"
                                placeholder="Masukkan alamat lengkap outlet" required>{{ old('alamat', $outlet->alamat ?? '') }}</textarea>
                            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-phone"></i></span>
                                <input type="text" name="nomor_telepon" id="nomor_telepon"
                                    class="form-control @error('nomor_telepon') is-invalid @enderror"
                                    value="{{ old('nomor_telepon', $outlet->nomor_telepon ?? '') }}"
                                    maxlength="20" placeholder="Contoh: 081234567890">
                                @error('nomor_telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Deskripsi Outlet</label>
                            <textarea name="deskripsi" id="deskripsi" rows="4"
                                class="form-control @error('deskripsi') is-invalid @enderror"
                                placeholder="Masukkan deskripsi outlet jika diperlukan">{{ old('deskripsi', $outlet->deskripsi ?? '') }}</textarea>
                            @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Lokasi</h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="link_google_maps" class="form-label">Link Google Maps</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-map-2"></i></span>
                                <input type="url" name="link_google_maps" id="link_google_maps"
                                    class="form-control @error('link_google_maps') is-invalid @enderror"
                                    value="{{ old('link_google_maps', $outlet->link_google_maps ?? '') }}"
                                    maxlength="2048" placeholder="https://maps.app.goo.gl/...">
                                @error('link_google_maps') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-hint">Tempelkan tautan berbagi lokasi dari Google Maps. Opsional.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="latitude" class="form-label">Latitude</label>
                            <input type="number" name="latitude" id="latitude"
                                class="form-control @error('latitude') is-invalid @enderror"
                                value="{{ old('latitude', $outlet->latitude ?? '') }}"
                                min="-90" max="90" step="0.00000001" placeholder="Contoh: -8.1723">
                            @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-hint">Rentang -90 sampai 90.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="longitude" class="form-label">Longitude</label>
                            <input type="number" name="longitude" id="longitude"
                                class="form-control @error('longitude') is-invalid @enderror"
                                value="{{ old('longitude', $outlet->longitude ?? '') }}"
                                min="-180" max="180" step="0.00000001" placeholder="Contoh: 113.7032">
                            @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-hint">Rentang -180 sampai 180.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sticky-aksi">
                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">Foto Outlet</h3></div>
                    <div class="card-body">
                        <div class="foto-box mb-3">
                            <img id="foto-preview" alt="Foto outlet"
                                @if ($edit && $outlet->foto) src="{{ asset('storage/' . $outlet->foto) }}" @else class="d-none" @endif>
                            <div class="kosong {{ $edit && $outlet->foto ? 'd-none' : '' }}" id="foto-kosong">
                                <i class="ti ti-photo-plus"></i>Belum ada foto
                            </div>
                        </div>

                        <input type="file" name="foto" id="foto"
                            class="form-control @error('foto') is-invalid @enderror"
                            accept=".jpg,.jpeg,.png,.webp">
                        @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-hint">
                            JPG, PNG, atau WEBP. Maksimal 2 MB.
                            @if ($edit) Kosongkan jika tidak ingin mengganti foto. @endif
                        </div>
                        <div id="foto-error" class="text-danger small mt-1 d-none"></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>{{ $edit ? 'Simpan Perubahan' : 'Simpan Outlet' }}
                        </button>
                        <a href="{{ route('admin.outlet.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('foto');
            const preview = document.getElementById('foto-preview');
            const kosong = document.getElementById('foto-kosong');
            const error = document.getElementById('foto-error');
            const awal = preview.getAttribute('src');
            const MAX = 2 * 1024 * 1024;
            const OK = ['image/jpeg', 'image/png', 'image/webp'];

            const tampil = (src) => {
                if (src) {
                    preview.src = src;
                    preview.classList.remove('d-none');
                    kosong.classList.add('d-none');
                } else {
                    preview.removeAttribute('src');
                    preview.classList.add('d-none');
                    kosong.classList.remove('d-none');
                }
            };

            input.addEventListener('change', () => {
                const file = input.files[0];
                error.classList.add('d-none');
                if (!file) { tampil(awal); return; }

                if (!OK.includes(file.type) || file.size > MAX) {
                    error.textContent = !OK.includes(file.type)
                        ? 'Format file harus JPG, JPEG, PNG, atau WEBP.'
                        : 'Ukuran foto maksimal 2 MB.';
                    error.classList.remove('d-none');
                    input.value = '';
                    tampil(awal);
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => tampil(e.target.result);
                reader.readAsDataURL(file);
            });
        });
    </script>
@endpush