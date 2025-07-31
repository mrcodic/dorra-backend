<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Typeable extends MorphPivot
{
protected $table = 'typeables';

protected $fillable = [
'typeable_id',
'typeable_type',
'type_id',
];

public $incrementing = true;
public $timestamps = true;

protected $casts = [
'typeable_id' => 'string',
];
}
