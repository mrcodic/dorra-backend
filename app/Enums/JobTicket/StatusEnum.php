<?php

namespace App\Enums\JobTicket;

use App\Helpers\EnumHelpers;

enum StatusEnum : int
{
    use EnumHelpers;

    case PENDING              = 1;
    case PREPRESS_QUEUE       = 2;
    case PREPRESS_IN_PROGRESS = 3;
    case PREPRESS_DONE        = 4;
    case PRINT_QUEUE          = 5;
    case PRINTING             = 6;
    case PRINTED              = 7;
    case FINISH_QUEUE         = 8;
    case FINISHING            = 9;
    case FINISHED             = 10;
    case QC_QUEUE             = 11;
    case QC_PASSING            = 12;
    case QC_PASSED           = 13;
    case PACK_QUEUE           = 14;
    case PACKING               = 15;
    case PACKED              = 16;


    public function label(): string
    {
        return match ($this) {
            self::PENDING              => __('jobticket.status.pending'),
            self::PREPRESS_QUEUE       => __('jobticket.status.prepress_queue'),
            self::PREPRESS_IN_PROGRESS => __('jobticket.status.prepress_in_progress'),
            self::PREPRESS_DONE        => __('jobticket.status.prepress_done'),
            self::PRINT_QUEUE          => __('jobticket.status.print_queue'),
            self::PRINTING             => __('jobticket.status.printing'),
            self::PRINTED              => __('jobticket.status.printed'),
            self::FINISH_QUEUE         => __('jobticket.status.finish_queue'),
            self::FINISHING            => __('jobticket.status.finishing'),
            self::FINISHED             => __('jobticket.status.finished'),
            self::QC_QUEUE             => __('jobticket.status.qc_queue'),
            self::QC_PASSING            => __('jobticket.status.qc_passing'),
            self::QC_PASSED            => __('jobticket.status.qc_passed'),
            self::PACK_QUEUE           => __('jobticket.status.pack_queue'),
            self::PACKING              => __('jobticket.status.packing'),
            self::PACKED               => __('jobticket.status.packed'),
        };
    }



    public static function toArray(): array
    {
        return collect(self::cases())->map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }
}
