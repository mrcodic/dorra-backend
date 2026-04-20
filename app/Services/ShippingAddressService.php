<?php

namespace App\Services;


use App\Repositories\Interfaces\GuestRepositoryInterface;
use App\Repositories\Interfaces\ShippingAddressRepositoryInterface;
use Illuminate\Validation\ValidationException;

class ShippingAddressService extends BaseService
{

    public function __construct(ShippingAddressRepositoryInterface $repository, public GuestRepositoryInterface $guestRepository)
    {
        parent::__construct($repository);

    }


    public function getUserOrGuestShippingAddresses()
    {
        $user = request()->user('sanctum');
        $cookieValue = request()->cookie('cookie_id');
        $guestId = null;

        if (!$user && $cookieValue) {
            $guest = $this->guestRepository->query()
                ->where('cookie_value', $cookieValue)
                ->first();

            $guestId = $guest?->id;
        }

        if (!$user && !$guestId) {
            throw ValidationException::withMessages([
                'authorization' => ['Either a logged-in user or a valid guest cookie must be provided.'],
            ]);
        }

        return $this->repository->query()
            ->when($user, fn($q) => $q->whereBelongsTo($user))
            ->when(!$user && $guestId, fn($q) => $q->where('guest_id', $guestId))
            ->get();
    }

}
