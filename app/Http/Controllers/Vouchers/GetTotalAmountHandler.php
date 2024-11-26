<?php

namespace App\Http\Controllers\Vouchers;


use App\Http\Resources\Vouchers\VoucherResource;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class GetTotalAmountHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(Request $request): array
    {
        $vouchers = $this->voucherService->getTotalAmount();
        return $vouchers;
    }
}
