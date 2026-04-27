<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::post('/submit-lead', [App\Http\Controllers\LeadController::class, 'store']);