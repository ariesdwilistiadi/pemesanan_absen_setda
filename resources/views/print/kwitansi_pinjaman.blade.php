<!DOCTYPE html>
<html>
<head>
    <title>Kwitansi #{{ $data->id_pinjaman_bayar }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; width: 100%; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        .content { margin-top: 20px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .footer { margin-top: 30px; text-align: center; border-top: 1px dashed #000; padding-top: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2 style="margin:0">KOPERASI MASTERING</h2>
        <p style="margin:5px">Bukti Pembayaran Angsuran</p>
    </div>

    <div class="content">
        <div class="row"><span>No. Kwitansi</span> <span>: #{{ $data->id_pinjaman_bayar }}</span></div>
        <div class="row"><span>Tanggal</span> <span>: {{ $data->tanggal_bayar }}</span></div>
        <div class="row"><span>Nama Anggota</span> <span>: {{ $data->anggota->nama }}</span></div>
        <div class="row"><span>ID Anggota</span> <span>: {{ $data->anggota->no_anggota }}</span></div>
        <hr style="border: 0.5px dashed #000">
        <div class="row"><span>Angsuran Pokok</span> <span>: Rp {{ number_format($data->bayar, 0, ',', '.') }}</span></div>
        <div class="row"><span>Jasa / Bunga</span> <span>: Rp {{ number_format($data->bunga, 0, ',', '.') }}</span></div>
        <div class="row"><span>Denda</span> <span>: Rp {{ number_format($data->denda, 0, ',', '.') }}</span></div>
        <div class="row" style="font-weight: bold;"><span>TOTAL BAYAR</span> <span>: Rp {{ number_format($data->bayar + $data->bunga + $data->denda, 0, ',', '.') }}</span></div>
    </div>

    <div class="footer">
        <p>Terima kasih atas pembayaran Anda.</p>
        <p style="margin-top: 20px;">( Petugas: {{ $data->username }} )</p>
        <button onclick="window.print()" class="no-print">Cetak Ulang</button>
    </div>
</body>
</html>