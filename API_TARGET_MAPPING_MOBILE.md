# API Target Mapping Mobile

Dokumentasi API untuk fitur pemetaan nilai target latihan di mobile, baik untuk peserta individu maupun kelompok.

## Endpoints

### 1. Get Participant Target Mapping

**GET** `/api/rencana-latihan/{rencanaId}/target-mapping/participant`

Mengambil data mapping target untuk peserta individu dalam rencana latihan tertentu.

#### Parameters
- `rencanaId` (path, required): ID rencana latihan
- `jenis_peserta` (query, optional): Jenis peserta (`atlet`, `pelatih`, `tenaga-pendukung`). Default: `atlet`

#### Response
```json
{
  "status": "success",
  "message": "Data mapping target peserta berhasil diambil",
  "data": {
    "rencana_latihan": {
      "id": 1,
      "tanggal": "2025-01-15",
      "materi": "Latihan Fisik Dasar",
      "lokasi_latihan": "Lapangan Utama",
      "program_latihan": {
        "nama_program": "Program Latihan Bulutangkis 2025",
        "cabor_nama": "Bulu Tangkis",
        "cabor_kategori_nama": "Tunggal Putra"
      }
    },
    "jenis_peserta": "atlet",
    "peserta_list": [
      {
        "peserta_id": 1,
        "peserta": {
          "id": 1,
          "nama": "John Doe",
          "foto": "path/to/photo.jpg",
          "jenis_kelamin": "Laki-laki",
          "usia": 25,
          "posisi": "Pemain Utama"
        },
        "targets": [
          {
            "target_latihan_id": 1,
            "deskripsi": "Kecepatan Lari 100m",
            "satuan": "detik",
            "nilai_target": "12.5",
            "nilai": "12.3",
            "trend": "naik"
          }
        ]
      }
    ]
  }
}
```

### 2. Get Group Target Mapping

**GET** `/api/program-latihan/{programId}/target-mapping/group`

Mengambil data mapping target untuk kelompok dalam program latihan.

#### Parameters
- `programId` (path, required): ID program latihan

#### Response
```json
{
  "status": "success",
  "message": "Data mapping target kelompok berhasil diambil",
  "data": {
    "program_latihan": {
      "nama_program": "Program Latihan Bulutangkis 2025",
      "cabor_nama": "Bulu Tangkis",
      "cabor_kategori_nama": "Tunggal Putra"
    },
    "rencana_latihan_list": [
      {
        "rencana_id": 1,
        "tanggal": "2025-01-15",
        "materi": "Latihan Fisik Dasar",
        "lokasi_latihan": "Lapangan Utama",
        "jumlah_atlet": 5,
        "jumlah_pelatih": 2,
        "jumlah_tenaga_pendukung": 1,
        "targets": [
          {
            "target_latihan_id": 1,
            "deskripsi": "Kecepatan Lari 100m",
            "satuan": "detik",
            "nilai_target": "12.5",
            "nilai": "12.2",
            "trend": "naik"
          }
        ]
      }
    ]
  }
}
```

### 3. Bulk Update Participant Targets

**POST** `/api/rencana-latihan/{rencanaId}/target-mapping/participant/bulk-update`

Mengupdate nilai target untuk peserta individu secara bulk.

#### Parameters
- `rencanaId` (path, required): ID rencana latihan

#### Request Body
```json
{
  "data": [
    {
      "peserta_id": 1,
      "target_latihan_id": 1,
      "nilai": "12.3",
      "trend": "naik"
    },
    {
      "peserta_id": 1,
      "target_latihan_id": 2,
      "nilai": "45.2",
      "trend": "stabil"
    }
  ]
}
```

#### Response
```json
{
  "status": "success",
  "message": "Data target peserta berhasil diperbarui"
}
```

### 4. Bulk Update Group Targets

**POST** `/api/program-latihan/{programId}/target-mapping/group/bulk-update`

Mengupdate nilai target untuk kelompok secara bulk.

#### Parameters
- `programId` (path, required): ID program latihan

#### Request Body
```json
{
  "data": [
    {
      "rencana_latihan_id": 1,
      "target_latihan_id": 1,
      "nilai": "12.2",
      "trend": "naik"
    },
    {
      "rencana_latihan_id": 2,
      "target_latihan_id": 1,
      "nilai": "12.1",
      "trend": "naik"
    }
  ]
}
```

