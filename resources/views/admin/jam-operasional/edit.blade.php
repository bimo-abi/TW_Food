@extends('admin.layouts.app')

@section('title', 'Edit Jam Operasional - Admin TWFood')
@section('page-title', 'Edit Jam Operasional')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pengelolaan Outlet</div>
                <h2 class="page-title">Edit Jam Operasional</h2>
                <div class="text-secondary mt-1">
                    Outlet:
                    <span class="fw-bold">
                        {{ $jamOperasional->outlet->nama_outlet }}
                    </span>
                </div>
            </div>

            <div class="col-auto">
                <a
                    href="{{ route('admin.jam-operasional.index', $jamOperasional->id_outlet) }}"
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
            <div class="fw-bold mb-2">
                Periksa kembali data berikut:
            </div>

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
            <h3 class="card-title">Form Edit Jam Operasional</h3>
        </div>

        <form
            action="{{ route('admin.jam-operasional.update', $jamOperasional->id_jam_operasional) }}"
            method="POST"
        >
            @csrf
            @method('PUT')

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
                            @foreach ([
                                'senin' => 'Senin',
                                'selasa' => 'Selasa',
                                'rabu' => 'Rabu',
                                'kamis' => 'Kamis',
                                'jumat' => 'Jumat',
                                'sabtu' => 'Sabtu',
                                'minggu' => 'Minggu',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(old('hari', $jamOperasional->hari) === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        @error('hari')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-check">
                            <input
                                type="checkbox"
                                name="tutup"
                                value="1"
                                id="tutup"
                                class="form-check-input"
                                @checked(old('tutup', $jamOperasional->tutup))
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
                            value="{{ old('jam_buka', $jamOperasional->jam_buka) ? substr(old('jam_buka', $jamOperasional->jam_buka), 0, 5) : '' }}"
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
                            value="{{ old('jam_tutup', $jamOperasional->jam_tutup) ? substr(old('jam_tutup', $jamOperasional->jam_tutup), 0, 5) : '' }}"
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
                    href="{{ route('admin.jam-operasional.index', $jamOperasional->id_outlet) }}"
                    class="btn btn-outline-secondary"
                >
                    Batal
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
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
