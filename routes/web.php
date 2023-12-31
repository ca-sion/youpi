<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EventController;
use App\Livewire\ViewResource;
use App\Livewire\ListResources;
use App\Livewire\CreateResource;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProtectController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\TrainersController;
use App\Livewire\SuccessResource;
use Illuminate\Support\Facades\Artisan;

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

Route::get('/', [HomeController::class, 'welcome'])->name('welcome');
Route::get('/program', [HomeController::class, 'program'])->name('program');

Route::get('protect', [ProtectController::class, 'password'])->name('protect');
Route::post('protect/check', [ProtectController::class, 'check'])->name('protect.check');

Route::get('resources', ListResources::class)->name('resources.list')->middleware('protect');
Route::get('resources/create', CreateResource::class)->name('resources.create')->middleware('protect');
Route::get('resources/{resource}', ViewResource::class)->name('resources.view');
Route::get('resources/{resource}/success', SuccessResource::class)->name('resources.success');
Route::get('resources/{resource}/share', [ResourceController::class, 'share'])->name('resources.share');

Route::get('events', [EventController::class, 'index'])->name('events.index');
Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('events/{event}/pdf', [EventController::class, 'pdf'])->name('events.pdf');
Route::get('events/{event}/text', [EventController::class, 'text'])->name('events.text');
Route::get('events/{event}/trainers-presences', [EventController::class, 'trainersPresences'])->name('events.trainers.presences');

Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
Route::get('documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
Route::get('documents/{document}/pdf', [DocumentController::class, 'pdf'])->name('documents.pdf');

Route::get('trainers/presences', [TrainersController::class, 'presences'])->name('trainers.presences');

Route::get('/run/schedule/daily', function () {
    Artisan::call('app:send-admin-events');
});
