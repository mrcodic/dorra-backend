<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Implementations\SocialAccountRepository;
use App\Repositories\Interfaces\DesignRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Traits\OtpTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthService
{
    use OtpTrait;

    public function __construct(public UserRepositoryInterface   $userRepository,
                                public SocialAccountRepository   $socialAccountRepository,
                                public DesignRepositoryInterface $designRepository)
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

        $expiresAt = ($validatedData['remember'] ?? false) ? now()->addDays(30) : now()->addHours(5);
        $plainTextToken = $user->createToken($user->email, expiresAt: $expiresAt)->plainTextToken;
        $user->token = $plainTextToken;

        $oldCookieId = request()->cookie('cookie_id');

        // Get designs with no user assigned but have matching cookie_id
        $cookieQuery = $this->designRepository->query()
            ->whereNull('user_id')
            ->whereCookieId($oldCookieId);

        if ($cookieQuery->exists()) {
            $cookieQuery->update(['user_id' => $user->id]);
        }

        // Get designs with same cookie_id that already have a user assigned
        $cookieUserQuery = $this->designRepository->query()
            ->whereNotNull('user_id')
            ->whereCookieId($oldCookieId);

        if ($cookieUserQuery->exists()) {
            // Generate new cookie ID
            $newCookie = (string) Str::uuid();

            // Queue it in response
            cookie()->queue(cookie('cookie_id', $newCookie, 60 * 24 * 30)); // 30 days

            // Fetch rows to copy
            $designsToCopy = $cookieUserQuery->get();

            // Duplicate each row with the new user and new cookie_id
            foreach ($designsToCopy as $design) {
                $newDesign = $design->replicate(); // Copy all attributes
                $newDesign->user_id = $user->id;
                $newDesign->cookie_id = $newCookie;
                $newDesign->save();
            }
        }

        return $user;
    }



}
