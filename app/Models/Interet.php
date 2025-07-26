<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interet extends Model
{
    use HasFactory;

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }
} 