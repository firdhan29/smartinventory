# PRODUCT REQUIREMENTS DOCUMENT (PRD) & ANALISIS SISTEM
## SMART INVENTORY APP (Laravel 12 + Inertia.js Vue 3 + Capacitor)

Dokumen ini disusun sebagai panduan blueprint pengembangan aplikasi **Smart Inventory**, yang memadukan keunggulan performa backend Laravel 12 dengan reaktivitas Vue 3 (via Inertia.js) dan fleksibilitas *hybrid mobile app* menggunakan Capacitor.

---

## 1. PENDAHULUAN & VISI PRODUK
**Smart Inventory** adalah sistem manajemen inventaris modern berbasis web dan mobile (hybrid) yang dirancang untuk membantu bisnis (terutama manufaktur, pergudangan, atau ritel) mengelola produk mereka secara dinamis. 

Aplikasi ini tidak hanya mencatat jumlah barang masuk dan keluar, tetapi juga memiliki fitur unggulan berupa:
*   **Grading Product System**: Memisahkan stok berdasarkan kualitas barang (Grade A, B, dan Reject).
*   **Location-Aware Stocking**: Memetakan letak fisik barang di rak penyimpanan gudang (`rak_lokasi`).
*   **Mobile-First Barcode/QR Scanning**: Menggunakan kamera perangkat mobile secara langsung (via Capacitor) untuk mempercepat proses scan barang.
*   **Real-time Stock Synchronization**: Pembaruan stok instan di berbagai perangkat tanpa reload page (via Pusher).
*   **Integrated Financial Tracking**: Menghubungkan setiap pergerakan barang dengan arus keuangan perusahaan secara otomatis.

---

## 2. ANALISIS KONDISI APLIKASI SAAT INI (AUDIT KODE)

Berdasarkan analisis file proyek di direktori `c:\laragon\www\www\smart-inventory`, berikut adalah status implementasi saat ini:

### A. Teknologi yang Sudah Terpasang (`package.json` & `composer.json`)
1.  **Backend Core**: Laravel `^12.0` (versi terbaru dengan performa tinggi) dan PHP `^8.2`.
2.  **Frontend Core**: Vue 3 `^3.4`, Inertia.js `^2.0` (menghubungkan Laravel & Vue dengan lancar tanpa ribet mengelola API terpisah), dan Tailwind CSS (Vite integration).
3.  **Mobile Support**: `@capacitor/core` dan `@capacitor/camera` untuk membangun aplikasi Android/iOS dengan akses native kamera (scanner).
4.  **Fitur Tambahan**:
    *   `spatie/laravel-permission`: Manajemen otorisasi pengguna berdasarkan Role & Permission.
    *   `barryvdh/laravel-dompdf`: Generator laporan berformat PDF (Invois/Surat Jalan).
    *   `maatwebsite/excel`: Ekspor dan Impor data inventaris dalam format Excel (.xlsx).
    *   `pusher/pusher-php-server`: Server event broadcasting untuk update stok *real-time*.
    *   `intervention/image`: Manipulasi gambar (kompresi foto produk).
    *   `chart.js` & `vue-chartjs`: Visualisasi grafik tren stok, penjualan, dan keuntungan.

### B. Struktur Database yang Sudah Didefinisikan (Migrations)
*   `products`: Menyimpan informasi dasar produk (`sku` unik, `name`, `foto`, `harga_beli`, `harga_jual`).
*   `stocks`: Relasi ke produk, mencatat jumlah barang berdasarkan `grade` ('A', 'B', 'Reject') dan `rak_lokasi`.
*   `transactions`: Log transaksi masuk/keluar, mencatat `product_id`, `user_id` (operator), `quantity`, `grade` yang digunakan, `harga_aktual` saat transaksi, dan `notes`.
*   `financials`: Keuangan terintegrasi, mencatat jenis (`pemasukan`, `pengeluaran`), `amount`, `date`, dan relasi opsional `transaction_id`.
*   `role_users`: Tabel kustom untuk peran pengguna (meskipun ada Spatie Permission).

