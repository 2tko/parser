<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/projects', [App\Http\Controllers\HomeController::class, 'getProjects']);
Route::get('/growth/count-holders-dashboard', [App\Http\Controllers\HomeController::class, 'getGrowthCountHoldersDashboard']);
Route::get('/growth/count-holders-dashboard/compare', [App\Http\Controllers\HomeController::class, 'getGrowthCompareCountHoldersDashboard']);
Route::get('/fall/count-holders-dashboard', [App\Http\Controllers\HomeController::class, 'getFallCountHoldersDashboard']);
Route::get('/growth-percent/count-holders-dashboard', [App\Http\Controllers\HomeController::class, 'getGrowthPercentCountHoldersDashboard']);

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
