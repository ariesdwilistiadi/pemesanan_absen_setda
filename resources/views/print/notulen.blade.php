<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notulen Rapat</title>
    <style>
        @page {
            size: A4;
            margin: 30px 50px 30px 50px;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }

        .container {
            width: 100%;
        }

        /* Header / Kop Surat */
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
            margin-bottom: 10px;
        }

        .kop-surat table {
            width: 100%;
            border-collapse: collapse;
        }

        .kop-surat td {
            vertical-align: middle;
        }

        .logo-box {
            width: 80px;
            text-align: center;
        }

        .logo-box img {
            width: 70px;
            height: auto;
        }

        .kop-text {
            text-align: center;
        }

        .kop-text h1 {
            margin: 0;
            font-size: 14pt;
            text-transform: uppercase;
            font-weight: bold;
        }

        .kop-text h2 {
            margin: 4px 0;
            font-size: 13pt;
            text-transform: uppercase;
            font-weight: bold;
        }

        .kop-text p {
            margin: 3px 0;
            font-size: 10pt;
        }

        /* Judul */
        .judul {
            text-align: center;
            margin: 40px 0 35px 0;
        }

        .judul h5 {
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
            text-decoration: underline;
            font-weight: bold;
        }

        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .info-table td {
            padding: 3px 0;
            font-size: 12pt;
        }

        .label-col {
            width: 160px;
        }

        /* Section */
        .section-title {
            font-weight: bold;
            margin: 5px 0 5px 0;
            font-size: 12pt;
        }

        /* Content */
        .content {
            margin-left: 10px;
            margin-right: 5px;
            font-size: 12pt;
            text-align: justify;
            line-height: 1.5;
        }

        .content p {
            margin-bottom: 6px;
        }

        .content ul, .content ol {
            margin-left: 25px;
            margin-bottom: 8px;
        }

        .content li {
            margin-bottom: 3px;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 70px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
        }

        .signature-right {
            text-align: center;
            margin-left: auto;
            width: 230px;
        }

        .signature-right p {
            margin: 6px 0;
            font-size: 11pt;
        }

        .signature-name {
            margin-top: 70px !important;
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-title {
            margin-top: 8px;
        }

        /* Page Break for Lampiran */
        .page-break {
            page-break-before: always;
        }

        /* Table Peserta Hadir */
        .peserta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11pt;
        }

        .peserta-table th {
            border: 1px solid #000;
            padding: 8px 5px;
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .peserta-table td {
            border: 1px solid #000;
            padding: 6px 5px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Kop Surat -->
        <div class="kop-surat">
            <table>
                <tr>
                    <td class="logo-box">
                        @if($instansi->logo && file_exists(public_path('storage/' . $instansi->logo)))
                            <img src="{{ public_path('storage/' . $instansi->logo) }}" alt="Logo">
                        @else
                            <div style="width:60px;height:70px;"></div>
                        @endif
                    </td>
                    <td class="kop-text">
                        <h1>{{ $instansi->pemerintah ?? 'PEMERINTAH DAERAH KOTA BOGOR' }}</h1>
                        <h2>{{ $instansi->nama_instansi ?? 'NAMA PERANGKAT DAERAH' }}</h2>
                        <p>{{ $instansi->alamat ?? 'Jalan .................. Nomor .. Kota Bogor' }}</p>
                        <p>Telepon (0251) {{ $instansi->telepon ?? '..........' }} Faksimile (0251) {{ $instansi->faksimile ?? '..........' }}</p>
                        <p>Situs web {{ $instansi->website ?? 'www................................' }}</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Judul -->
        <div class="judul">
            <h5>NOTULEN</h5>
        </div>

        <!-- Info Rapat -->
        <table class="info-table">
            <tr>
                <td class="label-col">Sidang/Rapat</td>
                <td>:</td>
                <td>{{ $notulen->rapat->nama_kegiatan ?? '......................' }}</td>
            </tr>
            <tr>
                <td class="label-col">Hari/Tanggal</td>
                <td>:</td>
                <td>
                    @if($notulen->rapat)
                        {{ \Carbon\Carbon::parse($notulen->rapat->tanggal)->translatedFormat('l, d F Y') }}
                    @else
                        ......................
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label-col">Waktu Panggilan</td>
                <td>:</td>
                <td>{{ $notulen->rapat->pukul ?? '......................' }} WIB</td>
            </tr>
            <tr>
                <td class="label-col">Waktu Sidang/Rapat</td>
                <td>:</td>
                <td>{{ $notulen->rapat->pukul ?? '......................' }} WIB s/d Selesai</td>
            </tr>
            <tr>
                <td class="label-col">Ruangan</td>
                <td>:</td>
                <td>{{ $notulen->rapat->ruangan->nama_ruangan ?? '......................' }}</td>
            </tr>
        </table>

        <!-- Pejabat Rapat -->
        <p class="section-title">PIMPINAN SIDANG/RAPAT</p>
        <table class="info-table">
            <tr>
                <td class="label-col">Ketua</td>
                <td>:</td>
                <td>{{ $notulen->ketua->name ?? $notulen->ketua_manual ?? '......................' }}</td>
            </tr>
            <tr>
                <td class="label-col">Sekretaris</td>
                <td>:</td>
                <td>{{ $notulen->sekretaris->name ?? $notulen->sekretaris_manual ?? '......................' }}</td>
            </tr>
            <tr>
                <td class="label-col">Pencatat</td>
                <td>:</td>
                <td>{{ $notulen->pencacat->name ?? $notulen->pencacat_manual ?? '......................' }}</td>
            </tr>
        </table>

        <!-- Peserta -->
        <p class="section-title">PESERTA SIDANG/RAPAT</p>
        <div class="content">
            <p><em>(Terlampir pada halaman berikutnya)</em></p>
        </div>

        <!-- Pembukaan -->
        <p class="section-title">1. KATA PEMBUKAAN</p>
        <div class="content">
            @if($notulen->pembukaan)
                {!! $notulen->pembukaan !!}
            @else
                <p>......................</p>
            @endif
        </div>

        <!-- Pembahasan -->
        <p class="section-title">2. PEMBAHASAN</p>
        <div class="content">
            @if($notulen->pembahasan)
                {!! $notulen->pembahasan !!}
            @else
                <p>......................</p>
            @endif
        </div>

        <!-- Peraturan -->
        <p class="section-title">3. PERATURAN</p>
        <div class="content">
            @if($notulen->peraturan)
                {!! $notulen->peraturan !!}
            @else
                <p>......................</p>
            @endif
        </div>

        <!-- Penutup -->
        @if($notulen->penutup)
        <p class="section-title">4. PENUTUP</p>
        <div class="content">
            {!! $notulen->penutup !!}
        </div>
        @endif

        <!-- Tanda Tangan -->
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td></td>
                    <td class="signature-right">
                        <p>{{ $instansi->kota ?? 'Kota Bogor' }}, {{ \Carbon\Carbon::parse($notulen->rapat->tanggal ?? now())->translatedFormat('d F Y') }}</p>
                        <p class="signature-title"><strong>PIMPINAN SIDANG/RAPAT</strong></p>

                        <p class="signature-name">{{ $notulen->ketua->name ?? $notulen->ketua_manual ?? '...........................' }}</p>
                        @if(($notulen->ketua && $notulen->ketua->nip) || $notulen->ketua_manual)
                            <p>NIP. {{ $notulen->ketua->nip ?? '...........................' }}</p>
                        @else
                            <p>NIP. ...........................</p>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- LAMPIRAN: DAFTAR HADIR -->
    <div class="page-break"></div>
    <div class="container">
        <!-- Kop Surat -->
       

        <!-- Judul Lampiran -->
        <div class="judul">
            <h5>LAMPIRAN</h5>
            <p style="font-size: 11pt; margin-top: 5px;">DAFTAR HADIR PESERTA RAPAT</p>
        </div>

        <!-- Info Rapat -->
        <table class="info-table">
            <tr>
                <td class="label-col">Sidang/Rapat</td>
                <td>:</td>
                <td>{{ $notulen->rapat->nama_kegiatan ?? '......................' }}</td>
            </tr>
            <tr>
                <td class="label-col">Hari/Tanggal</td>
                <td>:</td>
                <td>
                    @if($notulen->rapat)
                        {{ \Carbon\Carbon::parse($notulen->rapat->tanggal)->translatedFormat('l, d F Y') }}
                    @else
                        ......................
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label-col">Waktu</td>
                <td>:</td>
                <td>{{ $notulen->rapat->pukul ?? '......................' }} WIB s/d Selesai</td>
            </tr>
            <tr>
                <td class="label-col">Tempat</td>
                <td>:</td>
                <td>{{ $notulen->rapat->ruangan->nama_ruangan ?? '......................' }}</td>
            </tr>
        </table>

        <!-- Tabel Peserta -->
        <table class="peserta-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">No</th>
                    <th>Nama Lengkap</th>
                    <th>Instansi / Dinas</th>
                    <th>Jabatan</th>
                    <th>Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @forelse($daftarHadir as $peserta)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>
                        <strong>{{ $peserta->nama }}</strong>
                        @if($peserta->nip)
                            <br><span style="font-size: 9pt;">NIP. {{ $peserta->nip }}</span>
                        @endif
                    </td>
                    <td>
                       
                            {{ $peserta->nama_external ?? '-' }}
                        
                    </td>
                    <td>{{ $peserta->jabatan ?? '-' }}</td>
                    <td class="text-center">
                        @if($peserta->signature)
                            <img src="{{ public_path('storage/' . $peserta->signature) }}" alt="TTD" style="height: 40px;">
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada peserta yang hadir</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Total Peserta -->
        <div style="margin-top: 15px; font-size: 11pt;">
            <p><strong>Jumlah Peserta: {{ $daftarHadir->count() }} orang</strong></p>
        </div>

        <!-- Tanda Tangan -->
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                   
                  
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
