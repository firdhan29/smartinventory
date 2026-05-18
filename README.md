# Smart Inventory App 📦🚀

Aplikasi **Smart Inventory** adalah sistem manajemen inventarisasi pergudangan modern komersial yang dirancang khusus untuk menangani multi-kualitas barang (*Grading System*) dan penempatan lokasi rak fisik. Aplikasi ini berjalan secara *hybrid* (Web & Mobile) dengan memadukan keandalan backend **Laravel 12**, keindahan reaktivitas SPA dari **Inertia.js & Vue 3**, serta integrasi kamera native menggunakan **Capacitor**.

---

## 🌟 FITUR UTAMA & FUNGSIONAL

### 1. 📊 Pusat Statistik & Dasbor Analitik
*   **KPI Glassmorphism Cards**: Menampilkan total nilai aset fisik (stok $\times$ harga beli), rasio barang *reject*, total penjualan bulan ini, dan total barang kritis.
*   **Safety Stock Indicators**: Daftar responsif produk yang memiliki jumlah stok Grade A di bawah batas aman ($\le$ 5 pcs).
*   **Quality Distribution Chart**: Grafik lingkaran (*Doughnut Chart.js*) dinamis memetakan persentase sebaran persediaan (Grade A, B, Reject).

### 2. 📦 Katalog Produk & Auto-SKU
*   **CRUD Produk Lengkap**: Manajemen katalog dengan kategori, nama, deskripsi, harga beli, dan harga jual.
*   **Auto-Generating SKU**: Generator kode unik SKU otomatis berbasis inisial kategori dan penanda waktu.
*   **Foto Upload & Kompresi**: Proses upload foto produk secara asinkron dengan fitur kompresi otomatis di server (maksimal 500kb menggunakan *Intervention Image*) untuk menghemat penyimpanan server.

### 3. 📍 Peta Stok & Penempatan Rak (Shelf Locator)
*   **Tab Grading**: Persediaan barang dipisahkan ke dalam 3 tab kualitas fisik: **Grade A** (Prima), **Grade B** (Defect Ringan/Diskon), dan **Reject** (Rusak/Retur).
*   **Letak Koordinat Rak**: Pemetaan lokasi rak fisik penyimpanan barang (`rak_lokasi`).
*   **Safety Stock Banner Alert**: Notifikasi merah menyala saat stok produk Grade A menipis untuk mencegah kehabisan stok penjualan.

### 4. 📄 Log Mutasi & Cetak Surat Jalan PDF (Invois)
*   **Pencatatan Masuk & Keluar**: Form pencatatan transaksi masuk (pembelian/restock) dan keluar (penjualan/disposal) dengan *smart-fill* otomatis harga aktual.
*   **Cetak PDF (DomPDF)**: Tombol cetak langsung untuk mengunduh bukti **Surat Jalan / Invois PDF** profesional yang memuat kop surat, rincian barang, total biaya, catatan, serta kolom tanda tangan operator/manajer.

### 5. 💰 Buku Besar Keuangan & Ekspor Excel
*   **Auto-Ledger**: Setiap transaksi mutasi barang masuk otomatis tercatat sebagai **Pengeluaran** kas, dan barang keluar tercatat sebagai **Pemasukan** kas di buku besar.
*   **Monthly Cashflow Line Chart**: Grafik perbandingan total omset bulanan vs pengeluaran dalam 6 bulan terakhir.
*   **Catat Kas Operasional**: Modul penginputan kas manual untuk biaya non-barang seperti listrik, gaji karyawan, dan sewa.
*   **Ekspor Excel (.xlsx)**: Unduh lembar kerja spreadsheet buku besar keuangan terperinci dalam satu klik menggunakan *Maatwebsite Excel*.

### 6. 📸 Pemindai Barcode / QR (Mobile & Desktop USB)
*   **Platform Detection**: Deteksi otomatis lingkungan runtime (Capacitor Native App di smartphone vs Browser Web di desktop).
*   **Viewfinder Radar Scanner**: Animasi radar bidik kamera dengan sinar laser merah bergerak.
*   **Fast-Checkout Lookup**: Pemindaian kilat atau input SKU manual untuk memunculkan formulir transaksi secara instan.

