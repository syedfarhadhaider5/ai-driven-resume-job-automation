<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

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

Route::get('/', [FileUploadController::class, 'index']);
Route::post('/upload', [FileUploadController::class, 'upload'])->name('upload');
Route::get('/resume/{id}', [\App\Http\Controllers\CvDetailExtractController::class, 'ShowCvList']);
Route::put('/resume/{id}', [\App\Http\Controllers\CvDetailExtractController::class, 'CvListUpdate']);
Route::delete('/resume/{id}', [\App\Http\Controllers\CvDetailExtractController::class, 'CvDataUpdate']);
Route::middleware('cors')->group(function () {
    // Your routes here...
    Route::get('/fill-form/{id}', [\App\Http\Controllers\FillFormController::class, 'index'])->name('fill-form');
});
Route::get('/proxy', [\App\Http\Controllers\FillFormController::class, 'loadProxy']);
