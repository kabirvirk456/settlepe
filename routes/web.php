<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsultationPaymentController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\TeamAuthController;
use App\Http\Controllers\TeamCrmController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/terms-and-conditions', 'legal.terms')->name('terms');

Route::post('/submit-lead', [LeadController::class, 'store'])->name('lead.store');
Route::post('/payments/razorpay/webhook', RazorpayWebhookController::class)->name('payments.razorpay.webhook');

Route::get('/team/login', [TeamAuthController::class, 'showLogin'])->name('team.login');
Route::post('/team/login', [TeamAuthController::class, 'login'])->name('team.login.store');
Route::post('/team/logout', [TeamAuthController::class, 'logout'])->name('team.logout');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'sendOtp'])->name('login.store');
    Route::post('/login/send-otp', [AuthController::class, 'sendOtp'])->name('login.otp');
    Route::post('/login/verify-otp', [AuthController::class, 'verifyOtp'])->name('login.verify');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/cibil-profile', [AuthController::class, 'showCibilProfile'])->name('cibil.profile');
    Route::post('/cibil-profile', [AuthController::class, 'storeCibilProfile'])->name('cibil.profile.store');
    Route::post('/cibil-profile/authenticate', [AuthController::class, 'authenticateCrifReport'])->name('cibil.profile.authenticate');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/portal', [CustomerPortalController::class, 'dashboard'])->name('portal.dashboard');
    Route::post('/portal/documents', [CustomerPortalController::class, 'storeDocument'])->name('portal.documents.store');
    Route::get('/portal/documents/{document}/download', [CustomerPortalController::class, 'downloadDocument'])->name('portal.documents.download');
    Route::patch('/portal/tasks/{task}/complete', [CustomerPortalController::class, 'completeTask'])->name('portal.tasks.complete');
    Route::get('/portal/notices/{notice}/download', [CustomerPortalController::class, 'downloadNotice'])->name('portal.notices.download');
    Route::post('/consultation/orders', [ConsultationPaymentController::class, 'createOrder'])->name('consultation.orders.store');
    Route::post('/consultation/verify', [ConsultationPaymentController::class, 'verify'])->name('consultation.payments.verify');
    Route::get('/team', [TeamCrmController::class, 'home'])->name('team.home');
    Route::get('/team/sales', [TeamCrmController::class, 'sales'])->name('team.sales');
    Route::get('/team/rm', [TeamCrmController::class, 'rm'])->name('team.rm');
    Route::get('/team/admin', [TeamCrmController::class, 'admin'])->name('team.admin');
    Route::get('/team/leads/{lead}', [TeamCrmController::class, 'show'])->name('team.leads.show');
    Route::patch('/team/leads/{lead}/assignment', [TeamCrmController::class, 'updateAssignment'])->name('team.leads.assignment');
    Route::patch('/team/leads/{lead}/sales', [TeamCrmController::class, 'updateSales'])->name('team.leads.sales');
    Route::patch('/team/leads/{lead}/rm', [TeamCrmController::class, 'updateRm'])->name('team.leads.rm');
    Route::post('/team/leads/{lead}/activities', [TeamCrmController::class, 'storeActivity'])->name('team.leads.activities');
    Route::patch('/team/leads/{lead}/case', [TeamCrmController::class, 'updateCase'])->name('team.leads.case');
    Route::post('/team/leads/{lead}/tasks', [TeamCrmController::class, 'storeTask'])->name('team.leads.tasks');
    Route::post('/team/leads/{lead}/legal-notices', [TeamCrmController::class, 'storeLegalNotice'])->name('team.leads.legal-notices');
    Route::post('/team/leads/{lead}/documents', [TeamCrmController::class, 'storeDocument'])->name('team.leads.documents');
    Route::patch('/team/documents/{document}/review', [TeamCrmController::class, 'reviewDocument'])->name('team.documents.review');
    Route::get('/team/documents/{document}/download', [TeamCrmController::class, 'downloadDocument'])->name('team.documents.download');
    Route::post('/team/leads/{lead}/settlement-accounts', [TeamCrmController::class, 'storeSettlementAccount'])->name('team.leads.settlement-accounts');
    Route::patch('/team/settlement-accounts/{settlementAccount}', [TeamCrmController::class, 'updateSettlementAccount'])->name('team.settlement-accounts.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
