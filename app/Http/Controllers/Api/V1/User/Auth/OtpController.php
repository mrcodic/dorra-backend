<?php

namespace App\Http\Controllers\Api\V1\User\Auth;


use App\Enums\HttpEnum;
use App\Enums\OtpTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\OtpTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class OtpController extends Controller
{
    use OtpTrait;
    public function sendRegistrationOtp(Request $request)
    {
        $request->validate(['email'=>'required|email']);
        $otp = $this->generateOtp($request->email,OtpTypeEnum::REGISTRATION);
        return Response::api(message: "Otp has been sent to your email",data: [
            'otp_expires_at' => $otp->expires_at,
        ]);
    }
    public function sendPasswordResetOtp(Request $request)
    {
        $request->validate(['email'=>'required|email|exists:users,email']);
        $otp = $this->generateOtp($request->email,OtpTypeEnum::PASSWORD_RESET,User::firstWhere('email',$request->email));
        return Response::api(message: "Otp has been sent to your email",data: [
            'otp_expires_at' => $otp->expires_at,
        ]);
    }
    public function confirmPasswordResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6'
        ]);
        $resetToken = $this->confirmOtpAndGenerateResetToken($request->email,$request->otp,User::firstWhere('email',$request->email));
        if (!$resetToken) {
            return   Response::api(HttpEnum::BAD_REQUEST, message: "Bad Request",errors:[
                'otp'=>["wrong or invalid otp"]
            ]);
        }
        dd($resetToken);
        return Response::api(message: "Otp confirmed successfully, you can rest password.",data:[
            'reset_token' => $resetToken,
            'email' => $request->email,
        ]);
    }
}
