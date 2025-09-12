# API Pemeriksaan Peserta Parameter - Mobile

## Overview

API ini digunakan untuk mengelola parameter pemeriksaan peserta di mobile app. Meliputi pengambilan data parameter, peserta dengan nilai parameter, dan bulk update nilai parameter.

## Base URL

```
/api/pemeriksaan/{pemeriksaanId}/...
```

## Authentication

Semua endpoint memerlukan authentication menggunakan Sanctum token.

```http
Authorization: Bearer YOUR_TOKEN
```

## Endpoints

### 1. Get Parameter Pemeriksaan

Mengambil daftar parameter pemeriksaan.

**Endpoint:**
```http
GET /api/pemeriksaan/{pemeriksaanId}/parameter
```

**Parameters:**
- `pemeriksaanId` (integer, required): ID pemeriksaan

**Response:**
```json
{
  "status": "success",
  "message": "Data parameter pemeriksaan berhasil diambil",
  "data": [
    {
      "id": 1,
      "nama_parameter": "Kecepatan Lari 100m",
      "satuan": "detik"
    },
    {
      "id": 2,
      "nama_parameter": "Kekuatan Genggaman",
      "satuan": "kg"
    }
  ]
}
```

### 2. Get Peserta dengan Parameter Values

Mengambil daftar peserta pemeriksaan beserta nilai parameter yang sudah diisi.

**Endpoint:**
```http
GET /api/pemeriksaan/{pemeriksaanId}/peserta/{jenisPeserta}/parameter
```

**Parameters:**
- `pemeriksaanId` (integer, required): ID pemeriksaan
- `jenisPeserta` (string, required): Jenis peserta (`atlet`, `pelatih`, `tenaga-pendukung`)

**Response:**
```json
{
  "status": "success",
  "message": "Data peserta dengan parameter berhasil diambil",
  "data": [
    {
      "id": 1,
      "peserta_id": 5,
      "peserta": {
        "nama": "John Doe",
        "jenis_kelamin": "Laki-laki",
        "usia": 25,
        "foto": "path/to/photo.jpg"
      },
      "status": {
        "id": 1,
        "nama": "Selesai"
      },
      "ref_status_pemeriksaan_id": 1,
      "catatan_umum": "Pemeriksaan berjalan lancar",
      "parameters": [
        {
          "parameter_id": 1,
          "nilai": "12.5",
          "trend": "kenaikan"
        },
        {
          "parameter_id": 2,
          "nilai": "45.2",
          "trend": "stabil"
        }
      ]
    }
  ]
}
```

### 3. Bulk Update Parameter Peserta

Mengupdate nilai parameter untuk multiple peserta sekaligus.

**Endpoint:**
```http
POST /api/pemeriksaan/{pemeriksaanId}/peserta-parameter/bulk-update
```

**Parameters:**
- `pemeriksaanId` (integer, required): ID pemeriksaan

**Request Body:**
```json
{
  "data": [
    {
      "peserta_id": 1,
      "status": 1,
      "catatan": "Pemeriksaan berjalan lancar",
      "parameters": [
        {
          "parameter_id": 1,
          "nilai": "12.5",
          "trend": "kenaikan"
        },
        {
          "parameter_id": 2,
          "nilai": "45.2",
          "trend": "stabil"
        }
      ]
    },
    {
      "peserta_id": 2,
      "status": 2,
      "catatan": "Perlu perhatian khusus",
      "parameters": [
        {
          "parameter_id": 1,
          "nilai": "13.2",
          "trend": "penurunan"
        },
        {
          "parameter_id": 2,
          "nilai": "42.1",
          "trend": "kenaikan"
        }
      ]
    }
  ]
}
```

