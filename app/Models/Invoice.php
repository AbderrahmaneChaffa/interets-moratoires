<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{

    protected $fillable = [
        'releve_id',
        'path',
        'amount_ht',
        'amount_ttc',
        'status',
        'paid_at',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'paid_at' => 'datetime',
    ];
}
