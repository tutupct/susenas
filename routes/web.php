<?php

use App\Http\Controllers\PetugasController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DetailReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('layouts.app');
});
Route::resource('/petugas', PetugasController::class);

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
Route::get('/reports/petugas/{petugas}/edit', [ReportController::class, 'edit'])->name('reports.edit');
Route::put('/reports/petugas/{petugas}', [ReportController::class, 'update'])->name('reports.update');
Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');

Route::post('/reports/{report}/details', [DetailReportController::class, 'store'])->name('reports.details.store');

Route::put('/reports/{report}/details/{detailReport}', [DetailReportController::class, 'update'])->name('reports.details.update');
