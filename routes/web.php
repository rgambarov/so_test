<?php

use App\Http\Controllers\LeadImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LeadImportController::class, 'index'])->name('imports.index');
Route::post('/imports/preview', [LeadImportController::class, 'preview'])->name('imports.preview');
Route::post('/imports', [LeadImportController::class, 'import'])->name('imports.store');
