<?php

namespace App\Http\Controllers\Api\V1\User\Auth;


use App\Enums\HttpEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Traits\OtpTrait;
use Illuminate\Support\Facades\Response;

class ResetPasswordController extends Controller
{
    use OtpTrait;
    /**
     * Handle the incoming request.
     */
    public function __invoke(ResetPasswordRequest $request)
    {
        $resetToken = $this->verifyResetToken($request->reset_token);
        if(!$resetToken){
            return   Response::api(HttpEnum::BAD_REQUEST, message: "Bad Request",errors:[
                'otp'=>["Invalid or expired reset token."],
            ]);
        }
        $user = User::firstWhere('email', $request->email);
        $user->update(['password' =>$request->password]);
        $this->deleteResetToken($request->reset_token);
        return Response::api(message: "Password reset successfully.");

    }
}
