<?php

namespace App\Models;

use App\Models\Mockup;
use App\Enums\Product\StatusEnum;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany, HasManyThrough, MorphMany, MorphToMany};

class Product extends Model implements HasMedia
{
    use InteractsWithMedia, HasTranslations;

    public $translatable = ['name', 'description',];
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'sub_category_id',
        'has_custom_prices',
        'is_free_shipping',
        'base_price',
        'status',
        'has_mockup',
        'show_add_cart_btn',
        'show_customize_design_btn'
    ];

    protected static function booted()
    {

        static::updating(function (Product $product) {
            if ($product->base_price) {
                CartItem::where('cartable_id', $product->id)->get()
                    ->each(function ($item) use ($product) {
                        $data = [
                            'product_price' => $product->base_price,
                            'sub_total'     => ($product->base_price * $item->quantity)
                                + $item->specs_price
                                - $item->cart->discount_amount,
                        ];
                        if ($product->prices->isNotEmpty()) {
                            $data['quantity'] = 1;
                        }

                        $item->update($data);
                    });
            }
        });
    }



    public function price(): Attribute
    {
        return Attribute::get(function ($value) {
            return fmod($value, 1) == 0.0 ? (int)$value : $value;
        });
    }

    /**
     * Accessor for average rating
     */
    protected function rating(): Attribute
    {
        return Attribute::make(
            get: function () {
                $avg = $this->reviews_avg_rating ?? $this->reviews()->avg('rating');
                return is_numeric($avg) ? (int) round($avg) : null;
            }
        );
    }

    /**
     * Scope to filter products by review ratings
     */

    public function scopeWithReviewRating(Builder $query, $ratings): Builder
    {
        $ratings = is_array($ratings) ? $ratings : explode(',', (string) $ratings);
        $ratings = array_values(array_filter(array_map('intval', $ratings)));

        if (empty($ratings)) {
            return $query;
        }

        return $query
            ->withAvg('reviews as avg_rating', 'rating')
            ->havingRaw('ROUND(avg_rating) IN ('.implode(',', $ratings).')');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withDefault(['name' => 'uncategorized']);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')
            ->withPivot([
                'taggable_id',
                'taggable_type',
            ])
            ->withTimestamps();
    }

    public function dimensions()
    {
        return $this->morphToMany(Dimension::class,'dimensionable','dimension_product')->withTimestamps();
    }

    public function orders()
    {
        return $this->morphToMany(Order::class, 'orderable', 'order_items');
    }
    public function confirmedOrders()
    {
        return $this->morphToMany(Order::class,'orderable','order_items')
            ->where('status', \App\Enums\Order\StatusEnum::CONFIRMED);
    }


    public function specifications()
    {
        return $this->morphMany(ProductSpecification::class,'specifiable');
    }

    public function templates()
    {
        return $this->morphToMany(
            Template::class,
            'referenceable',
            'product_template',
            'referenceable_id',
            'template_id'
        )->withTimestamps();
    }


    public function specificationOptions(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProductSpecificationOption::class,
            ProductSpecification::class,
            'product_id',
            'product_specification_id',
            'id',
            'id'
        );
    }

    public function prices(): MorphMany
    {
        return $this->morphMany(ProductPrice::class, 'pricable');
    }


    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class,'reviewable');
    }

    public function users(): MorphToMany
    {
        return $this->morphToMany(User::class, 'savable');
    }

    public function mockups(): HasMany
    {
        return $this->hasMany(Mockup::class);
    }

    public function saves(): MorphMany
    {
        return $this->morphMany(Save::class, 'savable');
    }
    public function designs()
    {
        return $this->morphToMany(Design::class, 'designable', 'designables')
            ->withTimestamps();
    }
    public function carts(): MorphMany
    {
        return $this->morphMany(CartItem::class, 'cartable');
    }
    public function offers(): MorphToMany
    {
        return $this->morphToMany(Offer::class, 'offerable')->withTimestamps();
    }
    /**
     * Computed FK via subquery → usable as a real relation.
     */
    public function lastOffer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'last_offer_id');
    }

    /**
     * Adds `last_offer_id` = the most recent offer (by pivot `offerables.created_at`).
     * Works with eager loading: ->withLastOfferId()->with('lastOffer')
     */
    public function scopeWithLastOfferId(Builder $q): Builder
    {
        $offerables = 'offerables';
        $offers     = 'offers';
        $table      = $this->getTable();
        $class      = static::class;

        return $q->addSelect([
            'last_valid_offer_id' => DB::table($offerables)
                ->join($offers, "$offers.id", '=', "$offerables.offer_id")
                ->select("$offers.id")
                ->whereColumn("$offerables.offerable_id", "$table.id")
                ->where("$offerables.offerable_type", $class)
                ->where(function ($qq) use ($offers) {
                    $qq->whereNull("$offers.end_at")
                        ->orWhere("$offers.end_at", '>=', now());
                })
                ->orderByDesc("$offerables.created_at")
                ->limit(1),
        ]);
    }

    public function getAllProductImages()
    {
        return $this->getMedia('product_extra_images')
            ->merge($this->getMedia('product_main_image'));
    }


    public function getMainImageUrl(): string
    {
        return $this->getFirstMediaUrl('product_main_image');
    }

    public function getExtraImagesUrl(): string
    {
        return $this->getFirstMediaUrl('product_extra_images');
    }

    protected function casts()
    {
        return [
            'status' => StatusEnum::class,
        ];
    }




}
