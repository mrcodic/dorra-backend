<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MockupTemplate extends Pivot
{

    protected $table = 'mockup_template';


    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $casts = [
        'positions' => 'array',
        'colors' => 'array'
    ];
    protected $fillable = [
        'mockup_id',
        'template_id',
        'positions',
        'colors'
    ];
    protected $attributes = [
          'colors' => '[]',
    ];

    public function mockup()
    {
        return $this->belongsTo(Mockup::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
