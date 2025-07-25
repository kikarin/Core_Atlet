<?php

namespace App\Models;

use App\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MstJenisPelatih extends Model
{
    use HasFactory;
    use Blameable;
    use SoftDeletes;
    use LogsActivity;

    protected $table    = 'mst_jenis_pelatih';
    protected $guarded  = [];
    protected $fillable = [
        'nama',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => 'Master Jenis Pelatih');
    }
}
