<?php

use App\Http\Controllers\HomeController;
use App\Livewire\ViewResource;
use App\Livewire\ListResources;
use App\Livewire\CreateResource;
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

Route::get('/', [HomeController::class, 'welcome']);

Route::get('resources', ListResources::class)->name('resources.list');
Route::get('resources/create', CreateResource::class)->name('resources.create');
Route::get('resources/{resource}', ViewResource::class)->name('resources.view');
