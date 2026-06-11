<?php

namespace App\Observers;

use App\Models\OrderItem;
use Imagick;
use ImagickPixel;
use Illuminate\Support\Str;

class OrderItemObserver
{
    public function created(OrderItem $orderItem): void
    {
        if (! $orderItem->color || ! $orderItem->itemable) {
            return;
        }

        $this->generateColoredPreviews($orderItem);
    }

    public function updated(OrderItem $orderItem): void
    {
        // Regenerate if color changed
        if (! $orderItem->wasChanged('color') || ! $orderItem->itemable) {
            return;
        }

        $orderItem->clearMediaCollection('order_item_previews');
        $this->generateColoredPreviews($orderItem);
    }

    private function generateColoredPreviews(OrderItem $orderItem): void
    {
        $itemable = $orderItem->itemable;
        $types    = $itemable->types ?? null;

        if ($types) {
            foreach ($types as $type) {
                $label      = $type->value->label();
                $collection = match(strtolower($label)) {
                    'back'  => 'back_'.Str::plural(Str::lower(class_basename($itemable))),
                    default => Str::plural(Str::lower(class_basename($itemable))),
                };

                $this->composite($orderItem, $collection, $label);
            }
        } else {
            $collection = Str::plural(Str::lower(class_basename($itemable)));
            $this->composite($orderItem, $collection, 'default');
        }
    }

    private function composite(OrderItem $orderItem, string $collection, string $label): void
    {
        $media = $orderItem->itemable->getFirstMedia($collection);

        if (! $media) {
            return;
        }

        $imagePath = $media->getPath();

        if (! file_exists($imagePath)) {
            return;
        }

        try {
            $png = new Imagick($imagePath);
            $png->setImageFormat('png');

            $width  = $png->getImageWidth();
            $height = $png->getImageHeight();

            $background = new Imagick();
            $background->newImage($width, $height, new ImagickPixel($orderItem->color));
            $background->setImageFormat('png');
            $background->compositeImage($png, Imagick::COMPOSITE_OVER, 0, 0);

            // Save to temp file then add to media library
            $tmpPath = sys_get_temp_dir() . '/order_item_' . $orderItem->id . '_' . $label . '.png';
            $background->writeImage($tmpPath);

            $orderItem
                ->addMedia($tmpPath)
                ->withCustomProperties(['type' => $label])
                ->usingFileName("preview_{$label}.png")
                ->toMediaCollection('order_item_previews');

            $png->destroy();
            $background->destroy();

        } catch (\ImagickException $e) {
            \Log::error("Imagick failed for OrderItem [{$orderItem->id}] type [{$label}]: " . $e->getMessage());
        }
    }
}
