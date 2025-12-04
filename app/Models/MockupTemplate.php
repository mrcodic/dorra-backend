<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MockupTemplate extends Pivot
{

    protected $table = 'mockup_template';


    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $casts = [
        'positions' => 'array'
    ];
    protected $fillable = [
        'mockup_id',
        'template_id',
        'positions'
    ];

    public function mockup()
    {
        return $this->belongsTo(Mockup::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * كل الـ positions المرتبطة بالـ mockup_template row ده
     */
    public function positions()
    {
        return $this->hasMany(MockupPositionTemplate::class, 'mockup_template_id');
    }
}
