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
use Illuminate\Http\Request;
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


    public function redirectToGoogle(Request $request)
    {
        // cookie id (tracking / guest)
        $cookieId = $request->cookie('dorra_auth_cookie_id') ?? (string) Str::uuid();

        Cookie::queue(cookie(
            name: 'dorra_auth_cookie_id',
            value: $cookieId,
            minutes: 60 * 24 * 30,
            path: '/',
            domain: config('session.domain'),
            secure: true,
            httpOnly: false,
            sameSite: 'None'
        ));


        $url = $request->query('url', '/Home');

        $nonce = Str::random(32);
        session(['oauth_nonce' => $nonce]);

        $statePayload = [
            'cid'   => $cookieId,
            'url'   => $url,
            'nonce' => $nonce,
            'ts'    => time(),
        ];

        $state = rtrim(
            strtr(base64_encode(json_encode($statePayload, JSON_UNESCAPED_SLASHES)), '+/', '-_'),
            '='
        );

        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => $state])
            ->redirect();
    }



    public function handleGoogleCallback(): array|false
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $email = $googleUser->getEmail();
            $user  = $this->userRepository->findByEmail($email);

            $nameParts = preg_split('/\s+/', trim((string) $googleUser->getName()));
            $firstName = $nameParts[0] ?? '';
            $lastName  = $nameParts[1] ?? '';

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


            $state = $this->decodeState(request('state'));


            $expectedNonce = session('oauth_nonce');
            if ($state && !empty($state['nonce']) && $expectedNonce && $state['nonce'] !== $expectedNonce) {
                throw new \RuntimeException('Invalid oauth state nonce');
            }


            $redirectUrl = $state['url'] ?? (config('services.site_url') . 'Home');

            $cookieValue = request()->cookie('dorra_auth_cookie_id') ?? ($state['cid'] ?? null);

            if ($cookieValue) {
                $this->migrateGuestDataToUser($user, $cookieValue);
            }

            session()->forget('oauth_nonce');

            return [
                'user' => $user,
                'redirectUrl' => $redirectUrl,
            ];

        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return false;
        }
    }
    private function decodeState(?string $state): ?array
    {
        if (!$state) return null;

        try {
            // base64url -> base64
            $b64 = strtr($state, '-_', '+/');
            $pad = strlen($b64) % 4;
            if ($pad) $b64 .= str_repeat('=', 4 - $pad);

            $json = base64_decode($b64, true);
            if ($json === false) return null;

            $data = json_decode($json, true);
            return is_array($data) ? $data : null;
        } catch (\Throwable $e) {
            return null;
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
