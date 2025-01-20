<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ChatController;



// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
// });


Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/interest', [AuthController::class, 'getInterest']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('/packages', [AuthController::class, 'packages']);
Route::get('/set-identifier', [UserController::class, 'setIdentifier']);


Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [UserController::class, 'showProfile']);
    Route::post('profile', [UserController::class, 'updateProfile']);
    Route::get('user/disable', [UserController::class, 'disableAccount']);

    Route::post('set-message', [ChatController::class, 'setMessage']);
    Route::get('view-chat', [ChatController::class, 'viewChat']);

    Route::get('random-user', [UserController::class, 'getRandomUserByPostalCode']);
    Route::get('all-users', [UserController::class, 'getAllUsers']);
    Route::get('users/filter', [UserController::class, 'filterUsers']);
    Route::get('check-subscription', [UserController::class, 'checkSubscriptionStatus']);

    Route::post('subscription', [UserController::class, 'subscribeToPackage']);
    Route::get('success', [UserController::class, 'success'])->name('paypal.success');
    Route::get('cancel', [UserController::class, 'cancel'])->name('paypal.cancel');

    Route::delete('user-delete', [UserController::class, 'deleteUser']);
});
