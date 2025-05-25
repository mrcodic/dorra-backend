<?php

namespace App\DTOs;

class TemplateData
{
    public function __construct(
        public readonly int $product_id,
        public readonly string $name,
        public readonly int $status,
        public readonly array $design_data,
        public readonly ?string $preview_image,
        public readonly ?string $source_design_svg,
    ) {}

    /**
     * Create DTO from validated request data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            product_id: $data['product_id'],
            name: $data['name'],
            status: $data['status'] ?? 1,
            design_data: $data['design_data'],
            preview_image: $data['preview_image'] ?? null,
            source_design_svg: $data['source_design_svg'] ?? null,
        );
    }
}
