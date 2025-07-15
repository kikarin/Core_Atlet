<?php

namespace App\Models;

use App\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CaborKategoriTenagaPendukung extends Model
{
    use HasFactory, Blameable, SoftDeletes, LogsActivity;

    protected $table = 'cabor_kategori_tenaga_pendukung';
    protected $guarded = [];
    protected $fillable = [
        'cabor_id',
        'cabor_kategori_id',
        'tenaga_pendukung_id',
        'jenis_tenaga_pendukung_id',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Cabor Kategori Tenaga Pendukung");
    }

    public function cabor()
    {
        return $this->belongsTo(Cabor::class, 'cabor_id');
    }

    public function caborKategori()
    {
        return $this->belongsTo(CaborKategori::class, 'cabor_kategori_id');
    }

    public function tenagaPendukung()
    {
        return $this->belongsTo(TenagaPendukung::class, 'tenaga_pendukung_id');
    }

    public function jenisTenagaPendukung()
    {
        return $this->belongsTo(MstJenisTenagaPendukung::class, 'jenis_tenaga_pendukung_id');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function deleted_by_user()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
} 