**Validation Rules:**
- `data` (array, required): Array data peserta
- `data.*.peserta_id` (integer, required): ID peserta pemeriksaan
- `data.*.status` (integer, nullable): ID status pemeriksaan
- `data.*.catatan` (string, nullable): Catatan umum
- `data.*.parameters` (array, required): Array parameter
- `data.*.parameters.*.parameter_id` (integer, required): ID parameter pemeriksaan
- `data.*.parameters.*.nilai` (string, nullable): Nilai parameter
- `data.*.parameters.*.trend` (string, required): Trend parameter (`stabil`, `kenaikan`, `penurunan`)

**Response:**
```json
{
  "status": "success",
  "message": "Data parameter peserta berhasil diperbarui"
}
```

### 4. Get Single Peserta Parameter

Mengambil data parameter untuk satu peserta.

**Endpoint:**
```http
GET /api/pemeriksaan/{pemeriksaanId}/peserta/{pesertaId}/parameter
```

**Parameters:**
- `pemeriksaanId` (integer, required): ID pemeriksaan
- `pesertaId` (integer, required): ID peserta pemeriksaan

**Response:**
```json
{
  "status": "success",
  "message": "Data parameter peserta berhasil diambil",
  "data": {
    "id": 1,
    "peserta_id": 5,
    "peserta": {
      "nama": "John Doe",
      "jenis_kelamin": "Laki-laki",
      "usia": 25,
      "foto": "path/to/photo.jpg"
    },
    "status": {
      "id": 1,
      "nama": "Selesai"
    },
    "ref_status_pemeriksaan_id": 1,
    "catatan_umum": "Pemeriksaan berjalan lancar",
    "parameters": [
      {
        "parameter_id": 1,
        "nama_parameter": "Kecepatan Lari 100m",
        "satuan": "detik",
        "nilai": "12.5",
        "trend": "kenaikan"
      },
      {
        "parameter_id": 2,
        "nama_parameter": "Kekuatan Genggaman",
        "satuan": "kg",
        "nilai": "45.2",
        "trend": "stabil"
      }
    ]
  }
}
```

## Error Responses

### 404 Not Found
```json
{
  "status": "error",
  "message": "Pemeriksaan tidak ditemukan"
}
```

### 422 Validation Error
```json
{
  "status": "error",
  "message": "Validasi gagal",
  "errors": {
    "data.0.peserta_id": ["ID peserta wajib diisi"],
    "data.0.parameters.0.trend": ["Trend harus berupa: stabil, kenaikan, atau penurunan"]
  }
}
```

### 500 Internal Server Error
```json
{
  "status": "error",
  "message": "Gagal memperbarui data parameter peserta: Error message"
}
```

## Data Models

### PemeriksaanPesertaParameter
```php
{
  "pemeriksaan_id": 1,
  "pemeriksaan_peserta_id": 1,
  "pemeriksaan_parameter_id": 1,
  "nilai": "12.5",
  "trend": "kenaikan",
  "created_by": 1,
  "updated_by": 1
}
```

### Trend Options
- `stabil`: Stabil
- `kenaikan`: Kenaikan
- `penurunan`: Penurunan

## Usage Examples

### Mobile App Flow

1. **Load Parameter List**
   ```javascript
   const parameters = await fetch('/api/pemeriksaan/1/parameter');
   ```

2. **Load Peserta with Parameters**
   ```javascript
   const peserta = await fetch('/api/pemeriksaan/1/peserta/atlet/parameter');
   ```

3. **Save Bulk Update**
   ```javascript
   const response = await fetch('/api/pemeriksaan/1/peserta-parameter/bulk-update', {
     method: 'POST',
     headers: {
       'Content-Type': 'application/json',
       'Authorization': 'Bearer ' + token
     },
     body: JSON.stringify({
       data: pesertaData
     })
   });
   ```

## Notes

- Semua endpoint menggunakan database transaction untuk memastikan data consistency
- Field `nilai` dapat berupa string kosong jika belum diisi
- Field `trend` wajib diisi dengan salah satu dari 3 opsi yang tersedia
- API akan otomatis update `updated_by` dengan ID user yang sedang login
- Bulk update akan menggunakan `updateOrCreate` untuk parameter values
