<?php

namespace App\Http\Controllers\Vouchers;

use App\Exceptions\VoucherException;
use App\Models\Voucher;
use App\Services\VoucherService;
use Exception;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\JsonResponse;

class DeleteVoucherHandler
{
    public function __construct(private readonly VoucherService $voucherService) {}

    public function __invoke($voucher_id): JsonResponse
    {
        try {
            $this->voucherService->deleteVoucher($voucher_id);
            return response()->json([
                'message' => 'Comprobante eliminado exitosamente.'
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'error' => $error->getMessage() // if this function will be called by another backend service. If that's the case then this approach is correct
            ], $error->getCode());              // if this function is called by the frontend, then we should send a general message instead (if error is not an instance of some Custom Error)
        }
    }
}
