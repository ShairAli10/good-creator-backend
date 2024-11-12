<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Auth Apis
Route::controller(AuthController::class)->group(function () {
    Route::post('signUp', 'sign_up')->name('signUp');
    Route::post('login', 'sign_in')->name('login');
    Route::post('socialLoginSignUp', 'social_login_signUp')->name('socialLoginSignUp');
    Route::put('editSocialProfile', 'edit_social_profile')->name('editSocialProfile');
    Route::post('updateDeviceId', 'update_device_id')->name('updateDeviceId');
    Route::post('updateFirebaseid', 'update_firebase_id')->name('updateFirebaseid');
    Route::post('logout', 'logout_profile')->name('logout');
    Route::post('verifyEmailAddress', 'generate_otp')->name('verifyEmailAddress');

});


// forgot password
Route::controller(ForgotPasswordController::class)->group(function () {
    Route::post('generateOTP', 'generate_otp')->name('generateOTP');
    Route::post('verifyOTP', 'verify_otp')->name('verifyOTP');
    Route::post('resetPassword', 'reset_password')->name('resetPassword');
});
