<?php

use App\Http\Controllers\ChartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/sales/yearly', [ChartController::class, 'getYearlySales']);
Route::get('/sales/items', [ChartController::class, 'getItemSales']);
