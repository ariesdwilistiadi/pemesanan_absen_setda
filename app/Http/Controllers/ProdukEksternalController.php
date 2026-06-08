<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProdukEksternalController extends Controller
{
    /**
     * URL API External
     */
    private $apiBaseUrl = 'https://labs-koperasidharmawanita.kotabogor.go.id/api';

    /**
     * Credential untuk login
     */
    private $credentials = [
        'email' => 'apisekda@kotabogor.com',
        'password' => 'Sekda123##',
        'device_name' => 'sekda'
    ];

    /**
     * Ambil token dari cache atau login ulang
     */
    private function getToken()
    {
        // Cek cache dulu
        $token = Cache::get('external_api_token');

        if ($token) {
            return $token;
        }

        // Login untuk dapat token
        try {
            $response = Http::timeout(30)
                ->withOptions([
                    'verify' => false, // Skip SSL verification untuk server lokal
                ])
                ->asJson()
                ->post($this->apiBaseUrl . '/auth/login', $this->credentials);

            $body = $response->json();
            $token = $body['token'] ?? $body['access_token'] ?? null;

            if ($token) {
                // Simpan di cache (24 jam)
                Cache::put('external_api_token', $token, now()->addHours(24));
                Log::info('External API token obtained successfully');
                return $token;
            }

            Log::warning('External API login response: ' . json_encode($body));
        } catch (\Exception $e) {
            Log::error('Login API External gagal: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Ambil semua produk dari API external
     */
    public function index(Request $request)
    {
        $token = $this->getToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal login ke server. Cek koneksi internet atau credentials.',
                'produks' => []
            ]);
        }

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->withOptions([
                    'verify' => false, // Skip SSL verification
                ])
                ->get($this->apiBaseUrl . '/sekda/produks');

            Log::info('External API response status: ' . $response->status());

            if ($response->successful()) {
                $data = $response->json();

                // Handle different response structures
                if (is_array($data) && isset($data[0])) {
                    // Data adalah array langsung
                    $items = $data;
                } elseif (is_array($data) && isset($data['data'])) {
                    // Data ada di key 'data'
                    $items = $data['data'];
                } elseif (is_array($data) && isset($data['produks'])) {
                    // Data ada di key 'produks'
                    $items = $data['produks'];
                } else {
                    $items = [];
                }

                // Transform data ke format yang sama dengan produk lokal
                $produks = collect($items)->map(function ($item) {
                    return [
                        'id' => $item['id'] ?? uniqid(),
                        'kode_barang' => $item['kode'] ?? $item['kode_barang'] ?? '',
                        'nama_barang' => $item['nama'] ?? $item['nama_barang'] ?? $item['produk'] ?? '',
                        'harga_jual' => $item['harga'] ?? $item['harga_jual'] ?? $item['harga_jual'] ?? 0,
                        'stok' => $item['stok'] ?? $item['stok'] ?? 999,
                        'satuan' => $item['satuan'] ?? 'pcs',
                        'gambar' => $item['gambar'] ?? $item['foto'] ?? null,
                        'sumber' => 'external',
                    ];
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Produk berhasil dimuat',
                    'produks' => $produks,
                    'debug' => [
                        'items_count' => count($items),
                        'response_structure' => array_keys($data)
                    ]
                ]);
            }

            // Jika token expired atau unauthorized
            if ($response->status() === 401) {
                Cache::forget('external_api_token');
                Log::warning('External API token expired');

                // Retry dengan token baru
                $newToken = $this->getToken();
                if ($newToken) {
                    return $this->fetchProducts($newToken);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Sesi habis. Silakan refresh.',
                    'produks' => []
                ]);
            }

            // Server error atau lainnya
            Log::error('External API error: ' . $response->status() . ' - ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Fetch produk external error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => false,
            'message' => 'Koneksi ke server external gagal. Server mungkin sedang down.',
            'produks' => []
        ]);
    }

    /**
     * Ambil data penjualan dari API external
     */
    public function penjualan(Request $request)
    {
        $token = $this->getToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal login ke server. Cek koneksi internet atau credentials.',
                'data' => []
            ]);
        }

        // Default tanggal bulan ini
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-t'));
        $kategori = $request->get('kategori', 'ATK');

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->withOptions([
                    'verify' => false,
                ])
                ->get($this->apiBaseUrl . '/sekda/penjualan', [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'kategori' => $kategori,
                ]);

            Log::info('External API Penjualan response status: ' . $response->status());

            if ($response->successful()) {
                $data = $response->json();

                return response()->json([
                    'success' => true,
                    'message' => 'Data penjualan berhasil dimuat',
                    'data' => $data,
                    'filters' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'kategori' => $kategori,
                    ]
                ]);
            }

            // Jika token expired
            if ($response->status() === 401) {
                Cache::forget('external_api_token');
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi habis. Silakan refresh.',
                    'data' => []
                ]);
            }

            Log::error('External API Penjualan error: ' . $response->status());

        } catch (\Exception $e) {
            Log::error('Fetch penjualan external error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => false,
            'message' => 'Koneksi ke server gagal.',
            'data' => []
        ]);
    }

    /**
     * Fetch products with specific token (for retry)
     */
    private function fetchProducts($token)
    {
        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->withOptions(['verify' => false])
                ->get($this->apiBaseUrl . '/sekda/produks');

            if ($response->successful()) {
                $data = $response->json();
                $items = is_array($data) ? ($data['data'] ?? $data['produks'] ?? $data) : [];

                $produks = collect($items)->map(function ($item) {
                    return [
                        'id' => $item['id'] ?? uniqid(),
                        'kode_barang' => $item['kode'] ?? '',
                        'nama_barang' => $item['nama'] ?? $item['nama_barang'] ?? '',
                        'harga_jual' => $item['harga'] ?? 0,
                        'stok' => $item['stok'] ?? 999,
                        'satuan' => $item['satuan'] ?? 'pcs',
                        'gambar' => $item['gambar'] ?? null,
                        'sumber' => 'external',
                    ];
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Produk berhasil dimuat',
                    'produks' => $produks
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Retry fetch products error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengambil data produk',
            'produks' => []
        ]);
    }

    /**
     * Refresh token (logout/login ulang)
     */
    public function refreshToken()
    {
        Cache::forget('external_api_token');
        $token = $this->getToken();

        if ($token) {
            return response()->json([
                'success' => true,
                'message' => 'Token berhasil di-refresh'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal refresh token. Server mungkin sedang down.'
        ]);
    }

    /**
     * Test koneksi ke API
     */
    public function testConnection()
    {
        try {
            $response = Http::timeout(10)
                ->withOptions(['verify' => false])
                ->asJson()
                ->post($this->apiBaseUrl . '/auth/login', $this->credentials);

            return response()->json([
                'success' => true,
                'status' => $response->status(),
                'response' => $response->json()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}