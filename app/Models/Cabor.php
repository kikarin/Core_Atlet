<?php

namespace App\Models;

use App\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\CaborKategori;

class Cabor extends Model
{
    use HasFactory, Blameable, SoftDeletes, LogsActivity;

    protected $table = 'cabor';
    protected $guarded = [];
    protected $fillable = [
        'nama',
        'deskripsi',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function kategori()
    {
        return $this->hasMany(CaborKategori::class, 'cabor_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Cabor");
    }
} 