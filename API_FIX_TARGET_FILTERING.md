# API Fix - Target Filtering untuk Mobile

## Masalah yang Diperbaiki

### 1. **Target Kelompok Muncul di Pemetaan Peserta**
- **Masalah**: Semua target (individu dan kelompok) ditampilkan di pemetaan peserta
- **Solusi**: Filter target berdasarkan `jenis_target = 'individu'`

### 2. **Target Individu Tidak Memfilter Berdasarkan Peruntukan**
- **Masalah**: Semua target individu ditampilkan untuk semua jenis peserta
- **Solusi**: Filter target berdasarkan `peruntukan` sesuai dengan `jenis_peserta`

### 3. **Missing Fields dalam Response**
- **Masalah**: Response tidak mengembalikan `jenis_target` dan `peruntukan`
- **Solusi**: Tambahkan field tersebut ke response

## Perubahan yang Dibuat

### 1. **Update Method `getParticipantTargetMapping`**

**Sebelum**:
```php
// Get target latihan for this rencana
$targets = $rencana->targetLatihan->map(function ($target) {
    return [
        'id' => $target->id,
        'deskripsi' => $target->deskripsi,
        'satuan' => $target->satuan,
        'nilai_target' => $target->nilai_target,
    ];
});
```

**Sesudah**:
```php
// Get target latihan for this rencana with proper filtering
$peruntukan = $this->mapJenisPesertaToPeruntukan($jenisPeserta);

$targets = $rencana->targetLatihan()
    ->where('jenis_target', 'individu') // Filter hanya target individu
    ->where('peruntukan', $peruntukan) // Filter berdasarkan peruntukan
    ->get()
    ->map(function ($target) {
        return [
            'id' => $target->id,
            'deskripsi' => $target->deskripsi,
            'satuan' => $target->satuan,
            'nilai_target' => $target->nilai_target,
            'jenis_target' => $target->jenis_target, // ✅ Added
            'peruntukan' => $target->peruntukan,     // ✅ Added
        ];
    });
```

### 2. **Tambahkan Method `mapJenisPesertaToPeruntukan`**

```php
/**
 * Map jenis peserta to peruntukan
 */
private function mapJenisPesertaToPeruntukan($jenisPeserta)
{
    return match ($jenisPeserta) {
        'atlet' => 'atlet',
        'pelatih' => 'pelatih',
        'tenaga-pendukung' => 'tenaga-pendukung',
        default => 'atlet',
    };
}
```

### 3. **Update Method `getExistingParticipantMappings`**

**Sebelum**:
```php
$mappings = RencanaLatihanPesertaTarget::where('rencana_latihan_id', $rencanaId)
    ->get()
    ->groupBy('peserta_id')
    // ...
```

**Sesudah**:
```php
// Map jenis peserta to model class
$pesertaType = match ($jenisPeserta) {
    'atlet' => 'App\\Models\\Atlet',
    'pelatih' => 'App\\Models\\Pelatih',
    'tenaga-pendukung' => 'App\\Models\\TenagaPendukung',
    default => 'App\\Models\\Atlet',
};

$mappings = RencanaLatihanPesertaTarget::where('rencana_latihan_id', $rencanaId)
    ->where('peserta_type', $pesertaType) // ✅ Added filter
    ->get()
    ->groupBy('peserta_id')
    // ...
```

## Response Format yang Diperbaiki

### **Sebelum**:
```json
{
  "status": "success",
  "message": "Data mapping target peserta berhasil diambil",
  "data": {
    "participants": [...],
    "targets": [
      {
        "id": 1,
        "deskripsi": "Kecepatan Lari 100m",
        "satuan": "detik",
        "nilai_target": "12.5"
      }
    ],
    "mappings": {...}
  }
}
```

### **Sesudah**:
```json
{
  "status": "success",
  "message": "Data mapping target peserta berhasil diambil",
  "data": {
    "participants": [...],
    "targets": [
      {
        "id": 1,
        "deskripsi": "Kecepatan Lari 100m",
        "satuan": "detik",
        "nilai_target": "12.5",
        "jenis_target": "individu",  // ✅ Added
        "peruntukan": "atlet"        // ✅ Added
      }
    ],
    "mappings": {...}
  }
}
```

## Filtering Logic

### **1. Filter Jenis Target**
- Hanya menampilkan target dengan `jenis_target = 'individu'`
- Target kelompok (`jenis_target = 'kelompok'`) tidak akan muncul

### **2. Filter Peruntukan**
- **Atlet**: Hanya melihat target dengan `peruntukan = 'atlet'`
- **Pelatih**: Hanya melihat target dengan `peruntukan = 'pelatih'`
- **Tenaga Pendukung**: Hanya melihat target dengan `peruntukan = 'tenaga-pendukung'`

## Database Query yang Diperbaiki

```sql
SELECT 
  tl.*
FROM target_latihan tl
JOIN rencana_latihan_target_latihan rltl ON tl.id = rltl.target_latihan_id
WHERE rltl.rencana_latihan_id = ?
  AND tl.jenis_target = 'individu'
  AND tl.peruntukan = ? -- sesuai jenis_peserta parameter
```

## Testing

### **Test Case 1: Atlet**
```bash
curl -X GET "http://localhost:8000/api/rencana-latihan/1/target-mapping/participant?jenis_peserta=atlet" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected**: Hanya target dengan `jenis_target: 'individu'` dan `peruntukan: 'atlet'`

### **Test Case 2: Pelatih**
```bash
curl -X GET "http://localhost:8000/api/rencana-latihan/1/target-mapping/participant?jenis_peserta=pelatih" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected**: Hanya target dengan `jenis_target: 'individu'` dan `peruntukan: 'pelatih'`

### **Test Case 3: Tenaga Pendukung**
```bash
curl -X GET "http://localhost:8000/api/rencana-latihan/1/target-mapping/participant?jenis_peserta=tenaga-pendukung" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected**: Hanya target dengan `jenis_target: 'individu'` dan `peruntukan: 'tenaga-pendukung'`

## Status

✅ **Fixed**: Target kelompok tidak muncul di pemetaan peserta
✅ **Fixed**: Target individu difilter berdasarkan peruntukan
✅ **Fixed**: Response mengembalikan `jenis_target` dan `peruntukan`
✅ **Added**: Method `mapJenisPesertaToPeruntukan`
✅ **Updated**: Filtering logic di `getExistingParticipantMappings`

## Catatan

- Filtering dilakukan di level database untuk performa yang lebih baik
- Response format tetap backward compatible
- Mobile app dapat menggunakan field `jenis_target` dan `peruntukan` untuk validasi tambahan
- Semua bug yang dilaporkan dari mobile telah diperbaiki
