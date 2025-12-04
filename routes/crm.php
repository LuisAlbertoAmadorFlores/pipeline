<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DealController;

// CRM Pipeline routes
Route::get('/crm', [DealController::class, 'index'])->name('deals.index');
Route::get('/crm/create', [DealController::class, 'create'])->name('deals.create')->middleware('can_manage_deals');
Route::post('/crm', [DealController::class, 'store'])->name('deals.store')->middleware('can_manage_deals');
Route::get('/crm/{deal}/edit', [DealController::class, 'edit'])->name('deals.edit')->middleware('can_manage_deals');
Route::put('/crm/{deal}', [DealController::class, 'update'])->name('deals.update')->middleware('can_manage_deals');
Route::delete('/crm/{deal}', [DealController::class, 'destroy'])->name('deals.destroy')->middleware('can_manage_deals');

// API endpoint to move a deal to another stage (used by drag & drop)
Route::patch('/crm/{deal}/move', [DealController::class, 'move'])->name('deals.move')->middleware('can_manage_deals');