### C. Komponen yang Belum Diimplementasikan (0% Logika Bisnis)
*   **Models**: Hanya ada `User.php`. Model `Product`, `Stock`, `Transaction`, dan `Financial` belum dibuat.
*   **Controllers**: Hanya ada `ProfileController` dan otentikasi bawaan. Logika untuk manajemen produk, transaksi, filter stok, ekspor laporan, dan charting belum ada.
*   **Routes**: Belum ada endpoint untuk operasi inventaris di `web.php`.
*   **UI Views**: Baru terdapat halaman Dashboard standar bawaan Laravel Breeze. Halaman manajemen produk, scan QR, log transaksi, dan keuangan belum ada.

---

## 3. ANALISIS KELEBIHAN & KEKURANGAN (PROS & CONS)

### KELEBIHAN (Pros)
1.  **Arsitektur Sangat Modern & Cepat**: Laravel 12 + Inertia.js + Vue 3 memberikan sensasi aplikasi SPA (Single Page Application) yang sangat cepat tanpa overhead pembuatan API RESTful yang rumit.
2.  **Mendukung Mobile Hybrid (Capacitor)**: Pilihan cerdas menggunakan Capacitor. Kode web yang sama bisa dibungkus menjadi aplikasi Android/iOS, menghemat waktu dan biaya pengembangan aplikasi native secara terpisah.
3.  **Manajemen Kualitas Barang (Grading)**: Pemisahan stok berdasarkan `Grade A, B, Reject` sangat krusial untuk industri manufaktur, garmen, pertanian, atau elektronik yang memiliki variasi kualitas fisik produk.
4.  **Audit Finansial yang Ketat**: Dengan menghubungkan `transactions` langsung ke tabel `financials` via `transaction_id`, sistem secara otomatis mencatat pengeluaran saat beli barang (masuk) dan pemasukan saat jual barang (keluar). Pengguna dapat melihat keuntungan bersih riil.
5.  **Dukungan Laporan Lengkap**: Paket PDF dan Excel sudah terintegrasi, memudahkan pembuatan laporan stock opname bulanan dan cetak Surat Jalan.

### KEKURANGAN & CELAH DESAIN (Cons / Risks)
1.  **Keterbatasan Lokasi Rak**: Tabel `stocks` menyimpan kolom `rak_lokasi` hanya sebagai teks (`string` nullable). Jika gudang memiliki tata letak yang kompleks, ini akan sulit divalidasi. Idealnya ada tabel master `raks` tersendiri agar dapat dipetakan berdasarkan kapasitas dan zona.
2.  **Tidak Ada Kategori Produk**: Tabel `products` tidak memiliki kolom `category_id` atau tabel kategori. Tanpa kategori, pencarian barang pada inventaris berskala ribuan item akan sangat menyulitkan.
3.  **Redundansi Otorisasi**: Ada tabel kustom `role_users` di migrasi, padahal proyek sudah menyertakan paket `spatie/laravel-permission` yang memiliki skema tabel relasi role sendiri yang jauh lebih lengkap dan teruji.
4.  **Keamanan Upload Foto**: Belum ada pengelolaan penyimpanan gambar yang efisien (misalnya kompresi otomatis agar ukuran foto produk tidak membuat server penuh, apalagi aplikasi ini dirancang mobile-ready yang sering mengunggah foto beresolusi tinggi).

---

## 4. SPESIFIKASI KEBUTUHAN PRODUK (PRODUCT REQUIREMENTS)

### A. Fitur Utama & Kebutuhan Fungsional (FR)

#### 1. Modul Produk & SKU Generator
*   CRUD (Create, Read, Update, Delete) Produk.
*   Generasi SKU otomatis berbasis kode kategori dan timestamp (atau input manual).
*   Upload foto produk dengan auto-kompresi menggunakan `Intervention/Image` ke ukuran maksimal 500kb.
*   Integrasi pencarian dan filter cepat berdasarkan SKU atau nama produk.

