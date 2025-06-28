<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Implementations\SocialAccountRepository;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\DesignRepositoryInterface;
use App\Repositories\Interfaces\ShippingAddressRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Traits\OtpTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthService
{
    use OtpTrait;

    public function __construct(public UserRepositoryInterface            $userRepository,
                                public SocialAccountRepository            $socialAccountRepository,
                                public DesignRepositoryInterface          $designRepository,
                                public CartRepositoryInterface            $cartRepository,
                                public ShippingAddressRepositoryInterface $shippingAddressRepository,
    )
    {
    }


    public function register($validatedData): false|User
    {
        if (!$this->verifyOtp($validatedData['email'], $validatedData['otp'])) {
            return false;
        }
        $validatedData['email_verified_at'] = now();
        $user = $this->userRepository->create($validatedData);
        if (!empty($validatedData['image'])) {
            handleMediaUploads($validatedData['image'], $user);
        }
        $plainTextToken = $user->createToken($user->email, expiresAt: now()->addHours(5))->plainTextToken;
        $user->token = $plainTextToken;
        return $user->refresh();
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();

    }

    public function handleGoogleCallback(): false|User|null
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = $this->userRepository->findByEmail($googleUser->getEmail());
            if (!$user) {
                $nameParts = explode(' ', $googleUser->getName());
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';
                $user = $this->userRepository->create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $googleUser->getEmail(),
                    'password' => str()->random(16),
                    'email_verified_at' => now(),
                ]);
            }
            $this->socialAccountRepository->updateOrCreate(['user_id' => $user->id, 'provider' => 'google',], [
                'provider_id' => $googleUser->getId(),
            ]);
            Auth::login($user);
//            $plainTextToken = $user->createToken($user->email, expiresAt: now()->addHours(5))->plainTextToken;
//            $user->token = $plainTextToken;

            return $user;

        } catch (Exception $exception) {
            return false;
        }
    }

    public function login($validatedData): ?User
    {
        $user = $this->userRepository->findByEmail($validatedData['email']);

        $expiresAt = ($validatedData['remember'] ?? false)
            ? now()->addDays(30)
            : now()->addHours(5);

        $plainTextToken = $user->createToken($user->email, expiresAt: $expiresAt)->plainTextToken;
        $user->token = $plainTextToken;
        return $user;
    }

    public function logout($request)
    {
        $user = $request->user();

        $oldCookieId = request()->cookie('cookie_id');
        if ($oldCookieId) {
            $this->designRepository->query()
                ->whereNotNull('user_id')
                ->whereCookieId($oldCookieId)
                ->update(['cookie_id' => null]);

            $this->cartRepository->query()
                ->whereNotNull('user_id')
                ->whereCookieId($oldCookieId)
                ->update(['cookie_id' => null]);

            $this->shippingAddressRepository->query()
                ->whereNotNull('user_id')
                ->whereCookieId($oldCookieId)
                ->update(['cookie_id' => null]);
        }

        return $user->currentAccessToken()->delete();
    }


}
