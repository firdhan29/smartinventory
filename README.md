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

### 5. 📊 Spreadsheet Laporan Keuangan, Karyawan & Payroll
*   **Spreadsheet Interaktif (Excel-Style)**: Grid spreadsheet responsif lengkap dengan baris indeks (`1, 2, 3...`), tajuk kolom (`A, B, C...`), dan baris formula reaktif `=SUM(Pemasukan) - SUM(Pengeluaran)` layaknya Google Sheets.
*   **Kelola Data Karyawan**: CRUD Karyawan terintegrasi database untuk mendata Nama, Posisi, Gaji Pokok Bulanan, dan Rekening Bank Staf.
*   **Disbursement Payroll Buku Besar**: Pencairan gaji staf dengan satu klik yang otomatis mengurangi arus kas operasional dan mencatat slip payroll ke Buku Besar secara langsung.
*   **Ekspor Excel (.xlsx)**: Unduh lembar kerja spreadsheet buku besar keuangan terperinci dalam satu klik menggunakan *Maatwebsite Excel*.

### 6. 📸 Pemindai Barcode / QR (Mobile & Desktop USB)
*   **Platform Detection**: Deteksi otomatis lingkungan runtime (Capacitor Native App di smartphone vs Browser Web di desktop).
*   **Viewfinder Radar Scanner**: Animasi radar bidik kamera dengan sinar laser merah bergerak.
*   **Fast-Checkout Lookup**: Pemindaian kilat atau input SKU manual untuk memunculkan formulir transaksi secara instan.

### 7. 📋 Histori Edit & Audit Trail Logging (Premium Track)
*   **Pencatatan Transaksional Audit**: Setiap tindakan penambahan, pengubahan, atau penghapusan catatan kas oleh staf secara instan dicatat di tabel `financial_audit_logs`.
*   **Visual Side-by-Side Comparison**: Membandingkan perubahan **Data Lama (Sebelum)** vs **Data Baru (Sesudah)** secara mendalam (Tipe, Nominal, Tanggal, dan Catatan).
*   **Role-Based Gating Otoritas**:
    *   **Administrator (`admin`)**: Akses penuh mengedit/menghapus kas Buku Besar, melihat riwayat audit, dan hak eksklusif membersihkan log audit secara permanen.
    *   **Finance Specialist (`finance`)**: Dapat menginput kas manual, mengedit ledger, dan membaca log audit dengan status *Read-Only*.
    *   **Operator Gudang (`operator`)**: Terkunci sepenuhnya dari menu Keuangan dan Audit Trail.

---

## 🛠 TEKNOLOGI YANG DIGUNAKAN

### Backend (Laravel 12 Core)
*   **Laravel 12.x**: Kerangka kerja backend PHP berkinerja tinggi.
*   **Spatie Laravel Permission**: Manajemen otorisasi hak akses berdasarkan peran (**Admin**, **Finance**, dan **Operator Gudang**).
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
*   PHP $\ge$ 8.2 (Direkomendasikan PHP 8.3)
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

Saat Anda menjalankan `db:seed`, akun pengguna bawaan dengan tingkat otorisasi peran yang berbeda otomatis dibuat untuk mempermudah uji coba:

### 1. Administrator Utama (Full Access + Advanced Control)
*   **Email**: `admin@gmail.com`
*   **Password**: `admin12`
*   **Hak Akses**: Akses kontrol menyeluruh terhadap catalog barang, mutasi, spreadsheet fx, edit/hapus Buku Besar, registrasi akun staf, payroll gaji karyawan, serta hak penuh untuk mereset log audit trail.

### 2. Operator Gudang (Akses Terbatas + Mutasi Stok & Scanner)
*   **Email**: `operator@smartinventory.com`
*   **Password**: `password`
*   **Hak Akses**: Terbatas hanya untuk melihat stok produk, letak koordinat rak, melakukan transaksi masuk/keluar barang (scan/manual), serta mengunduh Surat Jalan PDF. Tidak dapat mengakses data keuangan, karyawan, payroll, ataupun audit trail.

### 3. Staf Finance (Akses Keuangan + Read-Only Audit)
*   Dapat didaftarkan langsung oleh Administrator melalui modul **Registrasi Akun Staf** di dalam dasbor Admin. Staf Finance memiliki hak penuh mencatat kas manual, mengedit ledger, serta melihat visual riwayat audit (read-only, tanpa hak reset).
