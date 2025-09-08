# Update Module Pemeriksaan Parameter

## Ringkasan Perubahan

Module pemeriksaan parameter telah diupdate untuk menggunakan referensi ke tabel `mst_parameter` alih-alih input manual nama parameter dan satuan. Perubahan ini memungkinkan:

1. **Konsistensi Data**: Parameter yang sama akan memiliki nama dan satuan yang konsisten
2. **Kemudahan Maintenance**: Perubahan nama parameter atau satuan hanya perlu dilakukan di satu tempat
3. **Dropdown Selection**: User dapat memilih parameter dari daftar yang sudah ada

## File yang Diubah

### 1. Database Migrations

#### `database/migrations/2025_07_21_000001_create_pemeriksaan_parameter_table.php`

- Mengupdate migration asli untuk langsung menggunakan `mst_parameter_id`
- Menghapus kolom `nama_parameter` dan `satuan`
- Menambahkan foreign key constraint ke tabel `mst_parameter`

### 2. Models

#### `app/Models/PemeriksaanParameter.php`

- Menambahkan `mst_parameter_id` ke fillable
- Menghapus `nama_parameter` dan `satuan` dari fillable
- Menambahkan relasi `mstParameter()` ke model `MstParameter`

#### `app/Models/MstParameter.php`

- Menambahkan relasi `pemeriksaanParameters()` ke model `PemeriksaanParameter`

### 3. Controllers

#### `app/Http/Controllers/PemeriksaanParameterController.php`

- Menambahkan import `MstParameter`
- Mengirim data `mstParameters` ke view create dan edit
- Menggunakan relasi untuk mengambil data parameter

#### `app/Http/Controllers/PemeriksaanPesertaParameterController.php`

- Menambahkan import `MstParameter`
- Menggunakan relasi `mstParameter` saat mengambil data parameter

### 4. Repositories

#### `app/Repositories/PemeriksaanParameterRepository.php`

- Menambahkan `mstParameter` ke relasi yang dimuat
- Update search untuk mencari di relasi `mstParameter`
- Update transform data untuk menggunakan data dari relasi

#### `app/Repositories/PemeriksaanPesertaParameterRepository.php`

- Menambahkan `mstParameter` ke relasi yang dimuat
- Update search dan transform data untuk menggunakan relasi

#### `app/Repositories/PemeriksaanRepository.php`

- Update method `getParameterForMobile` untuk menggunakan relasi

### 5. Requests

#### `app/Http/Requests/PemeriksaanParameterRequest.php`

- Menambahkan validasi `mst_parameter_id` sebagai required
- Menghapus validasi `nama_parameter` dan `satuan`
- Menambahkan pesan error untuk validasi baru

### 6. Views (Vue.js)

#### `resources/js/pages/modules/pemeriksaan-parameter/Form.vue`

- Mengubah input nama parameter menjadi dropdown pilihan parameter
- Menambahkan field `mst_parameter_id`
- Menghapus field nama parameter dan satuan

#### `resources/js/pages/modules/pemeriksaan-parameter/Show.vue`

- Menampilkan informasi parameter dari relasi master parameter
- Menghapus referensi ke kolom nama_parameter dan satuan

#### `resources/js/pages/modules/pemeriksaan-peserta-parameter/Form.vue`

- Update dropdown parameter untuk menampilkan nama dan satuan dari relasi

#### `resources/js/pages/modules/pemeriksaan-peserta-parameter/Show.vue`

- Update tampilan parameter untuk menggunakan data dari relasi

### 7. Seeders

#### `database/seeders/PemeriksaanParameterSeeder.php`

- Mengupdate seeder untuk menggunakan relasi ke `mst_parameter`
- Menghapus referensi ke kolom `nama_parameter` dan `satuan`
- Menggunakan `mst_parameter_id` untuk membuat pemeriksaan parameter

## Cara Menjalankan Update

### 1. Jalankan Migrations

```bash
php artisan migrate
```

### 2. Jalankan Seeder Master Parameter (jika belum)

```bash
php artisan db:seed --class=MstParameterSeeder
```

### 3. Jalankan Seeder Pemeriksaan Parameter (jika diperlukan)

```bash
php artisan db:seed --class=PemeriksaanParameterSeeder
```

## Backward Compatibility

Perubahan ini tidak mempertahankan backward compatibility karena:

1. **Kolom Lama Dihapus**: Kolom `nama_parameter` dan `satuan` dihapus dari database
2. **Struktur Baru**: Sistem sekarang sepenuhnya menggunakan relasi ke `mst_parameter`
3. **Fresh Start**: Database akan dibuat dengan struktur baru yang lebih bersih

## Struktur Data Baru

### Tabel `pemeriksaan_parameter`

```sql
- id (primary key)
- pemeriksaan_id (foreign key)
- mst_parameter_id (foreign key ke mst_parameter) -- BARU
- created_at, updated_at, deleted_at
- created_by, updated_by, deleted_by
```

### Relasi Baru

```php
// PemeriksaanParameter
public function mstParameter()
{
    return $this->belongsTo(MstParameter::class, 'mst_parameter_id');
}

// MstParameter
public function pemeriksaanParameters()
{
    return $this->hasMany(PemeriksaanParameter::class, 'mst_parameter_id');
}
```

## Manfaat Perubahan

1. **Data Consistency**: Parameter yang sama akan memiliki nama dan satuan yang konsisten
2. **Easy Maintenance**: Perubahan parameter hanya perlu dilakukan di master parameter
3. **Better UX**: User dapat memilih parameter dari dropdown yang sudah ada
4. **Data Integrity**: Foreign key constraint memastikan data parameter valid
5. **Scalability**: Mudah menambah parameter baru tanpa mengubah struktur form

## Testing

Setelah update, pastikan untuk test:

1. **Create Parameter**: Membuat parameter pemeriksaan baru dengan pilihan dari dropdown
2. **Edit Parameter**: Mengedit parameter yang sudah ada
3. **View Parameter**: Menampilkan parameter dengan data dari relasi
4. **Peserta Parameter**: Membuat dan melihat parameter peserta
5. **Search**: Pencarian parameter berdasarkan nama dari master parameter
6. **Mobile API**: API mobile untuk parameter pemeriksaan

## Troubleshooting

### Jika ada error saat migration:

1. Pastikan tabel `mst_parameter` sudah ada
2. Pastikan seeder `MstParameterSeeder` sudah dijalankan
3. Check foreign key constraint jika ada data yang tidak valid

### Jika data tidak muncul:

1. Pastikan relasi sudah dimuat dengan `with('mstParameter')`
2. Check apakah `mst_parameter_id` sudah terisi
3. Pastikan fallback logic berfungsi untuk data lama
