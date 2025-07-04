<?php

namespace App\Models;

use App\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\MstKecamatan;
use App\Models\MstDesa;
use App\Models\PelatihSertifikat;
use App\Models\PelatihPrestasi;
use App\Models\PelatihKesehatan;
use App\Models\PelatihDokumen;

class Pelatih extends Model implements HasMedia
{
    use HasFactory, Blameable, SoftDeletes, LogsActivity, InteractsWithMedia;
    
    protected $guarded = [];
    protected $table = "pelatihs";

    protected $fillable = [
        'nik',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'kecamatan_id',
        'kelurahan_id',
        'no_hp',
        'email',
        'is_active',
        'foto',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Pelatih");
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('media')
            ->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(25)
            ->performOnCollections('images');
    }

    // Accessor untuk foto
    public function getFotoAttribute($value)
    {
        $media = $this->getFirstMedia('images');
        if ($media) {
            return $media->getUrl();
        }
        return null;
    }

    // Accessor untuk foto thumbnail
    public function getFotoThumbnailAttribute()
    {
        $media = $this->getFirstMedia('images');
        if ($media) {
            return $media->getUrl('webp');
        }
        return null;
    }

    public function sertifikat()
    {
        return $this->hasMany(PelatihSertifikat::class, 'pelatih_id')
            ->with(['created_by_user', 'updated_by_user']);
    }

    public function prestasi()
    {
        return $this->hasMany(PelatihPrestasi::class, 'pelatih_id')
            ->with(['created_by_user', 'updated_by_user']);
    }

    public function dokumen()
    {
        return $this->hasMany(PelatihDokumen::class, 'pelatih_id')
            ->with(['created_by_user', 'updated_by_user', 'jenis_dokumen']);
    }

    public function kecamatan()
    {
        return $this->belongsTo(MstKecamatan::class, 'kecamatan_id')->select(['id', 'nama']);
    }

    public function kelurahan()
    {
        return $this->belongsTo(MstDesa::class, 'kelurahan_id')->select(['id', 'nama']);
    }

    public function kesehatan()
    {
        return $this->hasOne(PelatihKesehatan::class, 'pelatih_id');
    }
} 