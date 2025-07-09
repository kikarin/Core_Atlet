<?php

namespace App\Models;

use App\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CaborKategori extends Model
{
    use HasFactory, Blameable, SoftDeletes, LogsActivity;

    protected $table = 'cabor_kategori';
    protected $guarded = [];
    protected $fillable = [
        'cabor_id',
        'nama',
        'deskripsi',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function cabor()
    {
        return $this->belongsTo(Cabor::class, 'cabor_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Cabor Kategori");
    }
} 