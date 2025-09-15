<?php

use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MenuItemController::class, 'index'])->name('menu.index');
Route::get('/menu', [MenuItemController::class, 'index'])->name('menu.list');
Route::post('/pedidos', [OrderController::class, 'store'])->name('pedidos.store');
