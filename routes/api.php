<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventTicketController;
use App\Http\Controllers\GateController;
use App\Http\Controllers\GateTicketController;
use App\Http\Controllers\LogImportTicketsController;
use App\Http\Controllers\MainEventController;
use App\Http\Controllers\OptionsController;
use App\Http\Controllers\PrintTemplatesController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VenueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/auth', [AuthController::class, 'login'])->middleware(['validate.x.functions', 'cors']);

Route::prefix('main')->middleware(['validate.x.functions', 'cors'])->group(function () {
    Route::get('/', [MainEventController::class, 'index']);
    Route::post('/{id}', [MainEventController::class, 'find']);
    Route::post('/', [MainEventController::class, 'findAdditionalEvent']);
});

Route::prefix('transaction')->middleware(['validate.x.functions', 'cors'])->group(function () {
    Route::post('/place-order', [TransactionsController::class, 'create']);
    Route::post('/order-confirmation', [TransactionsController::class, 'orderConfirmation']);
});

Route::middleware(['auth:sanctum', 'validate.x.functions', 'cors'])->group(function () {
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
    });

    Route::prefix('options')->group(function () {
        Route::get('/', [OptionsController::class, 'index']);
    });

    Route::prefix('auth')->group(function () {
        Route::delete('/', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    Route::prefix('venue')->group(function () {
        Route::get('/', [VenueController::class, 'index']);
        Route::post('/', [VenueController::class, 'create']);
        Route::put('/{id}', [VenueController::class, 'update']);
        Route::delete('/{id}', [VenueController::class, 'delete']);
    });

    Route::prefix('event')->group(function () {
        Route::get('/', [EventController::class, 'index']);
        Route::post('/', [EventController::class, 'create']);
        Route::put('/{id}', [EventController::class, 'update']);
        Route::delete('/{id}', [EventController::class, 'delete']);
        Route::get('/sub-event/{id}', [EventController::class, 'findSubEvent']);
        Route::post('/upload', [EventController::class, 'upload']);
        Route::delete('/upload/{id}', [EventController::class, 'destroy']);
    });

    Route::prefix('event-tickets')->group(function () {
        Route::get('/', [EventTicketController::class, 'index']);
        Route::get('/{id}', [EventTicketController::class, 'find']);
        Route::post('/', [EventTicketController::class, 'create']);
        Route::put('/{id}', [EventTicketController::class, 'update']);
        Route::delete('/{id}', [EventTicketController::class, 'delete']);
    });

    Route::prefix('gates')->group(function () {
        Route::get('/', [GateController::class, 'index']);
        Route::get('/{id}', [GateController::class, 'findByEvent']);
        Route::post('/', [GateController::class, 'create']);
        Route::put('/{id}', [GateController::class, 'update']);
        Route::delete('/{id}', [GateController::class, 'delete']);
    });

    Route::prefix('gate-ticket')->group(function () {
        Route::get('/', [GateTicketController::class, 'index']);
        Route::post('/check-in', [GateTicketController::class, 'checkIn']);
        Route::post('/check-out', [GateTicketController::class, 'checkOut']);
    });

    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index']);
        Route::get('/{id}', [TicketController::class, 'find']);
        Route::post('/', [TicketController::class, 'create']);
        Route::post('/import', [TicketController::class, 'import']);
        Route::put('/{id}', [TicketController::class, 'update']);
        Route::delete('/all', [TicketController::class, 'deleteAll']);
        Route::delete('/{id}', [TicketController::class, 'delete']);
        Route::post('/upload-csv', [TicketController::class, 'upload']);
    });

    Route::prefix('print-templates')->group(function () {
        Route::get('/', [PrintTemplatesController::class, 'index']);
        Route::get('/{id}', [PrintTemplatesController::class, 'find']);
        Route::post('/', [PrintTemplatesController::class, 'create']);
        Route::put('/{id}', [PrintTemplatesController::class, 'update']);
        Route::delete('/{id}', [PrintTemplatesController::class, 'delete']);
    });

    Route::prefix('log-import-tickets')->group(function () {
        Route::get('/', [LogImportTicketsController::class, 'index']);
    });

    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'find']);
        Route::post('/', [UserController::class, 'create']);
        Route::put('/', [UserController::class, 'updateUser']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/', [UserController::class, 'delete']);
    });
});
