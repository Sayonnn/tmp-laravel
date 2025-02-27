<?php
 
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// socialite routes
Route::controller(SocialiteController::class)->group(function () {
    // NOTE: This route redirect the user to the google authentication page
    Route::get('/auth/google', 'googleLogin')->name('auth.google');
    // NOTE: This route process the google authentication : This includes sending the google_token to passport server after google authentication
    Route::get('/auth/google-callback', 'googleAuthentication')->name('auth.google-callback');

    Route::get('/oauth/authorize', 'passportAuthorization')->name('oauth.authorize');
    // NOTE: This route get the authorization code from passport that will be sent after verifying the google token sent by the client
    Route::get('/oauth/callback', 'passportCallback')->name('auth.callback');
    // NOTE: This route is use to refresh tokens
    Route::post('/oauth/refresh-token', 'refreshToken')->name('auth.refresh-token');
});

require __DIR__.'/auth.php';
