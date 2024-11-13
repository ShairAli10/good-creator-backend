<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Creator\ProfileController;
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
    Route::post('updateLocation', 'update_location')->name('updateLocation');
    Route::post('logout', 'logout_profile')->name('logout');
    Route::get('services', 'get_all_services')->name('services');
    Route::get('packages', 'get_all_packages')->name('packages');
    Route::post('userSocialEdit', 'user_social_edit')->name('userSocialEdit');
    Route::post('creatorSocialEdit', 'creator_social_edit')->name('creatorSocialEdit');


});

Route::group([
    'prefix' => 'creator/profile',
], function () {
    Route::post('/myProfile', [ProfileController::class, 'my_profile']);
    Route::put('/profileImage', [ProfileController::class, 'edit_profile_image']);
    Route::put('/updateName', [ProfileController::class, 'edit_name']);
    Route::put('/updateBio', [ProfileController::class, 'edit_bio']);
    Route::post('/media', [ProfileController::class, 'edit_media']);
    Route::post('/deleteMedia', [ProfileController::class, 'delete_media']);



});


// forgot password
Route::controller(ForgotPasswordController::class)->group(function () {
    Route::post('generateOTP', 'generate_otp')->name('generateOTP');
    Route::post('verifyOTP', 'verify_otp')->name('verifyOTP');
    Route::post('resetPassword', 'reset_password')->name('resetPassword');
});
