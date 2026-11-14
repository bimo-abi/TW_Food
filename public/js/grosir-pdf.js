/* public/js/grosir-pdf.js
   Membuat file PDF "Harga Grosir" langsung di browser, lalu mengunduhnya.
   Tanpa library luar. Isinya diambil dari tabel yang tampil di halaman,
   jadi otomatis sama dengan data dari database.
   Dipakai tombol: <button id="grosir-unduh" data-kontak="...">  */
(function () {
    'use strict';

    // Lebar huruf Helvetica (per 1000 unit) untuk karakter ASCII 32..126
    var W = [278,278,355,556,556,889,667,191,333,333,389,584,278,333,278,278,
             556,556,556,556,556,556,556,556,556,556,278,278,584,584,584,556,
             1015,667,667,722,722,667,611,778,722,278,500,667,556,833,722,778,
             667,778,722,667,611,722,667,944,667,667,611,278,278,278,469,556,
             333,556,556,500,556,556,278,556,556,222,222,500,222,833,556,556,
             556,556,333,500,278,556,500,722,500,500,500,334,260,334,584];
    var WB = [278,333,474,556,556,889,722,238,333,333,389,584,278,333,278,278,
              556,556,556,556,556,556,556,556,556,556,333,333,584,584,584,611,
              975,722,722,722,722,667,611,778,722,278,556,722,611,833,722,778,
              667,778,722,667,611,722,667,944,667,667,611,333,278,333,584,556,
              333,556,611,556,611,556,333,611,611,278,278,556,278,889,611,611,
              611,611,389,556,333,611,556,778,556,556,500,389,280,389,584];

    // Ubah teks ke kode WinAnsi (1 huruf = 1 byte). Karakter di luar itu diganti.
    function winAnsi(s) {
        var out = '';
        s = String(s).replace(/\s+/g, ' ');
        for (var i = 0; i < s.length; i++) {
            var c = s.charCodeAt(i);
            if (c >= 32 && c < 127) out += s[i];
            else if (c === 0xB7) out += '\xB7';          // ·
            else if (c === 0x2013 || c === 0x2014) out += '\x96'; // – —
            else if (c === 0x2019 || c === 0x2018) out += "'";
            else if (c === 0x201C || c === 0x201D) out += '"';
            else if (c >= 0xC0 && c <= 0xFF) out += s[i];   // huruf beraksen
            else out += '?';
        }
        return out;
    }
    function esc(s) { return s.replace(/([\\()])/g, '\\$1'); }

    function lebar(teks, ukuran, tebal) {
        var tbl = tebal ? WB : W, w = 0;
        for (var i = 0; i < teks.length; i++) {
            var c = teks.charCodeAt(i);
            w += (c >= 32 && c < 127) ? tbl[c - 32] : (c === 0xB7 ? 278 : (c === 0x96 ? 556 : 556));
        }
        return w * ukuran / 1000;
    }

    // Pecah teks jadi beberapa baris sesuai lebar kolom
    function bungkus(teks, maxW, ukuran, tebal) {
        var kata = winAnsi(teks).split(' '), baris = [], cur = '';
        kata.forEach(function (k) {
            var coba = cur ? cur + ' ' + k : k;
            if (lebar(coba, ukuran, tebal) <= maxW || !cur) cur = coba;
            else { baris.push(cur); cur = k; }
        });
        if (cur) baris.push(cur);
        return baris.length ? baris : [''];
    }

    function warna(hex) {
        var n = parseInt(hex.slice(1), 16);
        return [(n >> 16) / 255, ((n >> 8) & 255) / 255, (n & 255) / 255].map(function (x) { return x.toFixed(3); }).join(' ');
    }

    function buatPdf(data) {
        var PW = 595.28, PH = 841.89, M = 40, isi = PW - 2 * M;
        var halaman = [], ops, y;

        function halamanBaru() { ops = []; halaman.push(ops); y = PH - M; }
        function teks(x, yy, str, ukuran, tebal, hex) {
            ops.push('BT /' + (tebal ? 'F2' : 'F1') + ' ' + ukuran + ' Tf ' + warna(hex || '#222222') + ' rg ' +
                x.toFixed(2) + ' ' + yy.toFixed(2) + ' Td (' + esc(str) + ') Tj ET');
        }
        function kotak(x, yy, w, h, hex) { ops.push(warna(hex) + ' rg ' + x.toFixed(2) + ' ' + yy.toFixed(2) + ' ' + w.toFixed(2) + ' ' + h.toFixed(2) + ' re f'); }
        function garis(x1, y1, x2, y2, hex, tebal) {
            ops.push(warna(hex) + ' RG ' + (tebal || 0.6) + ' w ' + x1.toFixed(2) + ' ' + y1.toFixed(2) + ' m ' + x2.toFixed(2) + ' ' + y2.toFixed(2) + ' l S');
        }

        halamanBaru();

        // Judul
        winAnsi(data.judul);
        teks(M, y - 20, winAnsi(data.judul), 20, true, '#1A1209');
        y -= 36;
        bungkus(data.subjudul, isi, 10, false).forEach(function (b) { teks(M, y - 10, b, 10, false, '#555555'); y -= 14; });
        y -= 10;

        // Tabel
        var kol = [0.30, 0.27, 0.27, 0.16].map(function (p) { return p * isi; });
        var pad = 7, UK = 9.5, LH = 12.5;
        function xKol(i) { var x = M; for (var k = 0; k < i; k++) x += kol[k]; return x; }

        function barisHeader() {
            var h = 26;
            kotak(M, y - h, isi, h, '#FFE7CC');
            data.header.forEach(function (t, i) { teks(xKol(i) + pad, y - 17, winAnsi(t), 10, true, '#B54E00'); });
            garis(M, y - h, M + isi, y - h, '#FF7A00', 1.2);
            y -= h;
        }
        barisHeader();

        data.baris.forEach(function (row) {
            var sel = row.map(function (t, i) { return bungkus(t, kol[i] - 2 * pad, UK, i === 0); });
            var maks = Math.max.apply(null, sel.map(function (s) { return s.length; }));
            var h = maks * LH + 2 * pad - 2;
            if (y - h < M + 46) { halamanBaru(); barisHeader(); }
            sel.forEach(function (s, i) {
                s.forEach(function (b, j) { teks(xKol(i) + pad, y - pad - 8 - j * LH, b, UK, i === 0, '#222222'); });
            });
            y -= h;
            garis(M, y, M + isi, y, '#D9D2C8', 0.6);
        });

        // Catatan
        y -= 14;
        bungkus(data.catatan, isi, 9, false).forEach(function (b) {
            if (y < M + 40) { halamanBaru(); }
            teks(M, y - 9, b, 9, false, '#555555'); y -= 12.5;
        });

        // Footer + nomor halaman
        halaman.forEach(function (o, n) {
            ops = o;
            garis(M, M + 22, M + isi, M + 22, '#D9D2C8', 0.6);
            teks(M, M + 8, winAnsi(data.footer), 8.5, false, '#777777');
            var hal = 'Hal. ' + (n + 1) + '/' + halaman.length;
            teks(M + isi - lebar(hal, 8.5, false), M + 8, hal, 8.5, false, '#777777');
        });

        // ---- Rakit file PDF ----
        var objs = [], nPage = halaman.length;
        // 1 Catalog, 2 Pages, 3 F1, 4 F2, lalu pasangan (Page, Content) per halaman
        var kids = [];
        for (var i = 0; i < nPage; i++) kids.push((5 + i * 2) + ' 0 R');
        objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        objs[2] = '<< /Type /Pages /Kids [' + kids.join(' ') + '] /Count ' + nPage + ' >>';
        objs[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        objs[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        halaman.forEach(function (o, i) {
            var isiStream = o.join('\n');
            objs[5 + i * 2] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' + PW + ' ' + PH + '] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' + (6 + i * 2) + ' 0 R >>';
            objs[6 + i * 2] = '<< /Length ' + isiStream.length + ' >>\nstream\n' + isiStream + '\nendstream';
        });
        var pdf = '%PDF-1.4\n', off = [];
        for (var k = 1; k < objs.length; k++) { off[k] = pdf.length; pdf += k + ' 0 obj\n' + objs[k] + '\nendobj\n'; }
        var xref = pdf.length;
        pdf += 'xref\n0 ' + objs.length + '\n0000000000 65535 f \n';
        for (var m = 1; m < objs.length; m++) pdf += ('0000000000' + off[m]).slice(-10) + ' 00000 n \n';
        pdf += 'trailer\n<< /Size ' + objs.length + ' /Root 1 0 R >>\nstartxref\n' + xref + '\n%%EOF';

        var bytes = new Uint8Array(pdf.length);
        for (var b = 0; b < pdf.length; b++) bytes[b] = pdf.charCodeAt(b) & 255;
        return new Blob([bytes], { type: 'application/pdf' });
    }

    function teksDari(el) { return el ? el.textContent.replace(/\s+/g, ' ').trim() : ''; }

    function ambilData(tombol) {
        var tabel = document.querySelector('.grosir-tabel');
        var header = Array.prototype.map.call(tabel.querySelectorAll('thead th'), teksDari);
        var baris = Array.prototype.map.call(tabel.querySelectorAll('tbody tr'), function (tr) {
            return Array.prototype.map.call(tr.querySelectorAll('td'), teksDari);
        }).filter(function (r) { return r.length === header.length; });
        return {
            judul: 'Harga Grosir TW FOOD',
            subjudul: teksDari(document.querySelector('.grosir-head p')),
            header: header,
            baris: baris,
            catatan: teksDari(document.querySelector('.grosir-catatan')),
            footer: 'TW FOOD - Kuliner & Grosir Segar' + (tombol.dataset.kontak ? '  |  ' + tombol.dataset.kontak : '')
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        var tombol = document.getElementById('grosir-unduh');
        if (!tombol) return;
        tombol.addEventListener('click', function () {
            var blob = buatPdf(ambilData(tombol));
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'harga-grosir-twfood.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(function () { URL.revokeObjectURL(url); }, 2000);
        });
    });
})();