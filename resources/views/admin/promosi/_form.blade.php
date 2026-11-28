@push('styles')
<link rel="stylesheet" href="{{ asset('css/Admin/promosi.css') }}">
@endpush
@php
    $terpilih = collect(old('varian', $varianTerpilih))->map(fn($v) => (int) $v)->all();
    $diskonLama = old('diskon', $diskonVarian ?? []);
    $mulai = old('tanggal_mulai', optional($promosi->tanggal_mulai)->toDateString());
    $selesai = old('tanggal_selesai', optional($promosi->tanggal_selesai)->toDateString());
    $aktif = old('status_aktif', $promosi->status_aktif ?? true);
@endphp
@if ($errors->any())
<div class="alert alert-danger"><strong>Periksa kembali:</strong><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
@if ($legacyReadOnly ?? false)
<div class="alert alert-warning">Promosi ini menggunakan skema lama (potongan nominal/harga khusus). Data lama dipertahankan dan tidak dikonversi otomatis. Buat Campaign baru dengan skema persentase per varian.</div>
<div class="card"><div class="card-body"><strong>{{ $promosi->nama_promosi }}</strong><p class="mb-0">Periode: {{ $promosi->tanggal_mulai->format('d-m-Y') }} – {{ $promosi->tanggal_selesai->format('d-m-Y') }}. Edit Campaign lama ini dinonaktifkan untuk mencegah perubahan makna data.</p></div><div class="card-footer"><a class="btn btn-secondary" href="{{ route('admin.promosi.index') }}">Kembali</a></div></div>
@else
<div class="row g-4">
 <div class="col-lg-8">
  <div class="card mb-3"><div class="card-body">
   <h3>1. Informasi Campaign</h3>
   <div class="mb-3"><label class="form-label">Nama Campaign *</label><input class="form-control" name="nama_promosi" maxlength="150" required value="{{ old('nama_promosi', $promosi->nama_promosi) }}" placeholder="Contoh: Promo Akhir Tahun"></div>
   <label class="form-label">Deskripsi</label><textarea class="form-control" name="deskripsi" rows="2" maxlength="1000">{{ old('deskripsi', $promosi->deskripsi) }}</textarea>
  </div></div>
  <div class="card mb-3"><div class="card-body">
   <h3>2. Periode Campaign</h3><div class="row"><div class="col-md-6"><label class="form-label">Tanggal mulai *</label><input type="date" class="form-control" name="tanggal_mulai" id="tanggal_mulai" required value="{{ $mulai }}"></div><div class="col-md-6"><label class="form-label">Tanggal selesai *</label><input type="date" class="form-control" name="tanggal_selesai" id="tanggal_selesai" required min="{{ $mulai }}" value="{{ $selesai }}"></div></div>
  </div></div>
  <div class="card mb-3"><div class="card-body">
   <h3>3. Pilih Produk dan Varian</h3><p class="text-secondary">Diskon ditetapkan terpisah untuk setiap varian. Hanya harga ecer yang mendapatkan diskon; harga grosir tidak berubah.</p>
   <input id="cari-varian" class="form-control mb-3" type="search" placeholder="Cari nama produk atau varian…">
   <div class="d-flex gap-2 mb-3"><button type="button" class="btn btn-sm btn-outline-secondary" id="pilih-semua">Pilih semua hasil</button><button type="button" class="btn btn-sm btn-outline-secondary" id="hapus-pilihan">Kosongkan pilihan</button></div>
   @forelse($daftarVarian as $namaProduk => $list)
    <div class="border rounded p-3 mb-3 grup-varian" data-search="{{ strtolower($namaProduk) }}"><strong>{{ $namaProduk }}</strong>
     @foreach($list as $v)
      <label class="d-flex flex-wrap align-items-center gap-2 py-2 border-bottom varian-row" data-search="{{ strtolower($v->nama_produk.' '.$v->nama_varian) }}">
       <input type="checkbox" class="form-check-input pilih-varian" name="varian[]" value="{{ $v->id_varian }}" data-id="{{ $v->id_varian }}" @checked(in_array((int)$v->id_varian, $terpilih))>
       <span class="flex-grow-1">{{ $v->nama_varian }}</span>
       <span class="text-secondary small">Ecer: {{ $v->harga_ecer !== null ? 'Rp '.number_format($v->harga_ecer,0,',','.') : 'Belum ada harga aktif' }}</span>
       <span class="input-group input-group-sm" style="width:125px"><input type="number" class="form-control input-diskon" name="diskon[{{ $v->id_varian }}]" min="0.01" max="100" step="0.01" placeholder="Diskon" value="{{ $diskonLama[$v->id_varian] ?? '' }}" @disabled(!in_array((int)$v->id_varian, $terpilih)) data-price="{{ $v->harga_ecer ?? 0 }}" data-id="{{ $v->id_varian }}"><span class="input-group-text">%</span></span>
      </label>
     @endforeach
    </div>
   @empty <p class="text-secondary">Belum ada varian aktif dengan produk aktif.</p> @endforelse
  </div></div>
  <div class="card"><div class="card-body"><h3>4. Preview Harga</h3><p class="text-secondary">Nominal diskon per unit dibulatkan ke Rupiah terdekat (setengah dibulatkan naik).</p><div class="table-responsive"><table class="table"><thead><tr><th>Produk/Varian</th><th>Harga ecer</th><th>Diskon</th><th>Harga akhir</th></tr></thead><tbody id="preview-campaign"><tr><td colspan="4" class="text-secondary">Pilih varian dan isi diskon untuk melihat preview.</td></tr></tbody></table></div></div></div>
 </div>
 <div class="col-lg-4"><div class="card" style="position:sticky;top:1rem"><div class="card-header"><h3 class="card-title">Ringkasan Campaign</h3></div><div class="card-body"><p><strong>Varian dipilih:</strong> <span id="jumlah-varian">0</span></p><p><strong>Periode:</strong><br><span id="ringkas-periode">{{ $mulai ?: '-' }} – {{ $selesai ?: '-' }}</span></p><label class="form-check form-switch"><input type="checkbox" class="form-check-input" name="status_aktif" value="1" @checked($aktif)><span class="form-check-label">Campaign aktif</span></label><small class="text-secondary">Campaign aktif akan diperiksa agar tidak bertabrakan dengan Campaign aktif lain pada varian yang sama.</small></div><div class="card-footer d-flex gap-2"><a href="{{ route('admin.promosi.index') }}" class="btn btn-outline-secondary">Batal</a><button type="submit" class="btn btn-primary flex-fill">Simpan Campaign</button></div></div></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
 const rupiah = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
 const rows = [...document.querySelectorAll('.varian-row')];
 const checks = [...document.querySelectorAll('.pilih-varian')];
 const inputs = [...document.querySelectorAll('.input-diskon')];
 const preview = document.getElementById('preview-campaign');
 function render() {
  const selected = checks.filter(c => c.checked);
  document.getElementById('jumlah-varian').textContent = selected.length;
  preview.replaceChildren();
  selected.forEach(c => {
   const row = c.closest('.varian-row'); const input = row.querySelector('.input-diskon');
   const price = Number(input.dataset.price || 0); const pct = Number(input.value || 0);
   const label = row.querySelector('.flex-grow-1').textContent.trim();
   const product = row.closest('.grup-varian').querySelector('strong').textContent;
   const discount = Math.floor((price * pct / 100) + 0.5);
   const tr = document.createElement('tr');
   [product + ' – ' + label, rupiah(price), pct ? pct + '%' : '-', price ? rupiah(price-discount) : 'Harga belum tersedia'].forEach(value => {
    const td = document.createElement('td'); td.textContent = value; tr.appendChild(td);
   });
   preview.appendChild(tr);
  });
  if (!selected.length) {
   const tr = document.createElement('tr'); const td = document.createElement('td');
   td.colSpan = 4; td.className = 'text-secondary'; td.textContent = 'Pilih varian dan isi diskon untuk melihat preview.';
   tr.appendChild(td); preview.appendChild(tr);
  }
 }
 checks.forEach(c => c.addEventListener('change', () => { c.closest('.varian-row').querySelector('.input-diskon').disabled = !c.checked; render(); })); inputs.forEach(i => i.addEventListener('input', render));
 document.getElementById('cari-varian').addEventListener('input', e => { const q=e.target.value.toLowerCase(); rows.forEach(r => r.hidden=!r.dataset.search.includes(q)); document.querySelectorAll('.grup-varian').forEach(g=>g.hidden=!g.querySelector('.varian-row:not([hidden])')); });
 document.getElementById('pilih-semua').addEventListener('click',()=>{checks.forEach(c=>{if(!c.closest('.varian-row').hidden){c.checked=true;c.closest('.varian-row').querySelector('.input-diskon').disabled=false;}});render();});
 document.getElementById('hapus-pilihan').addEventListener('click',()=>{checks.forEach(c=>{c.checked=false;c.closest('.varian-row').querySelector('.input-diskon').disabled=true;});render();});
 ['tanggal_mulai','tanggal_selesai'].forEach(id=>document.getElementById(id).addEventListener('change',()=>{document.getElementById('ringkas-periode').textContent=document.getElementById('tanggal_mulai').value+' – '+document.getElementById('tanggal_selesai').value;}));
 render();
});
</script>
@endif
