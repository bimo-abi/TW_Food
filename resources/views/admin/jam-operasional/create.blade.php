@extends('admin.layouts.app')

@section('title', 'Tambah Jam Operasional - Admin TWFood')
@section('page-title', 'Tambah Jam Operasional')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pengelolaan Outlet</div>
                <h2 class="page-title">Tambah Jam Operasional</h2>
                <div class="text-secondary mt-1">
                    Outlet:
                    <span class="fw-bold">{{ $outlet->nama_outlet }}</span>
                </div>
            </div>

            <div class="col-auto">
                <a
                    href="{{ route('admin.jam-operasional.index', $outlet->id_outlet) }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-bold mb-2">Periksa kembali data berikut:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            <i class="ti ti-alert-circle me-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Jam Operasional</h3>
        </div>

        @if (count($hariTersedia) > 0)
            <form
                action="{{ route('admin.jam-operasional.store', $outlet->id_outlet) }}"
                method="POST"
            >
                @csrf

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="hari" class="form-label required">
                                Hari
                            </label>

                            <select
                                name="hari"
                                id="hari"
                                class="form-select @error('hari') is-invalid @enderror"
                                required
                            >
                                <option value="">-- Pilih Hari --</option>

                                @foreach ($hariTersedia as $hari)
                                    <option
                                        value="{{ $hari }}"
                                        @selected(old('hari') === $hari)
                                    >
                                        {{ ucfirst($hari) }}
                                    </option>
                                @endforeach
                            </select>

                            @error('hari')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="form-hint">
                                Hanya hari yang belum memiliki jadwal yang ditampilkan.
                            </small>
                        </div>

                        <div class="col-12">
                            <label class="form-check">
                                <input
                                    type="checkbox"
                                    name="tutup"
                                    value="1"
                                    id="tutup"
                                    class="form-check-input"
                                    @checked(old('tutup'))
                                >
                                <span class="form-check-label">
                                    Outlet Tutup
                                </span>
                            </label>

                            <small class="form-hint">
                                Centang jika outlet tidak beroperasi pada hari tersebut.
                            </small>
                        </div>

                        <div class="col-md-6" id="jam-buka-group">
                            <label for="jam_buka" class="form-label required">
                                Jam Buka
                            </label>

                            <input
                                type="time"
                                name="jam_buka"
                                id="jam_buka"
                                value="{{ old('jam_buka') }}"
                                class="form-control @error('jam_buka') is-invalid @enderror"
                            >

                            @error('jam_buka')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" id="jam-tutup-group">
                            <label for="jam_tutup" class="form-label required">
                                Jam Tutup
                            </label>

                            <input
                                type="time"
                                name="jam_tutup"
                                id="jam_tutup"
                                value="{{ old('jam_tutup') }}"
                                class="form-control @error('jam_tutup') is-invalid @enderror"
                            >

                            @error('jam_tutup')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    <a
                        href="{{ route('admin.jam-operasional.index', $outlet->id_outlet) }}"
                        class="btn btn-outline-secondary"
                    >
                        Batal
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>
                        Simpan Jam Operasional
                    </button>
                </div>
            </form>
        @else
            <div class="card-body text-center py-5">
                <i class="ti ti-calendar-check d-block mb-3"
                   style="font-size: 3rem;"></i>

                <h3 class="mb-1">Semua Hari Sudah Terdaftar</h3>

                <p class="text-secondary mb-3">
                    Outlet ini sudah memiliki jadwal untuk semua hari yang tersedia.
                </p>

                <a
                    href="{{ route('admin.jam-operasional.index', $outlet->id_outlet) }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali ke Jam Operasional
                </a>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tutupCheckbox = document.getElementById('tutup');
            const jamBukaGroup = document.getElementById('jam-buka-group');
            const jamTutupGroup = document.getElementById('jam-tutup-group');
            const jamBukaInput = document.getElementById('jam_buka');
            const jamTutupInput = document.getElementById('jam_tutup');

            if (
                !tutupCheckbox ||
                !jamBukaGroup ||
                !jamTutupGroup ||
                !jamBukaInput ||
                !jamTutupInput
            ) {
                return;
            }

            function updateJamField() {
                if (tutupCheckbox.checked) {
                    jamBukaGroup.style.display = 'none';
                    jamTutupGroup.style.display = 'none';

                    jamBukaInput.value = '';
                    jamTutupInput.value = '';

                    jamBukaInput.required = false;
                    jamTutupInput.required = false;
                } else {
                    jamBukaGroup.style.display = '';
                    jamTutupGroup.style.display = '';

                    jamBukaInput.required = true;
                    jamTutupInput.required = true;
                }
            }

            tutupCheckbox.addEventListener('change', updateJamField);
            updateJamField();
        });
    </script>
@endsection
