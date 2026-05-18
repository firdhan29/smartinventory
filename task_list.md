# DAFTAR TUGAS PENGEMBANGAN (TASK CHECKLIST)
## SMART INVENTORY APP

Dokumen ini melacak kemajuan implementasi fitur aplikasi **Smart Inventory** langkah demi langkah. Setiap tahap diselesaikan secara bertahap dan terverifikasi sebelum berlanjut ke tahap berikutnya.

---

## 📌 TAHAP 1: DATABASE & MODEL (FONDASI)
*Tujuan: Membangun fondasi relasi data yang kuat, mempersiapkan struktur Eloquent Model, dan menyiapkan data dummy untuk pengujian.*

- [x] **1.1 Verifikasi Migrasi & Hapus Redundansi**
  - [x] Hapus migrasi custom `role_users` (Gunakan Spatie Permission sepenuhnya).
  - [x] Tambahkan kolom `kategori` pada tabel `products` agar inventaris terkelola dengan baik.
- [x] **1.2 Pembuatan Eloquent Model & Relasi**
  - [x] `Product.php` (Relasi ke Stock dan Transaction; casting harga).
  - [x] `Stock.php` (Relasi ke Product; filter grade A, B, Reject).
  - [x] `Transaction.php` (Relasi ke Product, User, dan Financial).
  - [x] `Financial.php` (Relasi ke Transaction; scope pemasukan & pengeluaran).
- [x] **1.3 Pembuatan Seeder & Dummy Data**
  - [x] Buat `ProductSeeder.php` untuk mengisi bermacam produk dummy dengan SKU unik.
  - [x] Buat `StockSeeder.php` untuk membagi kuantitas stok produk ke Grade A, B, dan Reject di rak lokasi berbeda.
  - [x] Update `DatabaseSeeder.php` untuk menjalankan seeder produk, stok, dan user default.
- [x] **1.4 Uji Coba Jalankan Migrasi & Seeder**
  - [x] Jalankan `php artisan migrate:fresh --seed` dan pastikan tidak ada error relasi.

---

## 📌 TAHAP 2: LOGIKA BACKEND & CONTROLLER (LOGIKA BISNIS)
*Tujuan: Membuat RESTful/Inertia Controller untuk menangani manipulasi data, transaksi stok aman (mencegah race condition), dan ledger keuangan otomatis.*

- [x] **2.1 ProductController (Katalog Produk)**
  - [x] CRUD produk dasar dengan validasi SKU unik.
  - [x] Logika upload foto produk + auto kompresi menggunakan `Intervention/Image`.
- [x] **2.2 StockController (Manajemen Stok & Rak)**
  - [x] Tampilan list stok per grade.
  - [x] Endpoint update posisi rak fisik barang (`rak_lokasi`).
  - [x] Logika pengecekan limit stok minimum (alert safety stock).
- [x] **2.3 TransactionController (Transaksi & Auto-Ledger Keuangan)**
  - [x] Form pencatatan Transaksi Masuk (`masuk`) dan Keluar (`keluar`).
  - [x] **Kritis**: Bungkus mutasi stok dalam `DB::transaction()` dan gunakan `lockForUpdate()` di Eloquent.
  - [x] Logika Auto-Financial Entry: Otomatis mencatat pengeluaran/pemasukan di tabel `financials` berdasarkan detail transaksi barang.
- [x] **2.4 FinancialController (Keuangan & Dasbor Data)**
  - [x] Endpoint laporan kas masuk/keluar.
  - [x] API penyedia data untuk grafik tren bulanan.
- [x] **2.5 Integrasi Rute di `web.php`**
  - [x] Daftarkan semua route grup di bawah middleware `auth` untuk keamanan.

---

## 📌 TAHAP 3: ANTARMUKA VUE 3 & STATE MANAGEMENT (FRONTEND)
*Tujuan: Membangun antarmuka pengguna yang sangat responsif, modern, dan interaktif menggunakan Vue 3, Inertia.js, dan Tailwind CSS.*

- [ ] **3.1 Dashboard.vue (Pusat Statistik)**
  - [ ] Tampilan card KPI: Total Nilai Aset, Transaksi Bulan Ini, Rasio Reject, Arus Kas Bersih.
  - [ ] Tampilan Grafik Interaktif menggunakan Chart.js (Tren stok dan diagram lingkaran Grade).
- [ ] **3.2 Products/Index.vue (Katalog Produk)**
  - [ ] Grid produk dengan foto, harga beli/jual, dan SKU.
  - [ ] Form modal pembuatan produk dengan auto-generator SKU.
- [ ] **3.3 Stocks/Index.vue (Peta Stok & Lokasi Rak)**
  - [ ] Tab kualitas stok: Grade A, Grade B, Reject.
  - [ ] Fitur update rak_lokasi langsung dari tabel (inline edit/modal).
- [ ] **3.4 Transactions/Index.vue (Log Mutasi Barang)**
  - [ ] Tabel log mutasi barang masuk/keluar dengan badge warna dinamis (Hijau = Masuk, Merah = Keluar).
  - [ ] Tombol transaksi cepat (Restock & Sell).
- [ ] **3.5 Financials/Index.vue (Arus Kas & Pembukuan)**
  - [ ] Tabel arus kas keuangan terperinci.
  - [ ] Filter keuangan berdasarkan tanggal dan jenis transaksi.

---

## 📌 TAHAP 4: INTEGRASI SCAN BARCODE & MOBILE SCANNING
*Tujuan: Membangun antarmuka ramah mobile yang terintegrasi dengan pemindai barcode kamera.*

- [ ] **4.1 Desain Halaman Mobile `/scan`**
  - [ ] UI minimalis ramah jempol (mobile-first design).
- [ ] **4.2 Integrasi Capacitor Camera**
  - [ ] Deteksi runtime platform (jika berjalan di web browser vs native mobile app).
  - [ ] Pemicu izin kamera smartphone untuk scan QR / Barcode menggunakan `@capacitor/camera`.
  - [ ] Auto-search SKU barang di database setelah scan berhasil.
- [ ] **4.3 Fallback Input Manual & USB Scanner**
  - [ ] Input teks SKU tersembunyi yang mendengarkan input keyboard dari mesin USB Scanner fisik untuk desktop.

---

## 📌 TAHAP 5: EVENT REAL-TIME & EKSPOR DOKUMEN (PDF/EXCEL)
*Tujuan: Menambahkan fitur premium berupa update stok instan tanpa refresh halaman dan ekspor dokumen formal.*

- [ ] **5.1 Real-time Sync (Pusher)**
  - [ ] Setup event broadcasting di Laravel saat transaksi sukses.
  - [ ] Gunakan Laravel Echo di Vue 3 untuk menangkap event dan meng-update stok di dasbor pengguna secara instan.
- [ ] **5.2 Generator Laporan PDF (DomPDF)**
  - [ ] Template HTML Surat Jalan / Invois minimalis.
  - [ ] Tombol "Cetak PDF" pada detail transaksi.
- [ ] **5.3 Ekspor Data Excel (Laravel Excel)**
  - [ ] Tombol ekspor laporan stock opname dan arus kas per bulan ke format `.xlsx`.

---

> [!TIP]  
> **Status Sekarang:** Siap memulai **Tahap 1: Database & Model (Fondasi)**.
