<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\PaymentController;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/interest', [AuthController::class, 'getInterest']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('/packages', [AuthController::class, 'packages']);
Route::get('/set-identifier', [UserController::class, 'setIdentifier']);


Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    /*User*/
    Route::get('profile', [UserController::class, 'showProfile']);
    Route::get('user-details/{identifier}', [UserController::class, 'userDetails']);
    Route::post('profile', [UserController::class, 'updateProfile']);
    Route::get('user/disable', [UserController::class, 'disableAccount']);
    Route::get('random-user', [UserController::class, 'getRandomUserByPostalCode']);
    Route::get('all-users', [UserController::class, 'getAllUsers']);
    Route::get('check-subscription', [UserController::class, 'checkSubscriptionStatus']);
    Route::get('current-package', [UserController::class, 'checkCurrentPackage']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::delete('user-delete', [UserController::class, 'deleteUser']);

    /*Chat*/
    Route::post('set-message', [ChatController::class, 'setMessage']);
    Route::get('view-chat', [ChatController::class, 'viewChat']);

    /*Payment*/
    Route::post('subscription', [PaymentController::class, 'subscribeToPackage']);

});

//callback urls
Route::get('success', [PaymentController::class, 'success'])->name('paypal.success');
Route::get('cancel', [PaymentController::class, 'cancel'])->name('paypal.cancel');
