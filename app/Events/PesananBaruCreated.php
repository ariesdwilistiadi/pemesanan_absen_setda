<?php

namespace App\Events;

use App\Models\TransaksiHeader;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PesananBaruCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaksi;

    /**
     * Create a new event instance.
     */
    public function __construct(TransaksiHeader $transaksi)
    {
        $this->transaksi = $transaksi;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->transaksi->id,
            'no_transaksi' => $this->transaksi->no_transaksi,
            'nama' => $this->transaksi->nama,
            'nomor_meja' => $this->transaksi->nomor_meja,
            'total_item' => $this->transaksi->total_item,
            'total_harga' => $this->transaksi->total_harga,
            'status' => $this->transaksi->status,
            'tanggal_transaksi' => $this->transaksi->tanggal_transaksi,
            'details' => $this->transaksi->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'jumlah' => $detail->jumlah,
                    'nama_produk' => $detail->produk ? $detail->produk->nama_barang : 'Produk Dihapus',
                ];
            }),
            'created_at' => $this->transaksi->created_at->toIso8601String(),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new Channel('pesanan');
    }
}