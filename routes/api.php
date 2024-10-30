<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;



// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
// });

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [AuthController::class, 'showProfile']);
    Route::post('profile', [AuthController::class, 'updateProfile']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);

    Route::get('random-user/{postal_code}', [AuthController::class, 'getRandomUserByPostalCode']);
    Route::post('user-subscription', [AuthController::class, 'subscribeToPackage']);
    Route::get('all-users', [AuthController::class, 'getAllUsers']);
    Route::get('users/filter', [AuthController::class, 'filterUsers']);
    Route::get('check-subscription', [AuthController::class, 'checkSubscriptionStatus']);


});
