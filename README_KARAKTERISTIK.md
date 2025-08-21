# Fitur Karakteristik - Core Atlet

## Deskripsi
Fitur karakteristik adalah fitur analisis statistik yang menampilkan karakteristik data dari modul Atlet, Pelatih, dan Tenaga Pendukung dalam bentuk tabel dengan persentase.

## Fitur yang Tersedia

### 1. Karakteristik Atlet
- **Jenis Kelamin**: Laki-laki, Perempuan, Tidak ada data
- **Status Aktif**: Aktif, Nonaktif, Tidak ada data
- **Kategori Usia**: 
  - Anak-anak (0-12 tahun)
  - Remaja (13-17 tahun)
  - Dewasa Muda (18-25 tahun)
  - Dewasa (26-35 tahun)
  - Dewasa Tua (36+ tahun)
  - Tidak ada data tanggal lahir
- **Lama Bergabung**:
  - Baru bergabung (< 1 tahun)
  - Sedang (1-3 tahun)
  - Lama (3-5 tahun)
  - Sangat lama (5+ tahun)
  - Tidak ada data tanggal bergabung

### 2. Karakteristik Pelatih
- **Jenis Kelamin**: Laki-laki, Perempuan, Tidak ada data
- **Status Aktif**: Aktif, Nonaktif, Tidak ada data
- **Kategori Usia**: 
  - Dewasa Muda (18-25 tahun)
  - Dewasa (26-35 tahun)
  - Dewasa Tua (36-45 tahun)
  - Senior (46-55 tahun)
  - Veteran (56+ tahun)
  - Tidak ada data tanggal lahir
- **Lama Bergabung**:
  - Baru bergabung (< 2 tahun)
  - Sedang (2-5 tahun)
  - Lama (5-10 tahun)
  - Sangat lama (10+ tahun)
  - Tidak ada data tanggal bergabung

### 3. Karakteristik Tenaga Pendukung
- **Jenis Kelamin**: Laki-laki, Perempuan, Tidak ada data
- **Status Aktif**: Aktif, Nonaktif, Tidak ada data
- **Kategori Usia**: 
  - Dewasa Muda (18-25 tahun)
  - Dewasa (26-35 tahun)
  - Dewasa Tua (36-45 tahun)
  - Senior (46-55 tahun)
  - Veteran (56+ tahun)
  - Tidak ada data tanggal lahir
- **Lama Bergabung**:
  - Baru bergabung (< 2 tahun)
  - Sedang (2-5 tahun)
  - Lama (5-10 tahun)
  - Sangat lama (10+ tahun)
  - Tidak ada data tanggal bergabung

## Cara Menggunakan

### 1. Akses Halaman Karakteristik
- Klik tombol "📊 Statistik" di halaman index masing-masing modul
- Atau akses langsung melalui URL:
  - `/atlet/karakteristik`
  - `/pelatih/karakteristik`
  - `/tenaga-pendukung/karakteristik`

### 2. Filter Data
- Pilih rentang tanggal awal dan akhir (opsional)
- Klik tombol "Cari" untuk memfilter data berdasarkan tanggal
- Jika tidak memilih tanggal, akan menampilkan semua data

### 3. Interpretasi Data
- **Jumlah**: Menampilkan jumlah data untuk setiap indikator
- **Persentase**: Menampilkan persentase dari total data
- Data dikelompokkan berdasarkan karakteristik utama
- Setiap karakteristik memiliki beberapa indikator detail

## Struktur Teknis

### Backend
- **Repository**: Method `jumlah_karakteristik()` di masing-masing repository
- **Controller**: Method `karakteristik()` dan `apiKarakteristik()`
- **Model**: Scope `filter()` untuk filtering berdasarkan tanggal
- **Routes**: Route untuk halaman dan API karakteristik

### Frontend
- **Vue Components**: Halaman karakteristik dengan filter dan tabel
- **UI Components**: Menggunakan shadcn/ui components
- **Layout**: Konsisten dengan layout aplikasi lainnya
- **Responsive**: Mendukung tampilan mobile dan desktop

## API Endpoints

### 1. Atlet
- `GET /atlet/karakteristik` - Halaman karakteristik
- `POST /atlet/api-karakteristik` - Data karakteristik dalam format JSON

### 2. Pelatih
- `GET /pelatih/karakteristik` - Halaman karakteristik
- `POST /pelatih/api-karakteristik` - Data karakteristik dalam format JSON

### 3. Tenaga Pendukung
- `GET /tenaga-pendukung/karakteristik` - Halaman karakteristik
- `POST /tenaga-pendukung/api-karakteristik` - Data karakteristik dalam format JSON

## Format Response API

```json
{
    "success": true,
    "data": [
        {
            "key": "jenis_kelamin",
            "name": "Jenis Kelamin",
            "data": [
                {
                    "nama_indikator": "Laki-laki",
                    "jumlah": 150,
                    "persentase": 60.0
                },
                {
                    "nama_indikator": "Perempuan",
                    "jumlah": 100,
                    "persentase": 40.0
                }
            ]
        }
    ]
}
```

## Keunggulan Fitur

1. **Konsistensi**: Desain dan struktur yang konsisten dengan modul lainnya
2. **Responsif**: Mendukung berbagai ukuran layar
3. **Filtering**: Kemampuan filter berdasarkan rentang tanggal
4. **Real-time**: Data diambil secara real-time dari database
5. **User-friendly**: Interface yang mudah digunakan dan dipahami
6. **Extensible**: Mudah untuk menambah karakteristik baru

## Pengembangan Selanjutnya

1. **Chart/Graph**: Menambahkan visualisasi data dalam bentuk chart
2. **Export Data**: Kemampuan export data ke Excel/PDF
3. **Perbandingan**: Fitur perbandingan antar periode
4. **Dashboard**: Integrasi dengan dashboard utama
5. **Notifikasi**: Alert untuk perubahan data signifikan

## Catatan Penting

- Fitur ini memerlukan permission yang sesuai untuk diakses
- Data yang ditampilkan berdasarkan data yang ada di database
- Filter tanggal berdasarkan field `created_at` di masing-masing model
- Perhitungan persentase dilakukan secara real-time
- Data dikelompokkan secara otomatis berdasarkan kategori yang telah ditentukan
