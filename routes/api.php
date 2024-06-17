<?php

use Illuminate\Support\Facades\Route;
use Noorfarooqy\EasyNotifications\Controllers\EmailController;

Route::group(['prefix' => '/api/v1/en/', 'as' => 'api.en.'], function () {
    Route::post('send-email', [EmailController::class, 'sendEmail'])->name('send-email');
    Route::post('/at/bulk-sms', [EmailController::class, 'sendSms'])->name('send-sms');
});
