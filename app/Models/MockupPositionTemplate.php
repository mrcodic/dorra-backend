<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    class MockupPositionTemplate extends Model
{
    protected $table = 'mockup_position_template';

    protected $fillable = [
        'mockup_template_id',
        'position_id',
        'template_type',
    ];

    public function mockupTemplate()
    {
        return $this->belongsTo(MockupTemplate::class, 'mockup_template_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
