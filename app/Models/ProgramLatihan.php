<?php

namespace App\Models;

use App\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Cabor;
use App\Models\CaborKategori;
use App\Models\TargetLatihan;
use App\Models\RencanaLatihan;

class ProgramLatihan extends Model
{
    use HasFactory, SoftDeletes, Blameable, LogsActivity;

    protected $table = 'program_latihan';
    protected $guarded = [];
    protected $fillable = [
        'cabor_id',
        'nama_program',
        'cabor_kategori_id',
        'periode_mulai',
        'periode_selesai',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function cabor()
    {
        return $this->belongsTo(Cabor::class, 'cabor_id');
    }

    public function caborKategori()
    {
        return $this->belongsTo(CaborKategori::class, 'cabor_kategori_id');
    }

    public function targetLatihan()
    {
        return $this->hasMany(TargetLatihan::class, 'program_latihan_id');
    }

    public function rencanaLatihan()
    {
        return $this->hasMany(RencanaLatihan::class, 'program_latihan_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Program Latihan");
    }
} 