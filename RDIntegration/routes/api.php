<?php

use App\Http\Controllers\SendToRd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RDIntegrationController;
use App\Http\Controllers\NewOpportunity;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/UpdateOrganization', [RDIntegrationController::class, 'UpdateOrganization']);
Route::post('/SendToRd', [SendToRd::class, 'Send']);
Route::post('/BotNewContact', [NewOpportunity::class, 'Index']);
