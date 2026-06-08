<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the broadcast channels for your application.
| A channel is a public or private feed that events can be broadcast to.
|
*/

// Channel publik untuk pesanan - semua user bisa akses
Broadcast::channel('pesanan', function ($user) {
    return true; // Allow semua user untuk subscribe
});

// Channel private untuk pesanan (opsional - berdasarkan ownership)
// Broadcast::channel('pesanan.{userId}', function ($user, $userId) {
//     return $user->id === (int) $userId;
// });