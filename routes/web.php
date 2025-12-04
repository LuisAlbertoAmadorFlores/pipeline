<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

// CRM routes
require __DIR__ . '/crm.php';

// Password change (forced) routes
use App\Http\Controllers\Auth\ChangePasswordController;

Route::middleware('auth')->group(function () {
    Route::get('/user/change-password', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::post('/user/change-password', [ChangePasswordController::class, 'update'])->name('password.force_update');
});

// ChatBot routes
Route::middleware('auth')->group(function () {
    Route::get('/chatbot', [ChatBotController::class, 'index'])->name('chatbot.index');
    Route::post('/chatbot/send', [ChatBotController::class, 'sendMessage'])->name('chatbot.send');
});

// WhatsApp quick open
Route::middleware('auth')->group(function () {
    Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::post('/whatsapp/open', [WhatsAppController::class, 'open'])->name('whatsapp.open');
    Route::get('/whatsapp/view', [WhatsAppController::class, 'view'])->name('whatsapp.view');
});

// Email routes
Route::middleware('auth')->group(function () {
    Route::get('/emails', [EmailController::class, 'index'])->name('emails.index');
    Route::post('/emails/fetch', [EmailController::class, 'fetch'])->name('emails.fetch');
});
