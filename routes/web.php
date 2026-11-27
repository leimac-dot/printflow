<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\PrintModelController;
use App\Models\PrintModel;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $quotes = CalculatorController::visibleQuotes();
    $models = PrintModel::latest()->get();

    return view('calculator', compact('quotes', 'models'));
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [AuthController::class, 'logout']);

Route::post('/quotes', [CalculatorController::class, 'store']);
Route::patch('/quotes/{quote}/status', [CalculatorController::class, 'updateStatus']);
Route::post('/models', [PrintModelController::class, 'store']);
Route::post('/models/{model}', [PrintModelController::class, 'update']);
Route::delete('/models/{model}', [PrintModelController::class, 'destroy']);