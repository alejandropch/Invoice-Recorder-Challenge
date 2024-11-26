<?php

use App\Http\Controllers\Vouchers\DeleteVoucherHandler;
use App\Http\Controllers\Vouchers\GetTotalAmountHandler;
use App\Http\Controllers\Vouchers\GetVouchersHandler;
use App\Http\Controllers\Vouchers\StoreVouchersHandler;
use Illuminate\Support\Facades\Route;

Route::prefix('vouchers')->group(
    function () {
        Route::get('/', GetVouchersHandler::class);
        Route::get('/amount', GetTotalAmountHandler::class);
        Route::delete('/{voucher_id}', DeleteVoucherHandler::class);
        Route::post('/', StoreVouchersHandler::class);
    }
);
