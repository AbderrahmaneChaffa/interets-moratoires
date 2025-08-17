<?php

use App\Http\Controllers\FacturePdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Livewire\ClientCrud;
use App\Http\Livewire\FactureForm;
use App\Http\Livewire\FactureList;
use App\Http\Livewire\FactureTable;
use Illuminate\Support\Facades\Route;

/*
 * |--------------------------------------------------------------------------
 * | Routes publiques
 * |--------------------------------------------------------------------------
 */

Route::get('/', function () {
    return view('auth/login');
});

/*
 * |--------------------------------------------------------------------------
 * | Routes protégées par login
 * |--------------------------------------------------------------------------
 */

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->middleware('verified')->name('welcome');

    // Gestion du profil utilisateur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tes anciennes routes Livewire
    Route::get('/clients', ClientCrud::class)->name('clients');
    Route::get('/factures/creer', FactureForm::class)->name('factures.creer');
    Route::get('/factures', FactureList::class)->name('factures');
    Route::get('/factures/tableau', FactureTable::class)->name('factures.tableau');

    // Upload du PDF pour une facture
    Route::post('/factures/{facture}/upload-pdf', [FacturePdfController::class, 'upload'])->name('factures.upload_pdf');
    // Envoi d'email pour une facture
    Route::post('/factures/{facture}/send-email', [EmailController::class, 'sendFactureEmail'])->name('factures.send_email');
});

/*
 * |--------------------------------------------------------------------------
 * | Auth routes générées par Breeze
 * |--------------------------------------------------------------------------
 */

require __DIR__ . '/auth.php';
