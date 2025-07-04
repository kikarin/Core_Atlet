<?php

namespace App\Models;

use App\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\MstTingkat;

class PelatihPrestasi extends Model
{
    use HasFactory, Blameable, SoftDeletes, LogsActivity;

    protected $table = 'pelatih_prestasi';
    protected $guarded = [];

    protected $fillable = [
        'pelatih_id',
        'nama_event',
        'tingkat_id',
        'tanggal',
        'peringkat',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Pelatih Prestasi");
    }

    public function pelatih()
    {
        return $this->belongsTo(Pelatih::class, 'pelatih_id');
    }

    public function tingkat()
    {
        return $this->belongsTo(MstTingkat::class, 'tingkat_id');
    }
} 