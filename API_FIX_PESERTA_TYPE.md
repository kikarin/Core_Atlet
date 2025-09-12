# API Fix - Missing peserta_type Field

## Masalah

Error SQL terjadi karena field `peserta_type` tidak diisi saat insert ke tabel `rencana_latihan_peserta_target`:

```
SQLSTATE[HY000]: General error: 1364 Field 'peserta_type' doesn't have a default value
```

## Penyebab

Tabel `rencana_latihan_peserta_target` memiliki field `peserta_type` yang wajib diisi, tetapi method `bulkUpdateParticipantTargets` tidak mengisi field tersebut saat melakukan `updateOrCreate`.

## Solusi

### 1. Tambahkan Field peserta_type ke updateOrCreate

**Sebelum**:
```php
RencanaLatihanPesertaTarget::updateOrCreate(
    [
        'rencana_latihan_id' => $rencanaId,
        'peserta_id' => $item['peserta_id'],
        'target_latihan_id' => $item['target_latihan_id'],
    ],
    [
        'nilai' => $item['nilai'],
        'trend' => $item['trend'],
        'updated_by' => Auth::id(),
    ]
);
```

**Sesudah**:
```php
// Determine peserta_type based on the participant
$pesertaType = $this->getPesertaType($rencanaId, $item['peserta_id']);

RencanaLatihanPesertaTarget::updateOrCreate(
    [
        'rencana_latihan_id' => $rencanaId,
        'peserta_id' => $item['peserta_id'],
        'target_latihan_id' => $item['target_latihan_id'],
        'peserta_type' => $pesertaType, // ✅ Added
    ],
    [
        'nilai' => $item['nilai'],
        'trend' => $item['trend'],
        'updated_by' => Auth::id(),
    ]
);
```

### 2. Tambahkan Method getPesertaType()

Method untuk menentukan jenis peserta berdasarkan rencana latihan dan ID peserta:

```php
private function getPesertaType($rencanaId, $pesertaId)
{
    $rencana = RencanaLatihan::find($rencanaId);
    
    if (!$rencana) {
        return 'App\\Models\\Atlet'; // Default fallback
    }

    // Check if peserta is an atlet
    if ($rencana->atlets()->where('atlets.id', $pesertaId)->exists()) {
        return 'App\\Models\\Atlet';
    }

    // Check if peserta is a pelatih
    if ($rencana->pelatihs()->where('pelatihs.id', $pesertaId)->exists()) {
        return 'App\\Models\\Pelatih';
    }

    // Check if peserta is a tenaga pendukung
    if ($rencana->tenagaPendukung()->where('tenaga_pendukungs.id', $pesertaId)->exists()) {
        return 'App\\Models\\TenagaPendukung';
    }

    // Default fallback
    return 'App\\Models\\Atlet';
}
```

## Struktur Tabel

### rencana_latihan_peserta_target
```sql
CREATE TABLE `rencana_latihan_peserta_target` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rencana_latihan_id` bigint unsigned NOT NULL,
  `target_latihan_id` bigint unsigned NOT NULL,
  `peserta_id` bigint unsigned NOT NULL,
  `peserta_type` varchar(255) NOT NULL, -- ✅ Required field
  `nilai` varchar(255) DEFAULT NULL,
  `trend` enum('naik','stabil','turun') NOT NULL DEFAULT 'stabil',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_peserta_target` (`rencana_latihan_id`,`target_latihan_id`,`peserta_id`,`peserta_type`)
);
```

## Jenis Peserta yang Didukung

1. **Atlet**: `App\Models\Atlet`
2. **Pelatih**: `App\Models\Pelatih`
3. **Tenaga Pendukung**: `App\Models\TenagaPendukung`

## Testing

Setelah perbaikan, API endpoint dapat diakses tanpa error SQL:

```bash
# Test participant target mapping
curl -X POST "http://localhost:8000/api/rencana-latihan/1/target-mapping/participant/bulk-update" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "data": [
      {
        "peserta_id": 5,
        "target_latihan_id": 1,
        "nilai": "12",
        "trend": "stabil"
      }
    ]
  }'
```

## Status

✅ **Fixed**: Missing peserta_type field error
✅ **Added**: Method getPesertaType() untuk auto-detect jenis peserta
✅ **Added**: Field peserta_type ke updateOrCreate
✅ **Tested**: API endpoint berfungsi normal

## Catatan

- Field `peserta_type` wajib diisi untuk setiap record
- Method `getPesertaType()` akan auto-detect jenis peserta berdasarkan relasi
- Fallback ke `App\Models\Atlet` jika tidak dapat menentukan jenis peserta
- Unique constraint memastikan tidak ada duplikasi data
