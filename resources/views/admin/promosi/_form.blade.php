@php
    $nilaiLama = fn($k, $default = null) => old($k, $promosi->{$k} ?? $default);
    $terpilih = collect(old('varian', $varianTerpilih))->map(fn($v) => (int) $v)->all();
    $jenis = $nilaiLama('jenis_promosi', 'diskon_persen');
    $mulai = old('tanggal_mulai', optional($promosi->tanggal_mulai)->toDateString());
    $selesai = old('tanggal_selesai', optional($promosi->tanggal_selesai)->toDateString());
    $persen = old('nilai_persen', $promosi->nilai_persen !== null ? (float) $promosi->nilai_persen : null);
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-header"><h3 class="card-title">Tanggal Kembar (opsional)</h3></div>
    <div class="card-body">
        <label class="form-label" for="tgl-kembar">Pilih untuk mengisi nama &amp; periode otomatis</label>
        <select id="tgl-kembar" class="form-select">
            <option value="">— Promo biasa (isi manual) —</option>
            @foreach ($tanggalKembar as $t)
                <option value="{{ $t['tanggal'] }}" data-kode="{{ $t['kode'] }}">{{ $t['label'] }}</option>
            @endforeach
        </select>
        <div class="form-hint">Periode otomatis 1 hari. Tanggal selesai bisa kamu ubah kalau promo mau berlangsung lebih lama.</div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3 class="card-title">Detail Promosi</h3></div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label required" for="nama_promosi">Nama Promosi</label>
            <input type="text" id="nama_promosi" name="nama_promosi" maxlength="150" class="form-control"
                   value="{{ old('nama_promosi', $promosi->nama_promosi) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label" for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="2" maxlength="1000"
                      class="form-control">{{ old('deskripsi', $promosi->deskripsi) }}</textarea>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label required" for="jenis_promosi">Jenis</label>
                <select id="jenis_promosi" name="jenis_promosi" class="form-select">
                    <option value="diskon_persen" @selected($jenis === 'diskon_persen')>Diskon Persen (%)</option>
                    <option value="diskon_nominal" @selected($jenis === 'diskon_nominal')>Diskon Nominal (Rp potongan)</option>
                    <option value="harga_khusus" @selected($jenis === 'harga_khusus')>Harga Khusus (Rp harga jadi)</option>
                </select>
            </div>
            <div class="col-md-4" id="box-persen">
                <label class="form-label required" for="nilai_persen">Nilai Diskon (%)</label>
                <input type="number" step="0.01" min="0.01" max="100" id="nilai_persen" name="nilai_persen"
                       class="form-control" value="{{ $persen }}">
            </div>
            <div class="col-md-4" id="box-nominal">
                <label class="form-label required" for="nilai_nominal" id="label-nominal">Nilai (Rp)</label>
                <input type="number" min="1" max="16777215" id="nilai_nominal" name="nilai_nominal"
                       class="form-control" value="{{ old('nilai_nominal', $promosi->nilai_nominal) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="minimal_pembelian">Minimal Pembelian (item)</label>
                <input type="number" min="1" max="65535" id="minimal_pembelian" name="minimal_pembelian"
                       class="form-control" value="{{ old('minimal_pembelian', $promosi->minimal_pembelian) }}"
                       placeholder="Kosongkan jika tanpa minimal">
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label required" for="tanggal_mulai">Tanggal Mulai</label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control"
                       value="{{ $mulai }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label required" for="tanggal_selesai">Tanggal Selesai</label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control"
                       value="{{ $selesai }}" required>
            </div>
        </div>

        <label class="form-check form-switch">
            <input type="checkbox" name="status_aktif" value="1" class="form-check-input"
                   @checked(old('status_aktif', $promosi->status_aktif ?? true))>
            <span class="form-check-label">Promosi aktif</span>
        </label>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3 class="card-title">Varian Produk yang Kena Promo</h3></div>
    <div class="card-body" style="max-height: 360px; overflow:auto;">
        @forelse ($daftarVarian as $namaProduk => $list)
            <div class="mb-3">
                <div class="fw-semibold mb-1">{{ $namaProduk }}</div>
                @foreach ($list as $v)
                    <label class="form-check form-check-inline">
                        <input type="checkbox" class="form-check-input" name="varian[]"
                               value="{{ $v->id_varian }}" @checked(in_array($v->id_varian, $terpilih))>
                        <span class="form-check-label">{{ $v->nama_varian }}</span>
                    </label>
                @endforeach
            </div>
        @empty
            <div class="text-secondary">Belum ada varian aktif.</div>
        @endforelse
    </div>
</div>

<div class="d-flex gap-2 mb-4">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('admin.promosi.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var jenis = document.getElementById('jenis_promosi');
    var boxPersen = document.getElementById('box-persen');
    var boxNominal = document.getElementById('box-nominal');
    var labelNominal = document.getElementById('label-nominal');

    function aturJenis() {
        var persen = jenis.value === 'diskon_persen';
        boxPersen.style.display = persen ? '' : 'none';
        boxNominal.style.display = persen ? 'none' : '';
        labelNominal.textContent = jenis.value === 'harga_khusus' ? 'Harga Khusus (Rp)' : 'Potongan (Rp)';
    }
    jenis.addEventListener('change', aturJenis);
    aturJenis();

    document.getElementById('tgl-kembar').addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        if (!this.value) return;
        document.getElementById('tanggal_mulai').value = this.value;
        document.getElementById('tanggal_selesai').value = this.value;
        var nama = document.getElementById('nama_promosi');
        if (!nama.value.trim()) nama.value = 'Promo Tanggal Kembar ' + opt.dataset.kode;
    });
});
</script>