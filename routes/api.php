<?php

use App\Http\Controllers\Api\CapsuleController;
use Illuminate\Support\Facades\Route;

// Mevcut kullanıcıyı getiren Sanctum rotasını kaldırabilirsiniz veya Passport'a göre düzenleyebilirsiniz
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


// SpaceX Capsules API Rotaları
// OAuth ile korunan rotalar (auth:api)
Route::middleware('auth:api')->group(function () {
    // 1. Kapsül Listeleme ve Filtreleme
    Route::get('/capsules', [CapsuleController::class, 'index']);

    // 2. Kapsül Detay Görüntüleme
    Route::get('/capsules/{capsule_serial}', [CapsuleController::class, 'show']);
});
