<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReportController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('api')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    Route::get('/books/search', [BookController::class, 'search']);
    Route::apiResource('books', BookController::class);
    
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('members', MemberController::class);
    Route::apiResource('loans', LoanController::class)->except(['update', 'destroy']);
    Route::get('loans/history/{memberId}', [LoanController::class, 'history']);
    Route::get('loans/{id}/return', [LoanController::class, 'returnBook']);
    Route::put('loans/{id}/return', [LoanController::class, 'processReturn']);
    Route::get('/reports/books', [ReportController::class, 'bookReport']);
    Route::get('/reports/loans', [ReportController::class, 'loanReport']);
    Route::post('/members/register', [MemberController::class, 'register']);
    Route::post('/members/loans', [LoanController::class, 'memberStore']);
});