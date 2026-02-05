<?php

use App\Livewire\ViewResource;
use App\Livewire\ListResources;
use App\Livewire\CreateResource;
use App\Livewire\SuccessResource;
use Illuminate\Support\Facades\Route;
use App\Livewire\GenerateTshirtVoucher;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProtectController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ResourceController;

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

Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
Route::get('documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
Route::get('documents/{document}/pdf', [DocumentController::class, 'pdf'])->name('documents.pdf');

Route::get('/vouchers/tshirt/create', GenerateTshirtVoucher::class)->name('vouchers.tshirt.create');
Route::get('/vouchers/tshirt/{code}', [VoucherController::class, 'showTshirt'])->name('vouchers.tshirt.show');

Route::get('/run/schedule/daily', function () {
    Artisan::call('app:send-admin-events');
});

Route::get('logistics/{event_logistic}/survey', \App\Livewire\Logistics\Survey::class)->name('logistics.survey');
Route::get('logistics/{id}', [\App\Http\Controllers\LogisticsController::class, 'show'])->name('logistics.show');
