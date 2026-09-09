<?php

namespace App\Services\Bundle;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class BundleItemPurchaseFlowResolver
{
    public function resolve(Model $item): array
    {
        if (! $item instanceof Product && ! $item instanceof Category) {
            return [
                'can_purchase' => false,
                'needs_options' => false,
                'requires_custom_design' => false,
                'custom_design_optional' => false,
                'can_add_directly' => false,
                'steps' => [],
            ];
        }

        $hasCustomPrices = (bool) $item->has_custom_prices;
        $hasPrices = $item->prices()->exists();
        $hasSpecifications = $item->specifications()->exists();

        $needsOptions = $hasCustomPrices || $hasPrices || $hasSpecifications;

        $showAddCart = (bool) $item->show_add_cart_btn;
        $showCustomize = (bool) $item->show_customize_design_btn;

        $requiresCustomDesign = ! $showAddCart && $showCustomize;
        $customDesignOptional = $showAddCart && $showCustomize;
        $canPurchase = $showAddCart || $showCustomize;

        $steps = [];

        if ($needsOptions) {
            $steps[] = 'choose_options';
        }

        if ($requiresCustomDesign) {
            $steps[] = 'customize_design';
        } elseif ($customDesignOptional) {
            $steps[] = 'choose_purchase_path';
        } elseif ($showAddCart && $needsOptions) {
            $steps[] = 'include';
        }

        if ($showAddCart && ! $needsOptions && ! $showCustomize) {
            $steps[] = 'ready';
        }

        if ($showCustomize && ! $needsOptions && ! $showAddCart) {
            $steps[] = 'customize_design';
        }

        return [
            'can_purchase' => $canPurchase,
            'has_custom_prices' => $hasCustomPrices,
            'has_prices' => $hasPrices,
            'has_specifications' => $hasSpecifications,
            'needs_options' => $needsOptions,
            'show_add_cart_btn' => $showAddCart,
            'show_customize_design_btn' => $showCustomize,
            'requires_custom_design' => $requiresCustomDesign,
            'custom_design_optional' => $customDesignOptional,
            'can_add_directly' => $showAddCart && ! $needsOptions,
            'steps' => array_values(array_unique($steps)),
        ];
    }
}
