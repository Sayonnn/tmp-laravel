<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use Illuminate\Http\Request;
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
Route::controller(ServerController::class)->group(function () {
    // NOTE: This route redirect the user to the google authentication page
    Route::get('/auth/google', 'googleLogin')->name('auth.google');
    // NOTE: This route process the google authentication : This includes sending the google_token to passport server after google authentication
    Route::get('/auth/google-callback', 'googleAuthentication')->name('auth.google-callback');
});

Route::get('/dashboard/clients', function (Request $request) {

    return view('clients', [
        'clients' => $request->user()->clients
    ]);
})->middleware(['auth'])->name('dashboard.clients');


require __DIR__ . '/auth.php';
