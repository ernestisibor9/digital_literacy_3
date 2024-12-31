<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BulkMemberRegistrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);


    // Resend Verification Email
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['auth:sanctum', 'signed'])
        ->name('verification.verify');
    // Resend Verification Email
    Route::middleware('auth:sanctum')->post('/email/verify/resend', [AuthController::class, 'resendVerificationEmail']);


    // Protected routes (only for admin)
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/upload-csv', [BulkMemberRegistrationController::class, 'uploadCSV']);
    });

    Route::get('auth/verify/{email}/{password}', [BulkMemberRegistrationController::class, 'verifyEmail']);
});
