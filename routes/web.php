<?php

use App\Http\Controllers\PetugasController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('layouts.app');
});
Route::resource('/petugas', PetugasController::class);
Route::resource('/reports', ReportController::class);
