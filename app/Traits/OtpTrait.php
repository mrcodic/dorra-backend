<?php

namespace App\Traits;

use App\Enums\OtpTypeEnum;
use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;

trait OtpTrait
{
    public function generateOtp($identifier, $type, $otpable = null)
    {
        Otp::where('identifier', $identifier)
            ->where('type', $type)
            ->delete();
        $otp = rand(100000, 999999);
        Mail::to($identifier)->send(new OtpMail($otp));
        return Otp::create([
            'identifier' => $identifier,
            'otp' => $otp,
            'otpable_id' => $otpable?->id ?? 0,
            'otpable_type' => $otpable ? get_class($otpable) : 'App\Models\User',
            'type' => $type,
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    public function verifyOtp($identifier, $otp, $otpable = null): bool
    {
        $otpRecord = Otp::where([
            ['identifier', $identifier],
            ['otp', $otp],
            ['otpable_id', $otpable?->id ?? 0],
            ['otpable_type', $otpable ? get_class($otpable) : 'App\Models\User'],
            ['expires_at', '>', now()]
        ])->first();
        if ($otpRecord) {
            $otpRecord->delete();
            return true;
        }
        return false;
    }

    public function confirmOtpAndGenerateResetToken($identifier, $otp, $otpable = null)
    {
        $otpRecord = Otp::where([
            ['identifier', $identifier],
            ['otp', $otp],
            ['otpable_id', $otpable?->id ?? 0],
            ['otpable_type', $otpable ? get_class($otpable) : 'App\Models\User'],
            ['expires_at', '>', now()]
        ])->first();
        if (!$otpRecord) {
            return false;
        }
        $otpRecord->delete();
        $resetToken = bin2hex(random_bytes(16));
        Cache::put('password_reset_' . $resetToken, $identifier, now()->addMinutes(5));
        return $resetToken;

    }
    public function verifyResetToken($resetToken)
    {
        return Cache::get('password_reset_' . $resetToken);
    }

    public function deleteResetToken($resetToken)
    {
        Cache::forget('password_reset_' . $resetToken);
    }
    protected function sendOtpResponse(string $email, OtpTypeEnum $type, User $user = null)
    {
        $otp = $this->generateOtp($email, $type, $user);
        $now = now();
        $expiresAt = $otp->expires_at;
        $diff = $now->diff($expiresAt);

        return Response::api(message: "Otp has been sent to your email", data: [
            'current_time' => $now,
            'otp_expires_at' => $expiresAt,
            'remaining_time' => $diff->i . ' minutes and ' . $diff->s . ' seconds',
        ]);
    }

    public function getOtpExpirationTime($identifier,$otp)
    {
        $now = now();
        $expiresAt = $otp->expires_at;
        $diff = $now->diff($expiresAt);
        $otpRecord = Otp::where([
            ['identifier', $identifier],
            ['otp', $otp],
            ['otpable_id', $otpable?->id ?? 0],
            ['otpable_type', $otpable ? get_class($otpable) : 'App\Models\User'],
            ['expires_at', '>', now()]
        ])->first();
        if (!$otpRecord)
        {
            return false;
        }
        return [
            'current_time' => $now,
            'otp_expires_at' => $expiresAt,
            'remaining_time' => $diff->i . ' minutes and ' . $diff->s . ' seconds',
        ];
    }

}
