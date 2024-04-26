<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RDIntegrationController;
use App\Http\Controllers\CadastraOportunidadeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/NewClient', [RDIntegrationController::class, 'Index']);
Route::get('/BotNewContact', [CadastraOportunidadeController::class, 'Index']);