#### Response
```json
{
  "status": "success",
  "message": "Data target kelompok berhasil diperbarui"
}
```

## Data Models

### Participant Data
```typescript
interface Participant {
  id: number;
  nama: string;
  foto?: string;
  jenis_kelamin: 'Laki-laki' | 'Perempuan';
  usia: number | string;
  posisi?: string; // untuk atlet
  jenis_pelatih?: string; // untuk pelatih
  jenis_tenaga_pendukung?: string; // untuk tenaga pendukung
}
```

### Target Data
```typescript
interface Target {
  target_latihan_id: number;
  deskripsi: string;
  satuan: string;
  nilai_target: string;
  nilai: string;
  trend: 'naik' | 'stabil' | 'turun';
}
```

### Rencana Latihan Data
```typescript
interface RencanaLatihan {
  id: number;
  tanggal: string;
  materi: string;
  lokasi_latihan: string;
  program_latihan: {
    nama_program: string;
    cabor_nama: string;
    cabor_kategori_nama: string;
  };
}
```

## Error Responses

### 404 Not Found
```json
{
  "status": "error",
  "message": "Rencana latihan tidak ditemukan"
}
```

### 422 Validation Error
```json
{
  "status": "error",
  "message": "Validasi gagal",
  "errors": {
    "data.0.peserta_id": ["ID peserta wajib diisi"],
    "data.0.trend": ["Trend harus berupa: naik, stabil, atau turun"]
  }
}
```

### 500 Server Error
```json
{
  "status": "error",
  "message": "Gagal mengambil data mapping target peserta: [error message]"
}
```

## Authentication

Semua endpoints memerlukan authentication menggunakan Sanctum token. Include token dalam header:

```
Authorization: Bearer {token}
```

## Usage Examples

### Mobile App Implementation

#### 1. Load Participant Target Mapping
```javascript
// Load data untuk form pemetaan nilai peserta
const loadParticipantMapping = async (rencanaId, jenisPeserta = 'atlet') => {
  try {
    const response = await fetch(
      `/api/rencana-latihan/${rencanaId}/target-mapping/participant?jenis_peserta=${jenisPeserta}`,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }
    );
    
    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Error loading participant mapping:', error);
    throw error;
  }
};
```

#### 2. Save Participant Target Values
```javascript
// Simpan nilai target peserta
const saveParticipantTargets = async (rencanaId, targetData) => {
  try {
    const response = await fetch(
      `/api/rencana-latihan/${rencanaId}/target-mapping/participant/bulk-update`,
      {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ data: targetData })
      }
    );
    
    const result = await response.json();
    return result;
  } catch (error) {
    console.error('Error saving participant targets:', error);
    throw error;
  }
};
```

#### 3. Load Group Target Mapping
```javascript
// Load data untuk form pemetaan nilai kelompok
const loadGroupMapping = async (programId) => {
  try {
    const response = await fetch(
      `/api/program-latihan/${programId}/target-mapping/group`,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }
    );
    
    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Error loading group mapping:', error);
    throw error;
  }
};
```

#### 4. Save Group Target Values
```javascript
// Simpan nilai target kelompok
const saveGroupTargets = async (programId, targetData) => {
  try {
    const response = await fetch(
      `/api/program-latihan/${programId}/target-mapping/group/bulk-update`,
      {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ data: targetData })
      }
    );
    
    const result = await response.json();
    return result;
  } catch (error) {
    console.error('Error saving group targets:', error);
    throw error;
  }
};
```

## Notes

1. **Trend Values**: Hanya menerima nilai `naik`, `stabil`, atau `turun`
2. **Nilai**: Bisa berupa string kosong untuk nilai yang belum diisi
3. **Peserta Types**: 
   - `atlet`: Menampilkan posisi atlet
   - `pelatih`: Menampilkan jenis pelatih
   - `tenaga-pendukung`: Menampilkan jenis tenaga pendukung
4. **Bulk Update**: Semua data akan diupdate dalam satu transaksi database
5. **Existing Data**: API akan mengambil data yang sudah ada sebelumnya untuk pre-fill form
