<?php

use App\Http\Controllers\AppErrorController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CallLogController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactStatusController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\DealStageController;
use App\Http\Controllers\FollowUpsController;
use App\Http\Controllers\LeadNotesController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\QuotationStatusController;
use App\Http\Controllers\SpecificationController;
use App\Http\Controllers\TaskController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// error log
Route::post('/app-errors', [AppErrorController::class, 'store']);

Route::middleware('auth:api')->group(function () {

    Route::put('/user/company-info', [AuthController::class, 'updateCompanyInfo']);

    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    // Leads
    Route::get('/leads', [LeadController::class, 'index']);
    Route::post('/leads', [LeadController::class, 'store']);
    Route::delete('/leads/{id}', [LeadController::class, 'destroy']);
    Route::put('/leads/{id}', [LeadController::class, 'update']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Quotations
    Route::get('/quotations', [QuotationController::class, 'index']);
    Route::get('/quotations/stage', [QuotationController::class, 'getQuotationByStage']);
    Route::patch('/quotations/stage/{id}', [QuotationController::class, 'updateStage']);
    Route::get('/quotations/{id}', [QuotationController::class, 'show']);
    Route::post('/quotations', [QuotationController::class, 'store']);
    Route::put('/quotations/{id}', [QuotationController::class, 'update']);
    Route::get('/quotations/lead/{leadId}', [QuotationController::class, 'getQuotationsByLead']);

    // Terms routes
    Route::get('/terms', [TermsController::class, 'getTerms']);
    Route::post('/terms', [TermsController::class, 'storeTerm']);
    Route::delete('/terms/{id}', [TermsController::class, 'destroyTerm']);

    // Payment Terms routes
    Route::get('/payment-terms', [TermsController::class, 'getPaymentTerms']);
    Route::post('/payment-terms', [TermsController::class, 'storePaymentTerm']);
    Route::delete('/payment-terms/{id}', [TermsController::class, 'destroyPaymentTerm']);

    // Specification routes
    Route::post('/specifications', [SpecificationController::class, 'addSpecification']);
    Route::get('/specifications', [SpecificationController::class, 'getSpecifications']);
    Route::delete('/specifications/{id}', [SpecificationController::class, 'deleteSpecification']);
    Route::put('/specifications/{id}', [SpecificationController::class, 'updateSpecification']);

    // Follow-ups routes
    Route::post('/follow-ups', [FollowUpsController::class, 'createFollowUp']);
    Route::get('/follow-ups', [FollowUpsController::class, 'getFollowUps']);
    Route::put('/follow-ups/{id}', [FollowUpsController::class, 'updateFollowUp']);
    Route::delete('/follow-ups/{id}', [FollowUpsController::class, 'deleteFollowUp']);
    Route::get('/follow-ups/lead/{leadId}', [FollowUpsController::class, 'getFollowUpsByLead']);

    // Lead notes
    Route::post('/notes', [LeadNotesController::class, 'createLeadNote']);
    Route::get('/notes/lead/{lead_id}', [LeadNotesController::class, 'getNotesByLead']);
    Route::put('/notes/{id}', [LeadNotesController::class, 'updateLeadNote']);
    Route::delete('/notes/{id}', [LeadNotesController::class, 'deleteLeadNote']);

    // Task 
    Route::post('/tasks', [TaskController::class, 'createTask']);
    Route::get('/tasks', [TaskController::class, 'getTasks']);
    Route::put('/tasks/{id}', [TaskController::class, 'updateTask']);
    Route::delete('/tasks/{id}', [TaskController::class, 'deleteTask']);
    Route::get('/tasks/lead/{lead_id}', [TaskController::class, 'getTaskByLead']);
    Route::get('/tasks/today', [TaskController::class, 'todayTasks']);

    // Task status and priority
    Route::get('/tasks/status', [TaskController::class, 'getTaskStatus']);
    Route::get('/tasks/priority', [TaskController::class, 'getTaskPriority']);

    // Activity Logs
    Route::get('/activity/logs', [LeadController::class, 'getLogs']);

    // Contact Status
    Route::get('/contact-status', [ContactStatusController::class, 'getStatus']);

    // Quotation Status
    Route::get('/quotation-status', [QuotationStatusController::class, 'getStatus']);

    // call log
    Route::post('/call-log', [CallLogController::class, 'store']);
    Route::get('/call-log', [CallLogController::class, 'index']);
    Route::delete('/call-log/{id}', [CallLogController::class, 'destroy']);

    // Deals
    Route::get('/deals', [DealController::class, 'index']);
    Route::post('/deals', [DealController::class, 'store']);
    Route::get('/deals/{id}', [DealController::class, 'show']);
    Route::put('/deals/{id}', [DealController::class, 'update']);
    Route::delete('/deals/{id}', [DealController::class, 'destroy']);
    Route::delete('/deals/{id}', [DealController::class, 'destroy']);
    Route::patch('/deals/stage/{id}', [DealController::class, 'dealStageChange']);

    // Deal Stage
    Route::get('/deal-stage', [DealStageController::class, 'getDealStage']);
});
