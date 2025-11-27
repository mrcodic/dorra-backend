<?php

namespace App\Jobs;


use App\Services\SMS\SmsInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, \Illuminate\Bus\Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct(public $users,public string $message)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sms = app(SmsInterface::class);
        $chunkSize = 20;
        $delaySeconds = 2;
        $chunks = $this->users->chunk($chunkSize);
        foreach ($chunks as $index => $chunk) {

            $numbers = $chunk->pluck('phone_number')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
            Log::info("numbers: " . json_encode($numbers));

            if (empty($numbers)) {
                continue;
            }


            $sms->send($numbers, $this->message, ['language' => 1]);
            if ($index < $chunks->count() - 1) {
                sleep($delaySeconds);
            }
        }

    }
}
