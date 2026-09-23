<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Favourite;
use App\Models\FavouriteItem;
use App\Models\Guest;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Repositories\Interfaces\FavouriteItemRepositoryInterface;
use App\Repositories\Interfaces\FavouriteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class FavouriteService extends BaseService
{
    public function __construct(
        FavouriteRepositoryInterface $repository,
        public FavouriteItemRepositoryInterface $favouriteItemRepository,
    ) {
        parent::__construct($repository);
    }

    public function getCurrentFavourite(bool $create = true): ?Favourite
    {
        $owner = getAuthOrGuest();

        if (! $owner instanceof User && ! $owner instanceof Guest) {
            return null;
        }

        $attributes = [
            'user_id' => $owner instanceof User ? $owner->id : null,
            'guest_id' => $owner instanceof Guest ? $owner->id : null,
        ];

        $query = $this->repository->query()
            ->when($owner instanceof User, fn ($q) => $q->where('user_id', $owner->id))
            ->when($owner instanceof Guest, fn ($q) => $q->where('guest_id', $owner->id));

        if (! $create) {
            return $query->first();
        }

        return $this->repository->query()->firstOrCreate($attributes);
    }

    public function items(): Collection
    {
        $favourite = $this->getCurrentFavourite(create: false);

        if (! $favourite) {
            return new Collection();
        }

        return $favourite->items()
            ->with(['favouritable', 'contextable'])
            ->latest()
            ->get();
    }

    public function toggle(array $validatedData): array
    {
        return $this->handleTransaction(function () use ($validatedData) {
            $favourite = $this->getCurrentFavourite();

            if (! $favourite) {
                throw ValidationException::withMessages([
                    'favourite' => ['Unable to resolve favourite owner.'],
                ]);
            }

            $favourite = $this->repository->query()
                ->whereKey($favourite->id)
                ->lockForUpdate()
                ->firstOrFail();

            [$favouritable, $contextable] = $this->resolveModels($validatedData);
            $this->validateAttachment($favouritable, $contextable);

            $identity = [
                'favourite_id' => $favourite->id,
                'favouritable_type' => $favouritable->getMorphClass(),
                'favouritable_id' => (string) $favouritable->getKey(),
                'contextable_type' => $contextable->getMorphClass(),
                'contextable_id' => (string) $contextable->getKey(),
            ];

            $existing = $this->favouriteItemRepository->query()
                ->where($identity)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->delete();

                return [
                    'is_favourite' => false,
                    'item' => null,
                    'favouritable_type' => $validatedData['favouritable_type'],
                    'favouritable_id' => (string) $validatedData['favouritable_id'],
                    'contextable_type' => $validatedData['contextable_type'],
                    'contextable_id' => (string) $validatedData['contextable_id'],
                ];
            }

            $item = $this->favouriteItemRepository->query()->create($identity);
            $item->load(['favouritable', 'contextable']);

            return [
                'is_favourite' => true,
                'item' => $item,
                'favouritable_type' => $validatedData['favouritable_type'],
                'favouritable_id' => (string) $validatedData['favouritable_id'],
                'contextable_type' => $validatedData['contextable_type'],
                'contextable_id' => (string) $validatedData['contextable_id'],
            ];
        });
    }

    public function mergeGuestIntoUser(Guest $guest, User $user): void
    {
        $guestFavourite = $this->repository->query()
            ->where('guest_id', $guest->id)
            ->first();

        if (! $guestFavourite) {
            return;
        }

        $userFavourite = $this->repository->query()
            ->where('user_id', $user->id)
            ->first();

        if (! $userFavourite) {
            $guestFavourite->update([
                'user_id' => $user->id,
                'guest_id' => null,
            ]);

            return;
        }

        $guestFavourite->items()
            ->orderBy('id')
            ->chunkById(100, function ($items) use ($userFavourite) {
                foreach ($items as $item) {
                    $this->favouriteItemRepository->query()->firstOrCreate([
                        'favourite_id' => $userFavourite->id,
                        'favouritable_type' => $item->favouritable_type,
                        'favouritable_id' => (string) $item->favouritable_id,
                        'contextable_type' => $item->contextable_type,
                        'contextable_id' => (string) $item->contextable_id,
                    ]);
                }
            });

        $guestFavourite->delete();
    }

    private function resolveModels(array $validatedData): array
    {
        $favouritable = match ($validatedData['favouritable_type']) {
            'template' => Template::query()->findOrFail($validatedData['favouritable_id']),
        };

        $contextable = match ($validatedData['contextable_type']) {
            'product' => Product::query()->findOrFail($validatedData['contextable_id']),
            'category' => Category::query()->findOrFail($validatedData['contextable_id']),
        };

        return [$favouritable, $contextable];
    }

    private function validateAttachment(Model $favouritable, Model $contextable): void
    {
        if (! $favouritable instanceof Template) {
            return;
        }

        $isAttached = match (true) {
            $contextable instanceof Product => $favouritable->products()
                ->where('products.id', $contextable->getKey())
                ->exists(),
            $contextable instanceof Category => $favouritable->categories()
                ->where('categories.id', $contextable->getKey())
                ->exists(),
            default => false,
        };

        if (! $isAttached) {
            throw ValidationException::withMessages([
                'contextable_id' => ['The selected template is not attached to this product/category.'],
            ]);
        }
    }
}
