<?php

namespace App\DTOs;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class TemplateData
{
    public int $product_id;
    public string $name;
    public int $status;
    public string $design_data;
    public ?UploadedFile $preview_image;
    public ?string $source_design_svg;

    /**
     * Create DTO from request
     */
    public static function fromRequest(Request $request): self
    {
        $data = new self();

        $data->product_id = $request->input('product_id');
        $data->name = $request->input('name');
        $data->status = $request->input('status', 1);
        $data->design_data = $request->input('design_data');
        $data->preview_image = $request->file('preview_image'); // handle file
        $data->source_design_svg = $request->input('source_design_svg');

        return $data;
    }
}
