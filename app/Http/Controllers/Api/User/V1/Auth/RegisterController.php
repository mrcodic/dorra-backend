<?php

namespace App\Http\Controllers\Api\User\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function __construct(public AuthService $authService,public UserRepositoryInterface $userRepository){}

    /**
     * Handle the incoming request.
     * @throws ValidationException
     */
    public function __invoke(RegisterUserRequest $request)
    {
       $user = $this->authService->register($request->validated());
       if (!$user) {
           throw ValidationException::withMessages(['otp'=>'wrong or invalid otp']);
       }
        return Response::api(message: "You are registered successfully",data: UserResource::make($user->load('countryCode')));
    }
}