#### 2. Modul Multi-Grade Stock & Shelf Locator
*   Tampilan persediaan barang per produk yang terbagi ke dalam 3 tab kualitas: **Grade A** (Kondisi Prima), **Grade B** (Defect Ringan/Diskon), **Reject** (Barang Rusak/Retur).
*   Peta Rak: Menampilkan lokasi penempatan barang (`rak_lokasi`) dan mendukung pemindahan barang antar-rak dengan mutasi stok yang tercatat di sistem.
*   Notifikasi Stok Minimum: Memberikan peringatan visual (warna kuning/merah) jika kuantitas stok produk tertentu berada di bawah batas minimum (*Safety Stock*).

#### 3. Modul Scanner Barcode/QR (Mobile-Only Interface)
*   Tampilan web responsif khusus perangkat mobile dengan tombol mengambang (Floating Action Button) bergambar Kamera.
*   Ketika ditekan di aplikasi mobile (Capacitor), akan membuka kamera perangkat untuk membaca barcode/QR Code.
*   Setelah barcode terdeteksi, aplikasi langsung menampilkan info produk dan opsi transaksi: "Barang Masuk" atau "Barang Keluar".

#### 4. Modul Transaksi & Log Mutasi (Real-time)
*   Form Transaksi Masuk (Restock/Pembelian) dan Transaksi Keluar (Penjualan/Pemakaian).
*   Wajib mencantumkan Grade barang saat melakukan transaksi.
*   Update stok secara langsung ke database dan menyiarkan perubahan stok ke seluruh pengguna aktif yang sedang membuka dasbor secara *real-time* menggunakan Pusher.
*   Setiap transaksi menghasilkan Surat Jalan/Invois PDF yang bisa diunduh atau langsung dicetak (DomPDF).

#### 5. Modul Keuangan Terintegrasi (Auto-Ledger)
*   **Transaksi Masuk (Barang Masuk)** -> Menghasilkan entri otomatis di tabel `financials` sebagai **Pengeluaran** dengan nominal `quantity * harga_aktual`.
*   **Transaksi Keluar (Barang Keluar)** -> Menghasilkan entri otomatis di tabel `financials` sebagai **Pemasukan** dengan nominal `quantity * harga_aktual`.
*   Pencatatan Keuangan Manual: Mendukung penginputan biaya operasional non-barang (listrik, gaji karyawan, sewa gedung) sebagai pengeluaran manual.

#### 6. Dasbor Analitik & Laporan Interaktif
*   Grafik Tren Stok: Menampilkan naik turun stok dari waktu ke waktu.
*   Grafik Arus Kas (Cashflow Chart): Membandingkan pemasukan vs pengeluaran bulanan menggunakan Chart.js.
*   Metrik Utama: Total Nilai Aset (dihitung dari jumlah stok * harga beli), Total Penjualan Bulan Ini, Rasio Barang Reject.
*   Ekspor Laporan: Fitur ekspor Excel untuk semua data transaksi, stok saat ini, dan laporan keuangan berdasarkan rentang tanggal tertentu.

### B. Kebutuhan Non-Fungsional (NFR)
1.  **Keamanan (Security)**:
    *   Role-Based Access Control (RBAC):
        *   **Admin**: Akses penuh ke seluruh fitur termasuk pengaturan harga beli dan laporan keuangan.
        *   **Operator Gudang**: Hanya bisa menginput Transaksi Masuk/Keluar, melihat stok, dan menggunakan kamera scanner. Tidak bisa melihat laporan keuangan.
    *   Proteksi Route: Semua rute operasional harus terproteksi oleh middleware `auth` Laravel.
2.  **Responsivitas (Responsiveness)**: Tampilan antarmuka harus *fully-responsive* menggunakan Tailwind CSS, khususnya pada halaman scanning agar nyaman digunakan di smartphone Android/iOS.
3.  **Keandalan (Reliability)**: Sinkronisasi data stok harus presisi untuk mencegah terjadinya *overselling* atau selisih stok (stock disparity). Penggunaan DB Transaction di Laravel sangat direkomendasikan saat mencatat transaksi dan memperbarui stok.

---

## 5. RENCANA IMPLEMENTASI & ARSITEKTUR KODE (ROADMAP)

Langkah konkret untuk mulai membangun sistem ini dari skeleton yang ada sekarang:

### Tahap 1: Struktur Model & Hubungan Database (Eloquent)
1.  Hapus migrasi kustom `role_users` dan manfaatkan sepenuhnya tabel bawaan Spatie Laravel Permission.
2.  Buat Model beserta relasi Eloquent-nya:
    *   `Product.php` -> HasMany `Stock`, HasMany `Transaction`.
    *   `Stock.php` -> BelongsTo `Product`.
    *   `Transaction.php` -> BelongsTo `Product`, BelongsTo `User`, HasOne `Financial`.
    *   `Financial.php` -> BelongsTo `Transaction` (nullable).
3.  Ubah tabel `products` dengan menambahkan kolom `category` (atau buat migrasi baru untuk kategori jika ingin lebih dinamis).
4.  Buat `DatabaseSeeder` untuk menyuntikkan data dummy produk dan pengguna awal demi mempermudah testing.

### Tahap 2: Backend Logic & Controller
1.  Buat `ProductController` untuk mengelola CRUD produk dan proses upload gambar.
2.  Buat `StockController` untuk melihat daftar persediaan per grade dan pemindahan lokasi rak.
3.  Buat `TransactionController` dengan validasi ketat serta pembungkusan dalam `DB::transaction()` agar transaksi stok dan entri keuangan selalu tersimpan bersamaan secara aman.
4.  Buat `FinancialController` untuk laporan arus kas.

### Tahap 3: Pembuatan Antarmuka Vue 3 (Inertia.js Pages)
1.  Desain halaman katalog **Product List** dengan grid modern dan modal untuk form input/edit.
2.  Desain halaman **Stock Inventory** dengan visualisasi tabel per Grade dan pencarian dinamis.
3.  Desain halaman **Transaction Logs** dengan badge berwarna untuk tipe transaksi (`masuk` berwarna hijau, `keluar` berwarna merah).
4.  Desain halaman **Financial Report** dengan integrasi grafik Chart.js yang interaktif.

### Tahap 4: Integrasi Fitur Scan Kamera (Capacitor)
1.  Buat halaman khusus mobile `/scan`.
2.  Tulis modul Javascript untuk mendeteksi apakah aplikasi berjalan di lingkungan Capacitor (mobile app) atau web browser biasa.
3.  Jika di mobile, gunakan `@capacitor/camera` untuk meminta izin kamera dan memicu pemindaian barcode.
4.  Jika di browser desktop, sediakan input manual berupa kolom teks SKU yang otomatis mendeteksi input dari mesin pemindai barcode fisik USB (USB Barcode Scanner).

### Tahap 5: Fitur Lanjutan (PDF, Excel & Real-time)
1.  Integrasikan event broadcasting di Laravel ketika mutasi stok terjadi, lalu dengarkan event tersebut di Vue menggunakan Laravel Echo / Pusher Client untuk memperbarui data di dashboard tanpa reload.
2.  Implementasikan ekspor data ke Excel menggunakan package `maatwebsite/excel`.
3.  Buat template surat jalan berdesain minimalis dan bersih di HTML, lalu konversikan menjadi file PDF unduhan menggunakan `DomPDF`.

---

## 6. MATRIKS RISIKO & MITIGASI

| Risiko | Dampak | Mitigasi |
| :--- | :--- | :--- |
| **Selisih Stok Akibat Balapan Transaksi (Race Condition)** | Tinggi | Menggunakan `DB::transaction` dan klausa `lockForUpdate()` di Laravel Eloquent saat memeriksa dan mengurangi stok di database. |
| **Ukuran Penyimpanan Server Membengkak Akibat Foto Kamera HP** | Sedang | Melakukan kompresi gambar di sisi server menggunakan library `Intervention/Image` sebelum disimpan ke direktori `storage/app/public`. |
| **Akses Kamera Diblokir Pengguna di Mobile** | Tinggi | Menyediakan alternatif input SKU manual yang cepat atau pencarian autocomplete jika izin kamera tidak diberikan. |
| **Keterlambatan Update Stok di Monitor Admin** | Rendah | Menggunakan Pusher WebSockets untuk sinkronisasi otomatis agar admin langsung mengetahui jika stok berkurang oleh operator di lapangan. |
