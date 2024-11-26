<?php

namespace App\Http\Requests\Vouchers;

use Illuminate\Foundation\Http\FormRequest;

class GetVouchersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start_date'=>['required', 'date'],
            'end_date'=>['required', 'after:start_date'], // end_date must be after start_date
        ];
    }
}
