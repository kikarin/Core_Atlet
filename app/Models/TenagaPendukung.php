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

class TenagaPendukung extends Model implements HasMedia
{
    use HasFactory;
    use Blameable;
    use SoftDeletes;
    use LogsActivity;
    use InteractsWithMedia;

    protected $guarded = [];
    protected $table   = 'tenaga_pendukungs';

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
            ->setDescriptionForEvent(fn (string $eventName) => 'Tenaga Pendukung');
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

    public function kecamatan()
    {
        return $this->belongsTo(MstKecamatan::class, 'kecamatan_id')->select(['id', 'nama']);
    }

    public function kelurahan()
    {
        return $this->belongsTo(MstDesa::class, 'kelurahan_id')->select(['id', 'nama']);
    }

    public function sertifikat()
    {
        return $this->hasMany(TenagaPendukungSertifikat::class, 'tenaga_pendukung_id');
    }

    public function prestasi()
    {
        return $this->hasMany(TenagaPendukungPrestasi::class, 'tenaga_pendukung_id')
            ->with(['created_by_user', 'updated_by_user', 'tingkat']);
    }

    public function kesehatan()
    {
        return $this->hasOne(TenagaPendukungKesehatan::class, 'tenaga_pendukung_id');
    }

    public function dokumen()
    {
        return $this->hasMany(TenagaPendukungDokumen::class, 'tenaga_pendukung_id')
            ->with(['created_by_user', 'updated_by_user', 'jenis_dokumen']);
    }
}
