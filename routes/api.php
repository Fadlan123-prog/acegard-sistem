<?php

use App\Http\Controllers\Api\AttendanceApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('attendance')->name('api.attendance.')->controller(AttendanceApiController::class)->group(function () {
    Route::post('/check-in',  'checkIn')->name('checkIn');
    Route::post('/check-out', 'checkOut')->name('checkOut');
});
