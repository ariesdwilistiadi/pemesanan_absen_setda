<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Daftar Hadir - {{ $rapat->nama_kegiatan }}</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            color: #000;
        }

        /* Set page size to A4 */
        @page {
            size: A4;
            margin: 20mm;
        }

        .container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
        }

        /* Kop Surat */
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop-surat h1 {
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
        }
        .kop-surat h2 {
            margin: 0;
            font-size: 14pt;
        }
        .kop-surat p {
            margin: 0;
            font-size: 10pt;
        }

        /* Judul Dokumen */
        .judul-dokumen {
            text-align: center;
            margin-bottom: 20px;
        }
        .judul-dokumen h3 {
            margin: 0;
            font-size: 14pt;
            text-decoration: underline;
            text-transform: uppercase;
        }

        /* Informasi Rapat */
        .info-rapat {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-rapat td {
            vertical-align: top;
            padding: 2px 0;
        }
        .info-rapat .label {
            width: 150px;
        }
        .info-rapat .colon {
            width: 20px;
            text-align: center;
        }

        /* Tabel Daftar Hadir */
        table.tabel-hadir {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.tabel-hadir th, table.tabel-hadir td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: middle;
        }
        table.tabel-hadir th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }

        /* Tanda Tangan Image */
        .ttd-img {
            max-width: 100px;
            max-height: 50px;
            display: block;
            margin: 0 auto;
        }

        /* Tanda Tangan Pengesahan (Kepala) */
        .pengesahan {
            width: 100%;
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .pengesahan td {
            width: 50%;
            vertical-align: top;
        }
        .ttd-box {
            text-align: center;
            float: right;
            width: 250px;
        }
        .ttd-box p { margin: 0; }
        .ttd-box .nama-kepala {
            margin-top: 80px;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <!-- Kop Surat -->
        <div class="kop-surat">
            @if($instansi->logo)
            <img src="{{ asset('storage/' . $instansi->logo) }}" style="float:left; width:80px; margin-right: 15px;">
            @endif
            <h1>{{ $instansi->pemerintah }}</h1>
            <h2>{{ $instansi->nama_instansi }}</h2>
            <p>{{ $instansi->alamat }}</p>
            <p>{{ $instansi->kontak }}</p>
            <div style="clear:both;"></div>
        </div>

        <!-- Judul -->
        <div class="judul-dokumen">
            <h3>Daftar Hadir</h3>
        </div>

        <!-- Info Rapat -->
        <table class="info-rapat">
            <tr>
                <td class="label">Nama Kegiatan</td>
                <td class="colon">:</td>
                <td>{{ $rapat->nama_kegiatan }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td class="colon">:</td>
                <td>{{ \Carbon\Carbon::parse($rapat->tanggal)->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td class="label">Waktu</td>
                <td class="colon">:</td>
                <td>{{ $rapat->pukul }} WIB s/d Selesai</td>
            </tr>
            <tr>
                <td class="label">Ruangan</td>
                <td class="colon">:</td>
                <td>{{ $rapat->ruangan ? $rapat->ruangan->nama_ruangan : '-' }}</td>
            </tr>
        </table>

        <!-- Tabel Daftar Hadir -->
        <table class="tabel-hadir">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="30%">Nama / NIP</th>
                    <th width="30%">Instansi / Dinas</th>
                    <th width="20%">No. HP / Email</th>
                    <th width="15%">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kehadiran as $index => $hadir)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $hadir->nama }}</strong><br>
                        <span style="font-size:10pt;">{{ $hadir->nip ? 'NIP. '.$hadir->nip : '' }}</span>
                    </td>
                    <td>
                        {{ $hadir->tipe_peserta === 'internal' ? ($hadir->dinas ? $hadir->dinas->nama_dinas : '-') : $hadir->nama_external }}
                    </td>
                    <td>
                        <span style="font-size:10pt;">{{ $hadir->telp }}<br>{{ $hadir->email }}</span>
                    </td>
                    <td class="text-center">
                        @if($hadir->signature)
                            <img src="{{ $hadir->signature }}" class="ttd-img" alt="TTD">
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada peserta yang mengisi daftar hadir.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Tanda Tangan Kepala -->
        <table class="pengesahan">
            <tr>
                <td></td>
                <td>
                    <div class="ttd-box">
                        <p>Ditetapkan di: ............................</p>
                        <p>Pada Tanggal: {{ \Carbon\Carbon::parse($rapat->tanggal)->translatedFormat('d F Y') }}</p>
                        <br>
                        <p>Mengetahui,</p>
                        <p><strong>{{ $instansi->jabatan_kepala }}</strong></p>
                        <div class="nama-kepala">{{ $instansi->nama_kepala }}</div>
                        <p>{{ $instansi->nip_kepala ? 'NIP. ' . $instansi->nip_kepala : '' }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>