<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Requests\Vouchers\GetVouchersRequest;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Services\VoucherService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GetVouchersHandler
{
    public function __construct(private readonly VoucherService $voucherService) {}

    public function __invoke(GetVouchersRequest $request): JsonResponse|AnonymousResourceCollection
    {
        try {
            $vouchers = $this->voucherService->getVouchers(
                $request->query('page')?? 1,
                $request->query('paginate')?? 1,
                $request->query('currency'),
                $request->query('serial_number'),
                $request->query('id'), // assuming that "número" is a voucher's id
                $request->query('start_date'),
                $request->query('end_date'),
            );

            return VoucherResource::collection($vouchers);
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->getCode());
        }
    }
}
