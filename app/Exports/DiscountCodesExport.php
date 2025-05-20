<?php

namespace App\Exports;

use App\Models\DiscountCode;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DiscountCodesExport implements FromCollection, WithHeadings
{
    public function __construct(public $discountCodes = null)
    {
    }

    public function collection()
    {
        $discounts = $this->discountCodes ?? DiscountCode::select([
            'code', 'type', 'max_usage', 'used', 'expired_at'
        ])->get();
        return $discounts->map(function ($discount) {
            return [
                $discount->code,
                $discount->type->label(),
                $discount->max_usage,
                (string) $discount->used ?? '0',
                $discount->expired_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Code',
            'Type',
            'Restrictions',
            'Number Of Usage',
            'Expires Date',
        ];
    }
}
