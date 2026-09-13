<?php

use App\Http\Controllers\PetugasController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('layouts.app');
});
Route::resource('/petugas', PetugasController::class);
