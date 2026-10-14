@extends('admin.layouts.app')

@section('title', 'Jam Operasional - Admin TWFood')
@section('page-title', 'Jam Operasional')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pengelolaan Outlet</div>
                <h2 class="page-title">Jam Operasional</h2>
                <div class="text-secondary mt-1">
                    Outlet:
                    <span class="fw-bold">{{ $outlet->nama_outlet }}</span>
                </div>
            </div>

            <div class="col-auto">
                <a
                    href="{{ route('admin.jam-operasional.create', $outlet->id_outlet) }}"
                    class="btn btn-primary"
                >
                    <i class="ti ti-plus me-1"></i>
                    Tambah Jam Operasional
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            <i class="ti ti-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            <i class="ti ti-alert-circle me-2"></i>
            {{ session('error') }}
        </div>
    @endif

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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Jam Operasional</h3>
        </div>

        @if ($jamOperasional->isEmpty())
            <div class="card-body text-center py-5">
                <i class="ti ti-clock d-block mb-3"
                   style="font-size: 3rem;"></i>

                <h3 class="mb-1">Belum Ada Jam Operasional</h3>

                <p class="text-secondary mb-3">
                    Tambahkan jadwal operasional untuk outlet ini.
                </p>

                <a
                    href="{{ route('admin.jam-operasional.create', $outlet->id_outlet) }}"
                    class="btn btn-primary"
                >
                    <i class="ti ti-plus me-1"></i>
                    Tambah Jam Operasional
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No.</th>
                            <th>Hari</th>
                            <th>Jam Buka</th>
                            <th>Jam Tutup</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($jamOperasional as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td class="fw-bold">
                                    {{ ucfirst($item->hari) }}
                                </td>

                                <td class="text-nowrap">
                                    @if ($item->tutup)
                                        -
                                    @else
                                        {{ $item->jam_buka
                                            ? substr($item->jam_buka, 0, 5)
                                            : '-' }}
                                    @endif
                                </td>

                                <td class="text-nowrap">
                                    @if ($item->tutup)
                                        -
                                    @else
                                        {{ $item->jam_tutup
                                            ? substr($item->jam_tutup, 0, 5)
                                            : '-' }}
                                    @endif
                                </td>

                                <td>
                                    @if ($item->tutup)
                                        <span class="badge bg-danger-lt">
                                            <i class="ti ti-lock me-1"></i>
                                            Tutup
                                        </span>
                                    @else
                                        <span class="badge bg-success-lt">
                                            <i class="ti ti-check me-1"></i>
                                            Buka
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex flex-nowrap align-items-center justify-content-center gap-2">
                                        <a
                                            href="{{ route('admin.jam-operasional.edit', $item->id_jam_operasional) }}"
                                            class="btn btn-sm btn-outline-primary text-nowrap"
                                        >
                                            <i class="ti ti-edit me-1"></i>
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route('admin.jam-operasional.destroy', $item->id_jam_operasional) }}"
                                            method="POST"
                                            class="d-inline-flex flex-shrink-0"
                                            onsubmit="return confirm('Yakin ingin menghapus jam operasional ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger text-nowrap"
                                            >
                                                <i class="ti ti-trash me-1"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-3">
        <a
            href="{{ route('admin.outlet.index') }}"
            class="btn btn-outline-secondary"
        >
            <i class="ti ti-arrow-left me-1"></i>
            Kembali ke Outlet
        </a>
    </div>
@endsection
