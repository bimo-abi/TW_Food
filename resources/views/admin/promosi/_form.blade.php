@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/promosi.css') }}">
@endpush

@php
    $terpilih = collect(old('varian', $varianTerpilih))->map(fn($v) => (int) $v)->all();
    $jenis = old('jenis_promosi', $promosi->jenis_promosi ?? 'diskon_persen');
    $mulai = old('tanggal_mulai', optional($promosi->tanggal_mulai)->toDateString());
    $selesai = old('tanggal_selesai', optional($promosi->tanggal_selesai)->toDateString());
    $persen = old('nilai_persen', $promosi->nilai_persen !== null ? (float) $promosi->nilai_persen : null);
    $aktif = old('status_aktif', $promosi->status_aktif ?? true);
    $tanggalPilihan = $tanggalPilihan ?? null;
    $autoIsi = $autoIsi ?? false;
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <div class="fw-bold mb-1">Periksa kembali isian berikut:</div>
        <ul class="mb-0">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    {{-- ================= KIRI: langkah-langkah ================= --}}
    <div class="col-lg-8">

        {{-- 1. TANGGAL --}}
        <div class="card langkah">
            <div class="card-body">
                <div class="langkah-judul"><span class="langkah-no">1</span><h3>Pilih tanggal promo</h3></div>
                <p class="langkah-ket">Pilih tanggal kembar supaya nama dan periode terisi otomatis, atau pilih "Tanggal bebas".</p>

                <div class="chip-wrap" id="chip-tanggal">
                    @foreach ($tanggalKembar as $t)
                        <button type="button" class="tw-chip" data-tanggal="{{ $t['tanggal'] }}" data-kode="{{ $t['kode'] }}">
                            {{ $t['kode'] }}
                            <small>{{ \Carbon\Carbon::parse($t['tanggal'])->locale('id')->translatedFormat('j M Y') }}</small>
                        </button>
                    @endforeach
                    <button type="button" class="tw-chip bebas" id="chip-bebas">
                        Tanggal bebas
                        <small>atur sendiri</small>
                    </button>
                </div>
            </div>
        </div>

        {{-- 2. DETAIL --}}
        <div class="card langkah">
            <div class="card-body">
                <div class="langkah-judul"><span class="langkah-no">2</span><h3>Nama promo</h3></div>
                <p class="langkah-ket">Nama ini yang akan dilihat pelanggan.</p>

                <div class="mb-3">
                    <label class="form-label" for="nama_promosi">Nama promosi <span class="text-danger">*</span></label>
                    <input type="text" id="nama_promosi" name="nama_promosi" maxlength="150" class="form-control"
                        placeholder="Contoh: Promo Tanggal Kembar 12.12"
                        value="{{ old('nama_promosi', $promosi->nama_promosi) }}" required>
                </div>

                <div>
                    <label class="form-label" for="deskripsi">Deskripsi singkat (opsional)</label>
                    <textarea id="deskripsi" name="deskripsi" rows="2" maxlength="1000" class="form-control"
                        placeholder="Contoh: Diskon spesial untuk semua produk jamur krispi">{{ old('deskripsi', $promosi->deskripsi) }}</textarea>
                </div>
            </div>
        </div>

        {{-- 3. POTONGAN --}}
        <div class="card langkah">
            <div class="card-body">
                <div class="langkah-judul"><span class="langkah-no">3</span><h3>Bentuk potongan</h3></div>
                <p class="langkah-ket">Pilih satu cara memberi promo.</p>

                <div class="jenis-grid mb-3">
                    <label class="jenis-opsi">
                        <input type="radio" name="jenis_promosi" value="diskon_persen" @checked($jenis === 'diskon_persen')>
                        <div class="jenis-kotak"><strong>Diskon persen</strong><span>Harga dipotong sekian %. Contoh: diskon 11%.</span></div>
                    </label>
                    <label class="jenis-opsi">
                        <input type="radio" name="jenis_promosi" value="diskon_nominal" @checked($jenis === 'diskon_nominal')>
                        <div class="jenis-kotak"><strong>Potongan rupiah</strong><span>Harga dipotong sekian rupiah. Contoh: potong Rp5.000.</span></div>
                    </label>
                    <label class="jenis-opsi">
                        <input type="radio" name="jenis_promosi" value="harga_khusus" @checked($jenis === 'harga_khusus')>
                        <div class="jenis-kotak"><strong>Harga khusus</strong><span>Harga jadi ditetapkan. Contoh: jadi Rp10.000.</span></div>
                    </label>
                </div>

                <div class="row g-3">
                    <div class="col-md-6" id="box-persen">
                        <label class="form-label" for="nilai_persen">Besar diskon <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0.01" max="100" id="nilai_persen" name="nilai_persen"
                                class="form-control" value="{{ $persen }}" placeholder="11">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    <div class="col-md-6" id="box-nominal">
                        <label class="form-label" for="nilai_nominal" id="label-nominal">Nilai <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" min="1" max="16777215" id="nilai_nominal" name="nilai_nominal"
                                class="form-control" value="{{ old('nilai_nominal', $promosi->nilai_nominal) }}" placeholder="5000">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="minimal_pembelian">Minimal pembelian (opsional)</label>
                        <div class="input-group">
                            <input type="number" min="1" max="65535" id="minimal_pembelian" name="minimal_pembelian"
                                class="form-control" value="{{ old('minimal_pembelian', $promosi->minimal_pembelian) }}"
                                placeholder="Kosong = tanpa minimal">
                            <span class="input-group-text">item</span>
                        </div>
                    </div>
                </div>

                <div class="contoh-hasil" id="contoh-hasil" hidden></div>
            </div>
        </div>

        {{-- 4. PERIODE --}}
        <div class="card langkah">
            <div class="card-body">
                <div class="langkah-judul"><span class="langkah-no">4</span><h3>Lama promo berlaku</h3></div>
                <p class="langkah-ket">Untuk tanggal kembar biasanya 1 hari. Ubah tanggal selesai kalau mau lebih lama.</p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="tanggal_mulai">Mulai <span class="text-danger">*</span></label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control" value="{{ $mulai }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="tanggal_selesai">Selesai <span class="text-danger">*</span></label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control" value="{{ $selesai }}" required>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. VARIAN --}}
        <div class="card langkah">
            <div class="card-body">
                <div class="langkah-judul"><span class="langkah-no">5</span><h3>Produk yang kena promo</h3></div>
                <p class="langkah-ket">Centang varian yang ikut promo. Minimal satu.</p>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <input type="search" id="cari-varian" class="form-control" style="max-width: 280px;"
                        placeholder="Cari produk atau varian…">
                    <button type="button" class="btn btn-outline-secondary" id="varian-semua">Pilih semua</button>
                    <button type="button" class="btn btn-outline-secondary" id="varian-kosong">Kosongkan</button>
                </div>

                <div class="varian-box" id="varian-box">
                    @forelse ($daftarVarian as $namaProduk => $list)
                        <div class="varian-grup" data-nama="{{ strtolower($namaProduk) }}">
                            <div class="varian-grup-judul">
                                <strong>{{ $namaProduk }}</strong>
                                <button type="button" class="varian-pilih-semua">Pilih semua</button>
                            </div>
                            @foreach ($list as $v)
                                <label class="form-check form-check-inline varian-item" data-nama="{{ strtolower($v->nama_varian) }}">
                                    <input type="checkbox" class="form-check-input varian-cek" name="varian[]"
                                        value="{{ $v->id_varian }}" @checked(in_array($v->id_varian, $terpilih))>
                                    <span class="form-check-label">{{ $v->nama_varian }}</span>
                                </label>
                            @endforeach
                        </div>
                    @empty
                        <div class="p-3 text-secondary">Belum ada varian aktif. Tambahkan varian produk dulu.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ================= KANAN: ringkasan ================= --}}
    <div class="col-lg-4">
        <div class="card ringkas-sticky">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="judul-ikon ikon-merah"><i class="ti ti-receipt"></i></span>
                    Ringkasan
                </h3>
            </div>
            <div class="card-body">
                <div class="ringkas-baris"><span>Nama</span><span id="r-nama">-</span></div>
                <div class="ringkas-baris"><span>Potongan</span><span id="r-potongan">-</span></div>
                <div class="ringkas-baris"><span>Minimal beli</span><span id="r-minimal">Tanpa minimal</span></div>
                <div class="ringkas-baris"><span>Berlaku</span><span id="r-periode">-</span></div>
                <div class="ringkas-baris"><span>Varian</span><span id="r-varian">0 dipilih</span></div>

                <label class="form-check form-switch mt-3 mb-0">
                    <input type="checkbox" name="status_aktif" value="1" class="form-check-input" @checked($aktif)>
                    <span class="form-check-label fw-semibold">Promosi aktif</span>
                </label>
                <div class="text-secondary small mt-1">Matikan kalau promo belum mau ditayangkan.</div>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="{{ route('admin.promosi.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="ti ti-device-floppy me-1"></i>Simpan Promosi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var $ = function (id) { return document.getElementById(id); };
    var bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    var rupiah = function (n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); };
    var tgl = function (s) {
        if (!s) return '';
        var p = s.split('-');
        return parseInt(p[2], 10) + ' ' + bulan[parseInt(p[1], 10) - 1] + ' ' + p[0];
    };

    var nama = $('nama_promosi');
    var namaOtomatis = false;

    /* ---------- 1. chip tanggal kembar ---------- */
    var chips = document.querySelectorAll('#chip-tanggal .tw-chip[data-tanggal]');
    function tandaiChip(aktif) {
        document.querySelectorAll('#chip-tanggal .tw-chip').forEach(function (c) { c.classList.remove('aktif'); });
        if (aktif) aktif.classList.add('aktif');
    }
    function pilihChip(chip, isiNama) {
        tandaiChip(chip);
        $('tanggal_mulai').value = chip.dataset.tanggal;
        $('tanggal_selesai').value = chip.dataset.tanggal;
        if (isiNama && (!nama.value.trim() || namaOtomatis)) {
            nama.value = 'Promo Tanggal Kembar ' + chip.dataset.kode;
            namaOtomatis = true;
        }
        ringkas();
    }
    chips.forEach(function (c) { c.addEventListener('click', function () { pilihChip(c, true); }); });
    $('chip-bebas').addEventListener('click', function () { tandaiChip($('chip-bebas')); });
    nama.addEventListener('input', function () { namaOtomatis = false; });

    /* ---------- 3. jenis potongan ---------- */
    function jenisAktif() { return document.querySelector('input[name="jenis_promosi"]:checked').value; }
    function aturJenis() {
        var j = jenisAktif();
        var persen = j === 'diskon_persen';
        $('box-persen').hidden = !persen;
        $('box-nominal').hidden = persen;
        $('nilai_persen').required = persen;
        $('nilai_nominal').required = !persen;
        $('label-nominal').innerHTML = (j === 'harga_khusus' ? 'Harga jadi' : 'Potongan') + ' <span class="text-danger">*</span>';
        ringkas();
    }
    document.querySelectorAll('input[name="jenis_promosi"]').forEach(function (r) { r.addEventListener('change', aturJenis); });
    ['nilai_persen', 'nilai_nominal', 'minimal_pembelian', 'tanggal_mulai', 'tanggal_selesai', 'nama_promosi']
        .forEach(function (id) { $(id).addEventListener('input', ringkas); });

    /* ---------- 5. varian ---------- */
    var cek = document.querySelectorAll('.varian-cek');
    cek.forEach(function (c) { c.addEventListener('change', ringkas); });
    $('varian-semua').addEventListener('click', function () {
        document.querySelectorAll('.varian-item:not([hidden]) .varian-cek').forEach(function (c) { c.checked = true; });
        ringkas();
    });
    $('varian-kosong').addEventListener('click', function () { cek.forEach(function (c) { c.checked = false; }); ringkas(); });
    document.querySelectorAll('.varian-pilih-semua').forEach(function (b) {
        b.addEventListener('click', function () {
            b.closest('.varian-grup').querySelectorAll('.varian-cek').forEach(function (c) { c.checked = true; });
            ringkas();
        });
    });
    $('cari-varian').addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        document.querySelectorAll('.varian-grup').forEach(function (g) {
            var adaCocok = false;
            g.querySelectorAll('.varian-item').forEach(function (v) {
                var cocok = !q || g.dataset.nama.indexOf(q) > -1 || v.dataset.nama.indexOf(q) > -1;
                v.hidden = !cocok;
                if (cocok) adaCocok = true;
            });
            g.hidden = !adaCocok;
        });
    });

    /* ---------- ringkasan & contoh ---------- */
    function ringkas() {
        var j = jenisAktif();
        var p = parseFloat($('nilai_persen').value);
        var n = parseInt($('nilai_nominal').value, 10);
        var potongan = '-';
        var contoh = '';

        if (j === 'diskon_persen' && p > 0) {
            potongan = 'Diskon ' + p + '%';
            contoh = 'Contoh: produk Rp100.000 menjadi ' + rupiah(Math.round(100000 * (1 - p / 100))) + '.';
        } else if (j === 'diskon_nominal' && n > 0) {
            potongan = 'Potong ' + rupiah(n);
            contoh = 'Contoh: produk Rp100.000 menjadi ' + rupiah(Math.max(100000 - n, 0)) + '.';
        } else if (j === 'harga_khusus' && n > 0) {
            potongan = 'Harga ' + rupiah(n);
            contoh = 'Semua varian yang dipilih dijual seharga ' + rupiah(n) + '.';
        }
        var kotak = $('contoh-hasil');
        kotak.hidden = !contoh;
        kotak.textContent = contoh;

        var m = $('tanggal_mulai').value, s = $('tanggal_selesai').value;
        var periode = '-';
        if (m && s) {
            var hari = Math.round((new Date(s) - new Date(m)) / 86400000) + 1;
            periode = (m === s ? tgl(m) : tgl(m) + ' – ' + tgl(s)) + (hari > 0 ? ' (' + hari + ' hari)' : '');
        }
        var min = parseInt($('minimal_pembelian').value, 10);
        var jumlah = document.querySelectorAll('.varian-cek:checked').length;

        $('r-nama').textContent = nama.value.trim() || '-';
        $('r-potongan').textContent = potongan;
        $('r-minimal').textContent = min > 0 ? 'Min. ' + min + ' item' : 'Tanpa minimal';
        $('r-periode').textContent = periode;
        $('r-varian').textContent = jumlah + ' dipilih';
    }

    /* ---------- keadaan awal ---------- */
    aturJenis();
    var awal = @json($tanggalPilihan);
    var autoIsi = @json($autoIsi);
    if (awal) {
        var cocok = Array.prototype.find.call(chips, function (c) { return c.dataset.tanggal === awal; });
        if (cocok) {
            if (autoIsi) { pilihChip(cocok, true); } else { tandaiChip(cocok); }
        } else {
            tandaiChip($('chip-bebas'));
            if (autoIsi) { $('tanggal_mulai').value = awal; $('tanggal_selesai').value = awal; }
        }
    } else {
        tandaiChip($('chip-bebas'));
    }
    ringkas();
});
</script>