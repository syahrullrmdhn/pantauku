<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlacklistDomain extends Model
{
    protected $fillable = [
        'domain',
        'notes',
    ];

    public $timestamps = false;
}
