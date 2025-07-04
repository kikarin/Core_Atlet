<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MstTingkat extends Model
{
    use HasFactory;

    protected $table = 'mst_tingkat';
    protected $guarded = [];

    // Kolom yang dapat diisi secara massal
    protected $fillable = [
        'nama',
    ];
} 