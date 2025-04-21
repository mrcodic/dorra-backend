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
        $request->validate(['email' => 'required|email']);
        return $this->sendOtpResponse($request->email, OtpTypeEnum::REGISTRATION);
    }

    public function sendPasswordResetOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $user = User::firstWhere('email', $request->email);
        return $this->sendOtpResponse($request->email, OtpTypeEnum::PASSWORD_RESET, $user);
    }


    public function confirmPasswordResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6'
        ]);
        $resetToken = $this->confirmOtpAndGenerateResetToken($request->email, $request->otp, User::firstWhere('email', $request->email));
        if (!$resetToken) {
            return Response::api(HttpEnum::BAD_REQUEST, message: "Bad Request", errors: [
                'otp' => ["wrong or invalid otp"]
            ]);
        }
        return Response::api(message: "Otp confirmed successfully, you can rest password.", data: [
            'email' => $request->email,
            'reset_token' => $resetToken,
        ]);
    }

    public function getExpirationTimeOtp(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6'
        ]);
        $result = $this->getOtpExpirationTime($validatedData['email'],$validatedData['otp']);
        if (!$result)
        {
            return Response::api(HttpEnum::BAD_REQUEST, message: "Bad Request", errors: [
                'otp' => ["wrong or invalid otp"]
            ]);
        }
        return Response::api(data: $result);

    }
}
