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

class Atlet extends Model implements HasMedia
{
    use Blameable;
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    protected $guarded = [];

    protected $table = 'atlets';

    protected $fillable = [
        'nik',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'tanggal_bergabung',
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
            ->setDescriptionForEvent(fn (string $eventName) => 'Atlet');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('media')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
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

    public function atletOrangTua()
    {
        return $this->hasOne(AtletOrangTua::class, 'atlet_id');
    }

    public function sertifikat()
    {
        return $this->hasMany(AtletSertifikat::class, 'atlet_id')
            ->with(['created_by_user', 'updated_by_user']);
    }

    public function prestasi()
    {
        return $this->hasMany(AtletPrestasi::class, 'atlet_id')
            ->with(['created_by_user', 'updated_by_user', 'tingkat']);
    }

    public function dokumen()
    {
        return $this->hasMany(AtletDokumen::class, 'atlet_id')
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
        return $this->hasOne(AtletKesehatan::class, 'atlet_id');
    }

    public function pemeriksaanPeserta()
    {
        return $this->morphMany(PemeriksaanPeserta::class, 'peserta');
    }
}
