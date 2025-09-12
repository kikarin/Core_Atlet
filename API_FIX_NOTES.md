# API Fix Notes - Target Mapping Mobile

## Masalah yang Diperbaiki

### 1. Error: Call to undefined method App\Models\TargetLatihan::rencanaLatihan()

**Masalah**: Model `TargetLatihan` tidak memiliki relasi `rencanaLatihan()`, tetapi controller mencoba menggunakannya.

**Solusi**: 
- Mengganti query yang menggunakan `whereHas('rencanaLatihan')` dengan query langsung ke tabel pivot `rencana_latihan_target_latihan`
- Menggunakan `DB::table()` untuk mengakses data mapping yang sudah ada

### 2. Perbaikan Method getExistingGroupMappings()

**Sebelum**:
```php
$mappings = TargetLatihan::whereHas('rencanaLatihan', function ($query) use ($programId) {
    $query->where('program_latihan_id', $programId);
})
->whereNotNull('nilai')
->get()
```

**Sesudah**:
```php
// Get all rencana latihan for this program
$rencanaIds = RencanaLatihan::where('program_latihan_id', $programId)->pluck('id');

// Get target mappings from pivot table
$mappings = DB::table('rencana_latihan_target_latihan')
    ->whereIn('rencana_latihan_id', $rencanaIds)
    ->whereNotNull('nilai')
    ->get()
```

### 3. Perbaikan Method bulkUpdateGroupTargets()

**Sebelum**:
```php
TargetLatihan::where('id', $item['target_latihan_id'])
    ->whereHas('rencanaLatihan', function ($query) use ($item) {
        $query->where('id', $item['rencana_latihan_id']);
    })
    ->update([...]);
```

**Sesudah**:
```php
// Check if data exists
$existing = DB::table('rencana_latihan_target_latihan')
    ->where('rencana_latihan_id', $item['rencana_latihan_id'])
    ->where('target_latihan_id', $item['target_latihan_id'])
    ->first();

if ($existing) {
    // Update existing data
    DB::table('rencana_latihan_target_latihan')
        ->where('rencana_latihan_id', $item['rencana_latihan_id'])
        ->where('target_latihan_id', $item['target_latihan_id'])
        ->update([...]);
} else {
    // Insert new data
    DB::table('rencana_latihan_target_latihan')->insert([...]);
}
```

### 4. Filter Target Kelompok

Menambahkan filter untuk hanya mengambil target dengan `jenis_target = 'kelompok'`:

```php
'target_latihan' => $rencana->targetLatihan()
    ->where('jenis_target', 'kelompok')
    ->get()
    ->map(function ($target) {
        return [
            'id' => $target->id,
            'deskripsi' => $target->deskripsi,
            'satuan' => $target->satuan,
            'nilai_target' => $target->nilai_target,
        ];
    }),
```

## Struktur Database

### Tabel rencana_latihan_target_latihan
- `id` (primary key)
- `rencana_latihan_id` (foreign key)
- `target_latihan_id` (foreign key)
- `nilai` (decimal, nullable) - untuk menyimpan nilai target kelompok
- `trend` (enum: naik, stabil, turun) - untuk menyimpan trend target

### Tabel rencana_latihan_peserta_target
- `id` (primary key)
- `rencana_latihan_id` (foreign key)
- `target_latihan_id` (foreign key)
- `peserta_id` (foreign key)
- `peserta_type` (string) - App\Models\Atlet, App\Models\Pelatih, App\Models\TenagaPendukung
- `nilai` (string, nullable) - untuk menyimpan nilai target peserta
- `trend` (enum: naik, stabil, turun) - untuk menyimpan trend target

## API Endpoints yang Diperbaiki

1. **GET** `/api/program-latihan/{programId}/target-mapping/group`
   - Mengambil data mapping target kelompok
   - Hanya menampilkan target dengan jenis 'kelompok'

2. **POST** `/api/program-latihan/{programId}/target-mapping/group/bulk-update`
   - Update nilai target kelompok secara bulk
   - Menggunakan upsert (update atau insert)

3. **GET** `/api/rencana-latihan/{rencanaId}/target-mapping/participant`
   - Mengambil data mapping target peserta individu
   - Support untuk atlet, pelatih, dan tenaga pendukung

4. **POST** `/api/rencana-latihan/{rencanaId}/target-mapping/participant/bulk-update`
   - Update nilai target peserta secara bulk
   - Menggunakan updateOrCreate untuk upsert

## Testing

Untuk test API endpoints:

```bash
# Test get group target mapping
curl -X GET "http://localhost:8000/api/program-latihan/1/target-mapping/group" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"

# Test get participant target mapping
curl -X GET "http://localhost:8000/api/rencana-latihan/1/target-mapping/participant?jenis_peserta=atlet" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

## Status

✅ **Fixed**: Error 500 internal server error pada get target kelompok
✅ **Fixed**: Relasi yang tidak ada pada model TargetLatihan
✅ **Fixed**: Query yang tidak efisien
✅ **Added**: Filter untuk target kelompok
✅ **Added**: Upsert functionality untuk bulk update
