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
        'device_name',
        'latitude',
        'longitude',
        'occurred_at',
    ];

    protected $casts = [
        'is_suspicious' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'occurred_at' => 'datetime',
    ];

    public $timestamps = false;
}
