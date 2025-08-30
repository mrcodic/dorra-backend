<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Implementations\SocialAccountRepository;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\DesignRepositoryInterface;
use App\Repositories\Interfaces\GuestRepositoryInterface;
use App\Repositories\Interfaces\ShippingAddressRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Traits\OtpTrait;
use Exception;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthService
{
    use OtpTrait;

    public function __construct(public UserRepositoryInterface            $userRepository,
                                public SocialAccountRepository            $socialAccountRepository,
                                public DesignRepositoryInterface          $designRepository,
                                public CartRepositoryInterface            $cartRepository,
                                public ShippingAddressRepositoryInterface $shippingAddressRepository,
                                public GuestRepositoryInterface           $guestRepository,
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
        return $user;
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
            $nameParts = explode(' ', $googleUser->getName());
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            if (!$user) {
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
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);

            $plainTextToken = $user->createToken($user->email, expiresAt: now()->addHours(10))->plainTextToken;
            $user->token = $plainTextToken;

            return $user;

        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return false;
        }
    }

    public function login($validatedData): ?User
    {
        $user = $this->userRepository->findByEmail($validatedData['email']);
        $user->update(['last_login_at' => now()]);

        $expiresAt = ($validatedData['remember'] ?? false)
            ? now()->addDays(30)
            : now()->addHours(10);

        $plainTextToken = $user->createToken($user->email, expiresAt: $expiresAt)->plainTextToken;
        $user->token = $plainTextToken;
        $cookieValue = request()->cookie('cookie_id');
        if ($cookieValue) {
            $guest = $this->guestRepository->query()
                ->where('cookie_value', $cookieValue)
                ->first();

            if ($guest) {
                if ($user->cart) {
                    $guestCartItems = $guest->cart?->items?->toArray() ?? [];
                    $user->cart?->items()->createMany($guestCartItems);
                    $guest->cart?->items()->delete();
                    $guest->cart?->delete();

                    $this->designRepository->query()
                        ->whereNull('user_id')
                        ->where('guest_id', $guest->id)
                        ->update(['user_id' => $user->id]);

                    $this->shippingAddressRepository->query()
                        ->whereNull('user_id')
                        ->where('guest_id', $guest->id)
                        ->update(['user_id' => $user->id]);
                } else {
                    $this->designRepository->query()
                        ->whereNull('user_id')
                        ->where('guest_id', $guest->id)
                        ->update(['user_id' => $user->id]);

                    $this->shippingAddressRepository->query()
                        ->whereNull('user_id')
                        ->where('guest_id', $guest->id)
                        ->update(['user_id' => $user->id]);

                    $this->cartRepository->query()
                        ->whereNull('user_id')
                        ->where('guest_id', $guest->id)
                        ->update(['user_id' => $user->id]);
                }
            }
        }


        return $user;
    }


    public function logout($request)
    {
        $user = $request->user();
        $cookieValue = request()->cookie('cookie_id');
        $user->currentAccessToken()->delete();
        if ($cookieValue) {
            $guest = $this->guestRepository->query()
                ->where('cookie_value', $cookieValue)
                ->first();

            if ($guest) {
                $this->designRepository->query()
                    ->where('user_id', $user->id)
                    ->update(['guest_id' => null]);

                $this->shippingAddressRepository->query()
                    ->where('user_id', $user->id)
                    ->update(['guest_id' => null]);

                $this->cartRepository->query()
                    ->where('user_id', $user->id)
                    ->update(['guest_id' => null]);
                $guest->delete();
            }

        }

    }


}
