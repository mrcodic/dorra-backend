<?php

namespace App\Http\Controllers\Api\User\V1\Otp;

use App\Http\Controllers\Controller;
use App\Traits\OtpTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class OtpController extends Controller
{
    use OtpTrait;
    public function sendOtp(Request $request)
    {
        $request->validate(['email'=>'required|email']);
        $this->generateOtp($request->email);
        return Response::api(message: "Otp has been sent to your email");
    }
}
