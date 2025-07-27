<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\ClientCrud;
use App\Http\Livewire\FactureForm;
use App\Http\Livewire\FactureList;
use App\Http\Livewire\FactureTable;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/clients', ClientCrud::class)->name('clients');
Route::get('/factures/creer', FactureForm::class)->name('factures.creer');
Route::get('/factures', FactureList::class)->name('factures');
Route::get('/factures/tableau', FactureTable::class)->name('factures.tableau');
