<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DesignVersion extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable =[
        'design_id',
        'design_data',
        'design_back_data',
        'version',
    ];

    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }

}
