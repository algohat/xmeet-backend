<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;



// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
// });


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/interest', [AuthController::class, 'getInterest']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('/packages', [AuthController::class, 'packages']);


Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [AuthController::class, 'showProfile']);
    Route::post('profile', [AuthController::class, 'updateProfile']);


    Route::get('random-user', [AuthController::class, 'getRandomUserByPostalCode']);
    Route::get('all-users', [AuthController::class, 'getAllUsers']);
    Route::get('users/filter', [AuthController::class, 'filterUsers']);
    Route::get('check-subscription', [AuthController::class, 'checkSubscriptionStatus']);

    Route::post('subscription', [AuthController::class, 'subscribeToPackage']);
    Route::get('success', [AuthController::class, 'success'])->name('paypal.success');
    Route::get('cancel', [AuthController::class, 'cancel'])->name('paypal.cancel');
});
