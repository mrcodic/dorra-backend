<?php

namespace App\Services;


use App\Repositories\Interfaces\ShippingAddressRepositoryInterface;
use Illuminate\Validation\ValidationException;

class ShippingAddressService extends BaseService
{

    public function __construct(ShippingAddressRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function getUserOrGuestShippingAddresses()
    {
        $cookie = request()->cookie('cookie_id');
        $user = request()->user('sanctum');
        if (!$user && !$cookie) {
            throw ValidationException::withMessages([
                'authorization' => ['Either a logged-in user or a valid cookie must be provided.'],
            ]);
        }
        return $this->repository->query()->where(function ($query) use ($cookie, $user) {
            if ($user) {
                $query->whereBelongsTo($user);
            }
            elseif ($cookie) {
                $query->where('cookie_id', $cookie);
            }
        })->get();

    }

}
