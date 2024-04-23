<?php
use Noorfarooqy\EasyNotifications\Controllers\EmailController;

Route::group(['prefix' => '/api/v1/en/', 'as' => 'api.en.'], function () {
    Route::post('send-email', [EmailController::class,'sendEmail'])->name('send-email');
});