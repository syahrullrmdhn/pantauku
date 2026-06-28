<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'type',
        'value',
        'is_suspicious',
        'device_id',
        'occurred_at',
    ];

    protected $casts = [
        'is_suspicious' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    public $timestamps = false;
}