---

## 🛠 TEKNOLOGI YANG DIGUNAKAN

### Backend (Laravel 12 Core)
*   **Laravel 12.x**: Kerangka kerja backend PHP berkinerja tinggi.
*   **Spatie Laravel Permission**: Manajemen otorisasi hak akses berdasarkan peran (**Admin** dan **Operator Gudang**).
*   **Barryvdh Laravel DomPDF**: Pembuat berkas laporan berformat PDF.
*   **Maatwebsite Laravel Excel**: Pengekspor berkas Excel (.xlsx).
*   **Intervention Image**: Pemotong dan pengompres resolusi foto produk secara otomatis.

### Frontend (SPA & Reactivity)
*   **Inertia.js 2.x**: Penghubung dinamis tanpa API REST antara Laravel dan Vue 3 (SPA Experience).
*   **Vue 3 (Composition API)**: Framework Javascript reaktif untuk performa antarmuka yang cepat.
*   **Tailwind CSS**: Desain UI premium kustom bertema *glassmorphic* dan gelap yang modern.
*   **Chart.js & Vue-Chartjs**: Library grafik untuk laporan keuangan dan distribusi produk.

### Mobile Hybrid Integration
*   **Capacitor CLI**: Pembungkus aplikasi web menjadi aplikasi native Android/iOS.
*   **Capacitor Camera API**: Akses native kamera perangkat untuk pemindaian barcode.

---

## 🚀 PANDUAN INSTALASI & MENJALANKAN LOKAL

### Prasyarat
*   PHP $\ge$ 8.2
*   Composer
*   Node.js & NPM
*   Laragon / XAMPP (MySQL Database)

### Langkah-langkah
1.  **Clone repositori ke lokal**:
    ```bash
    git clone https://github.com/firdhan29/smartinventory.git
    cd smart-inventory
    ```

2.  **Instal dependensi Backend (PHP)**:
    ```bash
    composer install
    ```

3.  **Instal dependensi Frontend (NPM)**:
    ```bash
    npm install
    ```

4.  **Konfigurasi file `.env`**:
    Salin berkas `.env.example` menjadi `.env` dan atur koneksi database MySQL Anda:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=gudang
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5.  **Generate Application Key**:
    ```bash
    php artisan key:generate
    ```

6.  **Migrasi & Seed Database**:
    Jalankan perintah berikut untuk membuat seluruh tabel dan menyuntikkan data dummy awal (termasuk role, produk, transaksi, dan akun demo):
    ```bash
    php artisan migrate:fresh --seed
    ```

7.  **Hubungkan Link Storage**:
    ```bash
    php artisan storage:link
    ```

8.  **Kompilasi Frontend**:
    *   Untuk tahap pengembangan (development):
        ```bash
        npm run dev
        ```
    *   Untuk tahap produksi (production build):
        ```bash
        npm run build
        ```

9.  **Jalankan Aplikasi**:
    Gunakan Laragon (akses langsung virtual host seperti `http://smart-inventory.test`) atau jalankan server lokal bawaan PHP:
    ```bash
    php artisan serve
    ```

---

## 🔑 AKUN AKSES DEMO (TESTING CREDENTIALS)

Saat Anda menjalankan `db:seed`, dua akun pengguna dengan tingkat otorisasi peran yang berbeda otomatis dibuat untuk mempermudah uji coba:

### 1. Administrator (Akses Penuh + Keuangan)
*   **Email**: `admin@smartinventory.com`
*   **Password**: `password`
*   **Hak Akses**: Dapat melihat laporan keuangan, mengunduh file Excel buku besar, mengelola produk (CRUD), serta melihat stok.

### 2. Operator Gudang (Akses Terbatas + Mutasi Stok & Scanner)
*   **Email**: `operator@smartinventory.com`
*   **Password**: `password`
*   **Hak Akses**: Terbatas hanya untuk melihat stok produk, melakukan transaksi masuk/keluar barang (scan/manual), serta mengunduh Surat Jalan PDF. Tidak dapat mengakses data keuangan atau profit.
