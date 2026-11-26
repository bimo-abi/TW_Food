{{-- Form bersama untuk Tambah & Edit Resep. $resep = null saat tambah. --}}
@php
    $edit = isset($resep) && $resep;
    $urlForm = $edit ? route('admin.resep.update', $resep->id_resep) : route('admin.resep.store');
@endphp

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <style>
        #deskripsi-editor { border-radius: 0 0 8px 8px; overflow: hidden; border: 1px solid #dfe3e8; }
        #deskripsi-editor .ql-toolbar.ql-snow { border: none; border-bottom: 1px solid #dfe3e8; background: #f8f9fb; }
        #deskripsi-editor .ql-container.ql-snow { border: none; font-size: 1rem; }
        #deskripsi-editor .ql-editor { min-height: 180px; padding: 12px 16px; }
        #deskripsi-editor.border-danger { border-color: #d63939 !important; }
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

<form action="{{ $urlForm }}" method="POST" enctype="multipart/form-data" id="resep-form">
    @csrf
    @if ($edit) @method('PUT') @endif

    <div class="row g-4">
        {{-- ===== Kolom kiri ===== --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h3 class="card-title">Informasi Resep</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="judul" class="form-label required">Judul Resep</label>
                        <input type="text" name="judul" id="judul"
                            class="form-control @error('judul') is-invalid @enderror"
                            value="{{ old('judul', $resep->judul ?? '') }}"
                            placeholder="Contoh: Tumis Jamur Tiram" maxlength="100" required>
                        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi-editor" class="form-label">Deskripsi</label>
                        <div id="deskripsi-editor" class="bg-white @error('deskripsi') border border-danger rounded @enderror"></div>
                        <textarea name="deskripsi" id="deskripsi" class="d-none">{{ old('deskripsi', $resep->deskripsi ?? '') }}</textarea>
                        @error('deskripsi') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-0">
                        <label for="waktu_memasak" class="form-label">Waktu Memasak</label>
                        <div class="input-group" style="max-width: 260px;">
                            <input type="number" name="waktu_memasak" id="waktu_memasak"
                                class="form-control @error('waktu_memasak') is-invalid @enderror"
                                value="{{ old('waktu_memasak', $resep->waktu_memasak ?? '') }}"
                                min="1" max="1440" placeholder="Contoh: 30">
                            <span class="input-group-text">menit</span>
                            @error('waktu_memasak') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-hint">1 sampai 1.440 menit. Boleh dikosongkan.</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Bahan &amp; Langkah</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="bahan" class="form-label">Bahan</label>
                        <textarea name="bahan" id="bahan" rows="6" maxlength="10000"
                            class="form-control @error('bahan') is-invalid @enderror"
                            placeholder="Tuliskan bahan-bahan yang diperlukan, satu bahan per baris">{{ old('bahan', $resep->bahan ?? '') }}</textarea>
                        @error('bahan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-0">
                        <label for="langkah_pembuatan" class="form-label">Langkah Pembuatan</label>
                        <textarea name="langkah_pembuatan" id="langkah_pembuatan" rows="8" maxlength="10000"
                            class="form-control @error('langkah_pembuatan') is-invalid @enderror"
                            placeholder="Tuliskan langkah-langkah pembuatan resep">{{ old('langkah_pembuatan', $resep->langkah_pembuatan ?? '') }}</textarea>
                        @error('langkah_pembuatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Kolom kanan ===== --}}
        <div class="col-lg-4">
            <div class="sticky-aksi">
                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">Foto Resep</h3></div>
                    <div class="card-body">
                        <div class="foto-box mb-3" id="foto-box">
                            <img id="foto-preview" alt="Foto resep"
                                @if ($edit && $resep->foto) src="{{ asset('storage/' . $resep->foto) }}" @else class="d-none" @endif>
                            <div class="kosong {{ $edit && $resep->foto ? 'd-none' : '' }}" id="foto-kosong">
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
                            <i class="ti ti-device-floppy me-1"></i>{{ $edit ? 'Simpan Perubahan' : 'Simpan Resep' }}
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#preview-modal">
                            <i class="ti ti-eye me-1"></i>Pratinjau Pelanggan
                        </button>
                        <a href="{{ route('admin.resep.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Modal pratinjau pelanggan --}}
<div class="modal modal-blur fade" id="preview-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pratinjau Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <img id="preview-foto" src="" alt="" class="img-fluid rounded mb-3 d-none">
                <h2 id="preview-judul" class="mb-2"></h2>
                <p class="text-secondary" id="preview-waktu"></p>
                <div id="preview-deskripsi" class="mb-4 ql-editor p-0"></div>
                <h4>Bahan</h4>
                <div id="preview-bahan" class="mb-4" style="white-space: pre-wrap;"></div>
                <h4>Langkah Pembuatan</h4>
                <div id="preview-langkah" style="white-space: pre-wrap;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('resep-form');
            const deskripsiInput = document.getElementById('deskripsi');

            const quill = new Quill('#deskripsi-editor', {
                theme: 'snow',
                placeholder: 'Masukkan deskripsi resep',
                modules: { toolbar: [['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link'], ['clean']] },
            });

            if (deskripsiInput.value.trim() !== '') {
                quill.clipboard.dangerouslyPasteHTML(deskripsiInput.value);
            }

            form.addEventListener('submit', () => {
                deskripsiInput.value = quill.getText().trim() === '' ? '' : quill.root.innerHTML;
            });

            // Foto: validasi + pratinjau
            const fotoInput = document.getElementById('foto');
            const fotoPreview = document.getElementById('foto-preview');
            const fotoKosong = document.getElementById('foto-kosong');
            const fotoError = document.getElementById('foto-error');
            const fotoAwal = fotoPreview.getAttribute('src'); // foto yang sudah tersimpan (mode edit)
            const MAX_SIZE = 2 * 1024 * 1024;
            const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];

            const tampilFoto = (src) => {
                if (src) {
                    fotoPreview.src = src;
                    fotoPreview.classList.remove('d-none');
                    fotoKosong.classList.add('d-none');
                } else {
                    fotoPreview.removeAttribute('src');
                    fotoPreview.classList.add('d-none');
                    fotoKosong.classList.remove('d-none');
                }
            };

            fotoInput.addEventListener('change', () => {
                const file = fotoInput.files[0];
                fotoError.classList.add('d-none');

                if (!file) { tampilFoto(fotoAwal); return; }

                if (!ALLOWED.includes(file.type)) {
                    fotoError.textContent = 'Format file harus JPG, JPEG, PNG, atau WEBP.';
                    fotoError.classList.remove('d-none');
                    fotoInput.value = '';
                    tampilFoto(fotoAwal);
                    return;
                }
                if (file.size > MAX_SIZE) {
                    fotoError.textContent = 'Ukuran foto maksimal 2 MB.';
                    fotoError.classList.remove('d-none');
                    fotoInput.value = '';
                    tampilFoto(fotoAwal);
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => tampilFoto(e.target.result);
                reader.readAsDataURL(file);
            });

            // Pratinjau pelanggan
            document.getElementById('preview-modal').addEventListener('show.bs.modal', () => {
                const judul = document.getElementById('judul').value.trim();
                const waktu = document.getElementById('waktu_memasak').value.trim();
                const deskripsiHtml = quill.getText().trim() === '' ? '' : quill.root.innerHTML;

                document.getElementById('preview-judul').textContent = judul || 'Judul resep';
                document.getElementById('preview-waktu').textContent = waktu ? `⏱ ${waktu} menit` : '';
                document.getElementById('preview-deskripsi').innerHTML =
                    deskripsiHtml || '<p class="text-secondary">Belum ada deskripsi.</p>';
                document.getElementById('preview-bahan').textContent = document.getElementById('bahan').value.trim() || '-';
                document.getElementById('preview-langkah').textContent = document.getElementById('langkah_pembuatan').value.trim() || '-';

                const pf = document.getElementById('preview-foto');
                if (!fotoPreview.classList.contains('d-none') && fotoPreview.getAttribute('src')) {
                    pf.src = fotoPreview.src;
                    pf.alt = judul;
                    pf.classList.remove('d-none');
                } else {
                    pf.classList.add('d-none');
                }
            });
        });
    </script>
@endpush