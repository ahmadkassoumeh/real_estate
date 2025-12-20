<?php

use App\Http\Controllers\LoginWebController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/login', [LoginWebController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginWebController::class, 'login']);

Route::post('/logout', [LoginWebController::class, 'logout'])
    ->name('logout');

Route::middleware(['auth:web', EnsureAdmin::class])
    ->prefix('admin')
    ->group(function () {

        Route::get('/users/pending', [LoginWebController::class, 'index'])
            ->name('admin.users.pending');

        Route::post('/users/{user}/approve', [LoginWebController::class, 'approve']);

        Route::post('/users/{user}/reject', [LoginWebController::class, 'reject']);

        Route::get('/user-image/{userId}/{type}', function($userId, $type) {
            $disk = Storage::disk('users');
            
            if ($type === 'profile') {
                $path = "{$userId}/profile";
            } else {
                $path = "{$userId}/id-card";
            }
            
            $files = $disk->files($path);
            if (empty($files)) {
                abort(404);
            }
            
            $file = $files[0];
            return response($disk->get($file))
                ->header('Content-Type', $disk->mimeType($file));
        })->name('admin.user.image');

    });


    Route::get('/apartments/{apartment}', [LoginWebController::class, 'showImages']);