<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuotationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {

    Route::put('/user/company-info', [AuthController::class, 'updateCompanyInfo']);
    
    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    // Leads
    Route::get('/leads', [LeadController::class, 'index']);
    Route::post('/leads', [LeadController::class, 'store']);
    Route::delete('/leads/{id}', [LeadController::class, 'destroy']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Quotations
    Route::get('/quotations', [QuotationController::class, 'index']);
    Route::get('/quotations/{id}', [QuotationController::class, 'show']);
    Route::post('/quotations', [QuotationController::class, 'store']);
    Route::put('/quotations/{id}', [QuotationController::class, 'update']);
    
     // Terms routes
    Route::get('/terms', [TermsController::class, 'getTerms']);
    Route::post('/terms', [TermsController::class, 'storeTerm']);
    Route::delete('/terms/{id}', [TermsController::class, 'destroyTerm']);

    // Payment Terms routes
    Route::get('/payment-terms', [TermsController::class, 'getPaymentTerms']);
    Route::post('/payment-terms', [TermsController::class, 'storePaymentTerm']);
    Route::delete('/payment-terms/{id}', [TermsController::class, 'destroyPaymentTerm']);
});
