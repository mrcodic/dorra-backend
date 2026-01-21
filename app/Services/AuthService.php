<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\UserRegistered;
use App\Repositories\Implementations\SocialAccountRepository;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\DesignRepositoryInterface;
use App\Repositories\Interfaces\GuestRepositoryInterface;
use App\Repositories\Interfaces\ShippingAddressRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Traits\OtpTrait;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AuthService
{
    use OtpTrait;

    public function __construct(
        public UserRepositoryInterface            $userRepository,
        public SocialAccountRepository            $socialAccountRepository,
        public DesignRepositoryInterface          $designRepository,
        public CartRepositoryInterface            $cartRepository,
        public ShippingAddressRepositoryInterface $shippingAddressRepository,
        public GuestRepositoryInterface           $guestRepository,
        public AdminRepositoryInterface           $adminRepository,
    ) {}

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
        $this->migrateGuestDataToUser($user);
        event(new Registered($user));
        return $user;
    }

    public function redirectToGoogle()
    {
        $cookie = request()->cookie('dorra_auth_cookie_id') ?? (string) Str::uuid();
        Cookie::queue(cookie('dorra_auth_cookie_id', $cookie, 60*24*30, '/', config('session.domain'), true, false, 'none'));

        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => base64_encode($cookie)])
            ->redirect();

//        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback(): false|User|null
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = $this->userRepository->findByEmail($googleUser->getEmail());

            $nameParts = explode(' ', $googleUser->getName());
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            $email = $googleUser->getEmail();

            if (!$user) {
                $user = $this->userRepository->create([
                    'first_name'        => $firstName,
                    'last_name'         => $lastName,
                    'email'             => $email,
                    'password'          => str()->random(16),
                    'email_verified_at' => now(),
                ]);
            }

            $this->socialAccountRepository->updateOrCreate(
                ['provider' => 'google', 'provider_id' => $googleUser->getId()],
                [
                    'user_id'    => $user->id,
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $email,
                ]
            );

            $plainTextToken = $user->createToken($user->email, expiresAt: now()->addHours(10))->plainTextToken;
            $user->token = $plainTextToken;

            $stateCookie = request('state') ? base64_decode(request('state')) : null;
            $cookieValue = request()->cookie('dorra_auth_cookie_id') ?? $stateCookie;

            $this->migrateGuestDataToUser($user, $cookieValue);

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


        $this->migrateGuestDataToUser($user);

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



    private function migrateGuestDataToUser(User $user, $cookieGoogle = null): void
    {
        $cookieValue = request()->cookie('cookie_id') ?: $cookieGoogle;

        $guest = $this->guestRepository->query()
            ->where('cookie_value', $cookieValue)
            ->first();

        if (!$guest) {
            return;
        }

        DB::transaction(function () use ($guest, $user) {
            Media::query()
                ->where('model_type', get_class($guest))
                ->where('model_id', $guest->id)
                ->where('collection_name', 'guest_assets')
                ->update([
                    'model_type' => get_class($user),
                    'model_id'   => $user->id,
                    'collection_name' => 'sanctum_assets',
                ]);
            $guestCart      = $guest->cart()->first();
            $guestCartItems = $guestCart
                ? $guestCart->items()->with(['specs'])->get()
                : collect();

            if ($user->cart) {
                $userCart = $user->cart;
                collect($guestCartItems)->each(function ($gItem) use ($userCart) {
                    $newItem = $gItem->replicate();
                    $newItem->cart_id = $userCart->id;
                    $newItem->save();

                    collect($gItem->specs)->each(function ($spec) use ($newItem) {
                        $newSpec = $spec->replicate();
                        $newSpec->cart_item_id = $newItem->id;
                        $newSpec->save();
                    });
                });


                if ($guestCart) {
                    $guestCart->items()->delete();
                    $guestCart->delete();
                }
            } else {

                $this->cartRepository->query()
                    ->whereNull('user_id')
                    ->where('guest_id', $guest->id)
                    ->update(['user_id' => $user->id]);
            }


            $this->designRepository->query()
                ->whereNull('user_id')
                ->where('guest_id', $guest->id)
                ->update(['user_id' => $user->id]);


            $this->shippingAddressRepository->query()
                ->whereNull('user_id')
                ->where('guest_id', $guest->id)
                ->update(['user_id' => $user->id]);
        });
    }
}
