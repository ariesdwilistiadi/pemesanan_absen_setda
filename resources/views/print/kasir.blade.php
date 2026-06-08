<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan - {{ $transaksi->no_transaksi }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
            width: 58mm; /* Standard thermal printer width 58mm */
        }
        .container {
            padding: 5px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .mt-2 { margin-top: 10px; }
        .mb-2 { margin-bottom: 10px; }
        .border-top { border-top: 1px dashed #000; }
        .border-bottom { border-bottom: 1px dashed #000; }
        .py-1 { padding-top: 5px; padding-bottom: 5px; }
        
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 2px 0; }
        
        .item-name { padding-bottom: 2px; }
        .item-details { padding-bottom: 5px; }

        @media print {
            body { width: 100%; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="text-center mb-2">
            <h2 style="margin:0; font-size: 14px;">Koperasi Dharma Wanita</h2>
            <div>Alamat Balai Kota Bogor</div>
            <div>Telp: </div>
        </div>
        
        <div class="border-top border-bottom py-1 mb-2">
            <table>
                <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>No</td>
                    <td>:</td>
                    <td>{{ $transaksi->no_transaksi }}</td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td>:</td>
                    <td>{{ $transaksi->owner->name ?? 'Kasir' }}</td>
                </tr>
                <tr>
                    <td>Meja</td>
                    <td>:</td>
                    <td>{{ $transaksi->nomor_meja ?: 'Takeaway' }}</td>
                </tr>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td>{{ $transaksi->nama }}</td>
                </tr>
            </table>
        </div>

        <table class="mb-2">
            @forelse($transaksi->details as $item)
            <tr>
                <td colspan="3" class="item-name font-bold">
                    {{ $item->nama_produk_external ?? $item->produk->nama_barang ?? 'Produk Dihapus' }}
                    @if($item->nama_produk_external)
                        <span style="font-size:8px; background:#28a745; color:#fff; padding:1px 3px; border-radius:2px;">EXT</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td width="30%">{{ $item->jumlah }} x</td>
                <td width="35%">{{ $item->harga_satuan > 0 ? number_format($item->harga_satuan, 0, ',', '.') : 'FREE' }}</td>
                <td width="35%" class="text-right">{{ $item->subtotal > 0 ? number_format($item->subtotal, 0, ',', '.') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center">Tidak ada item</td>
            </tr>
            @endforelse
        </table>

        <div class="border-top py-1">
            <table>
                <tr>
                    <td class="font-bold">Total Item</td>
                    <td>:</td>
                    <td class="text-right font-bold">{{ $transaksi->total_item }}</td>
                </tr>
                <tr>
                    <td class="font-bold">Total Harga</td>
                    <td>:</td>
                    <td class="text-right font-bold">{{ number_format($transaksi->total_harga, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Bayar ({{ strtoupper($transaksi->metode_pembayaran) }})</td>
                    <td>:</td>
                    <td class="text-right">{{ number_format($transaksi->jumlah_bayar, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Kembali</td>
                    <td>:</td>
                    <td class="text-right">{{ number_format($transaksi->kembalian, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="text-center mt-2 border-top py-1">
            <div>Terima Kasih</div>
            <div>Atas Kunjungan Anda</div>
        </div>
    </div>
</body>
</html>