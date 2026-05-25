# absen_sekda

Project ini sekarang mendukung auth API berbasis Sanctum dengan `access token` dan `refresh token`.

## Endpoint Auth API

- `POST /api/auth/login`
- `POST /api/auth/refresh`
- `GET /api/auth/me`
- `POST /api/auth/logout`

Contoh request login:

```json
{
  "email": "user@example.com",
  "password": "password",
  "device_name": "web-app"
}
```

Response dari `login` dan `refresh`:

```json
{
  "token_type": "Bearer",
  "access_token": "...",
  "access_token_expires_at": "2026-05-25T12:34:56+00:00",
  "refresh_token": "...",
  "refresh_token_expires_at": "2026-06-01T12:34:56+00:00",
  "user": {
    "id": 1,
    "name": "Demo User",
    "email": "user@example.com"
  }
}
```

## Konfigurasi Expiry

- `SANCTUM_ACCESS_TOKEN_TTL=15`
- `SANCTUM_REFRESH_TOKEN_TTL_DAYS=7`

## Hardening Login & Session

- login memakai verifikasi manusia sederhana berbasis perhitungan
- ada honeypot field tersembunyi untuk bot dasar
- submit login yang terlalu cepat ditolak
- rate limit login dipisah per email+IP dan per IP
- sesi idle diputus oleh server setelah `SESSION_IDLE_TIMEOUT`
- halaman sensitif diberi header `no-store`

Konfigurasi tambahan:

- `SESSION_IDLE_TIMEOUT=15`
- `LOGIN_MIN_FORM_FILL_SECONDS=2`
- `PERMISSION_DENIAL_MAX_ATTEMPTS=5`
- `PERMISSION_DENIAL_LOCKOUT_SECONDS=60`

## Frontend

`resources/js/bootstrap.js` sudah menangani:

- bearer token otomatis di `axios`
- refresh token otomatis saat response `401`
- retry request sekali setelah refresh berhasil
- hapus token lokal jika refresh gagal
