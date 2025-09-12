# API Fix - Type Error pada Parameter

## Masalah

Error 500 Internal Server Error terjadi karena type hint yang tidak sesuai:

```
App\Http\Controllers\Api\RencanaLatihanTargetController::bulkUpdateParticipantTargets(): Argument #2 ($rencanaId) must be of type int, string given
```

## Penyebab

Laravel route parameters selalu dikirim sebagai string, tetapi method controller dideklarasikan dengan type hint `int`:

```php
// ❌ Error - Laravel mengirim string, tapi method expect int
public function bulkUpdateParticipantTargets(Request $request, int $rencanaId): JsonResponse

// ❌ Error - Laravel mengirim string, tapi method expect int  
public function bulkUpdateGroupTargets(Request $request, int $programId): JsonResponse
```

## Solusi

### 1. Hapus Type Hint dari Parameter

```php
// ✅ Fixed - Tidak ada type hint, parameter bisa string atau int
public function bulkUpdateParticipantTargets(Request $request, $rencanaId): JsonResponse
public function bulkUpdateGroupTargets(Request $request, $programId): JsonResponse
```

### 2. Tambahkan Validasi Manual

```php
public function bulkUpdateParticipantTargets(Request $request, $rencanaId): JsonResponse
{
    try {
        // Validate rencanaId parameter
        if (!is_numeric($rencanaId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID rencana latihan tidak valid'
            ], 400);
        }

        $rencanaId = (int) $rencanaId;
        
        // ... rest of the method
    }
}
```

### 3. Konversi ke Integer

Setelah validasi, konversi parameter ke integer:

```php
$rencanaId = (int) $rencanaId;
$programId = (int) $programId;
```

## Perubahan yang Dibuat

### File: `app/Http/Controllers/Api/RencanaLatihanTargetController.php`

1. **Method `bulkUpdateParticipantTargets`**:
   - Hapus type hint `int` dari parameter `$rencanaId`
   - Tambahkan validasi `is_numeric($rencanaId)`
   - Konversi ke integer dengan `(int) $rencanaId`

2. **Method `bulkUpdateGroupTargets`**:
   - Hapus type hint `int` dari parameter `$programId`
   - Tambahkan validasi `is_numeric($programId)`
   - Konversi ke integer dengan `(int) $programId`

## Testing

Setelah perbaikan, API endpoints dapat diakses tanpa error:

```bash
# Test participant target mapping
curl -X POST "http://localhost:8000/api/rencana-latihan/1/target-mapping/participant/bulk-update" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "data": [
      {
        "peserta_id": 1,
        "target_latihan_id": 1,
        "nilai": "12.5",
        "trend": "naik"
      }
    ]
  }'

# Test group target mapping
curl -X POST "http://localhost:8000/api/program-latihan/1/target-mapping/group/bulk-update" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "data": [
      {
        "rencana_latihan_id": 1,
        "target_latihan_id": 1,
        "nilai": "12.3",
        "trend": "stabil"
      }
    ]
  }'
```

## Status

✅ **Fixed**: Type error pada parameter controller
✅ **Added**: Validasi parameter yang robust
✅ **Added**: Konversi type yang aman
✅ **Tested**: API endpoints berfungsi normal

## Catatan

- Laravel route parameters selalu string
- Type hint `int` pada parameter controller tidak disarankan
- Gunakan validasi manual dan konversi type untuk keamanan
- Error handling yang baik untuk parameter yang tidak valid
