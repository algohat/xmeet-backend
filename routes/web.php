<?php


use App\Http\Controllers\backend\AdminAuthController;
use App\Http\Controllers\backend\AdminController;
use App\Http\Controllers\backend\InterestController;
use App\Http\Controllers\backend\SubscriptionController;
use App\Http\Controllers\backend\PackageController;
use App\Http\Controllers\backend\UserlistController;
use App\Http\Controllers\Auth\CustomPasswordResetController;
use Illuminate\Support\Facades\Route;


// Backend

Route::get('new-password/{token}', [CustomPasswordResetController::class, 'showResetForm'])->name('new.password');
Route::post('new-password', [CustomPasswordResetController::class, 'resetPassword'])->name('new.password');

Route::get('/', function () {
	return to_route('admin.login.form');
});

Route::get('admin/login', [AdminAuthController::class, 'loginForm'])->name('admin.login.form');
Route::post('admin-login', [AdminAuthController::class, 'login'])->name('admin.login');

Route::prefix('admin')->middleware('auth.admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

    Route::get('/user-lists', [UserlistController::class, 'index'])->name('user.index');
    Route::delete('/user-delete/{id}', [UserlistController::class, 'delete'])->name('user.delete');

    Route::resource('interests', InterestController::class);

    Route::resource('packages', PackageController::class);


});


require __DIR__.'/auth.php';

