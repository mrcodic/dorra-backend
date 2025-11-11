<?php
namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Support\AclNavigator;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();
        $url = app(AclNavigator::class)->firstAllowedUrl($user) ;

        return redirect()->intended('/dashboard');
    }
}
