{{--
    Switch aktif / nonaktif (dipakai ulang di halaman Produk, Varian, dll.)

    Variabel:
      $action : URL route toggle-status
      $aktif  : status saat ini (true/false)
      $nama   : (opsional) 'produk', 'varian', dll. untuk tooltip
--}}
@php
    $aktif = (bool) $aktif;
    $nama  = $nama ?? 'item';
    $aksi  = $aktif ? 'Nonaktifkan' : 'Aktifkan';
@endphp

<form action="{{ $action }}" method="POST" class="status-switch-form">
    @csrf
    @method('PATCH')

    <div class="form-check form-switch status-switch" title="{{ $aksi }} {{ $nama }}">
        <input type="checkbox"
               class="form-check-input"
               role="switch"
               aria-label="{{ $aksi }} {{ $nama }}"
               @checked($aktif)
               onchange="this.form.submit()">
        <span class="status-switch-label {{ $aktif ? 'is-aktif' : 'is-nonaktif' }}">
            {{ $aktif ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>

    <noscript>
        <button type="submit" class="btn btn-sm btn-outline-warning ms-2">Simpan</button>
    </noscript>
</form>
