<?php
namespace App\Traits;
use App\Mail\OtpMail;
use App\Models\Otp;
use Illuminate\Support\Facades\Mail;

trait OtpTrait
{
    public function generateOtp($identifier): void
    {
        Otp::where('identifier', $identifier)->delete();
        $otp = rand(100000, 999999);
        Otp::create([
            'identifier' => $identifier,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
            ]);
        Mail::to($identifier)->send(new OtpMail($otp));
    }

    public function verifyOtp($identifier, $otp): bool
    {
        $otpRecord = Otp::where([
            ['identifier', $identifier],
            ['otp', $otp],
            ['expires_at', '<' ,now()->addMinutes(5)]
        ])->first();
        if ($otpRecord) {
            $otpRecord->delete();
            return true;
        }
        return false;
    }
}
