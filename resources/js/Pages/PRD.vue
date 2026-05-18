<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const activeTab = ref('vision');

const prdTabs = [
    { id: 'vision', name: '📋 Ringkasan & Visi', icon: 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z' },
    { id: 'audit', name: '🔍 Audit & Analisis Pro-Contra', icon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L17 17m-2-5a2 2 0 11-4 0 2 2 0 014 0z' },
    { id: 'features', name: '🚀 Kebutuhan & Fitur Utama', icon: 'M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.64 8.38a14.98 14.98 0 00-6.16 12.12A14.98 14.98 0 0015.59 14.37z' },
    { id: 'architecture', name: '🛠 Rencana & Arsitektur Eloquent', icon: 'M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077l1.41-.513m14.095-5.13l1.41-.513M5.106 17.785l1.15-.827m11.379-8.16l1.15-.827M8.14 21.27l.707-1.03m7.747-11.276l.707-1.03M12 3v1.5m0 15V21m-3.077-8.457l-.513-1.41m5.13 14.095l-.513-1.41M6.215 17.785l-.827-1.15m8.16-11.379l-.827-1.15M2.73 15.86l-1.03-.707m11.276-7.747l-1.03-.707' },
    { id: 'risks', name: '⚠️ Matriks Risiko & Keamanan', icon: 'M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z' }
];

// Interactive Checklist to wow the user
const checklist = ref([
    { text: 'Integrasi Multi-Grade Stock System (A, B, Reject)', checked: true },
    { text: 'Shelf Coordinates Locator (rak_lokasi)', checked: true },
    { text: 'Auto-generating SKU based on category & timestamp', checked: true },
    { text: 'Fast transaction logs linked directly with financials', checked: true },
    { text: 'Automated Invoice / Surat Jalan PDF generation (DomPDF)', checked: true },
    { text: 'Financial spreadsheet download (Excel export)', checked: true },
    { text: 'Multi-device responsive camera scanning interface (Capacitor)', checked: true },
    { text: 'Role-Based Access Control middleware constraints (Admin vs. Operator)', checked: true },
    { text: 'Transaction safety lock mitigation (lockForUpdate DB transaction)', checked: true },
    { text: 'Automatic high-res image upload compression (Intervention)', checked: true }
]);

const checkedCount = computed(() => checklist.value.filter(c => c.checked).length);
const progressPercent = computed(() => Math.round((checkedCount.value / checklist.value.length) * 100));
</script>

<template>
    <Head title="Aplikasi PRD Blueprint" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 tracking-tight">
                        Dokumen Blueprint & PRD Aplikasi
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Akses spesifikasi kebutuhan produk, arsitektur database, dan laporan kesiapan implementasi sistem secara langsung.
                    </p>
                </div>
                <!-- Premium Progress Badge -->
                <div class="flex items-center gap-3 bg-white p-3 border border-slate-100 rounded-2xl shadow-sm">
                    <div class="flex flex-col text-right">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Kesiapan Fitur</span>
                        <span class="text-lg font-black text-indigo-600 leading-none">{{ progressPercent }}% Terpenuhi</span>
                    </div>
                    <div class="relative w-12 h-12 flex items-center justify-center">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="24" cy="24" r="20" stroke="#f1f5f9" stroke-width="4" fill="transparent" />
                            <circle cx="24" cy="24" r="20" stroke="#4f46e5" stroke-width="4" fill="transparent" 
                                    :stroke-dasharray="2 * Math.PI * 20" 
                                    :stroke-dashoffset="2 * Math.PI * 20 * (1 - progressPercent/100)" />
                        </svg>
                        <span class="absolute text-[10px] font-bold text-slate-700">✓</span>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-8 bg-slate-50 min-h-[calc(100vh-140px)]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <!-- Left Sidebar Navigation Tabs -->
                    <div class="space-y-3 lg:col-span-1">
                        <button 
                            v-for="tab in prdTabs" 
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            class="w-full text-left px-4 py-3 rounded-2xl border transition-all text-xs font-bold flex items-center gap-3"
                            :class="activeTab === tab.id 
                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-100 scale-[1.02]' 
                                : 'bg-white text-slate-600 border-slate-200/60 hover:bg-slate-50 hover:text-slate-800'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon" />
                            </svg>
                            <span>{{ tab.name }}</span>
                        </button>

                        <!-- Interactive Requirements Checklist Card inside Sidebar -->
                        <div class="bg-white border border-slate-200/60 rounded-3xl p-5 shadow-sm space-y-4">
                            <h4 class="font-extrabold text-slate-800 text-sm border-b border-slate-100 pb-2 flex items-center gap-1.5">
                                📋 Checklist Kesiapan
                            </h4>
                            <div class="space-y-2.5">
                                <label 
                                    v-for="(item, key) in checklist" 
                                    :key="key" 
                                    class="flex items-start gap-2.5 cursor-pointer text-[11px] text-slate-600 hover:text-slate-900"
                                >
                                    <input 
                                        type="checkbox" 
                                        v-model="item.checked" 
                                        class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5"
                                    />
                                    <span :class="item.checked ? 'line-through text-slate-400 font-medium' : 'font-semibold'">{{ item.text }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Right Main Content Panel -->
                    <div class="lg:col-span-3 bg-white border border-slate-200/60 rounded-3xl p-6 md:p-8 shadow-sm min-h-[500px]">
                        
                        <!-- TAB 1: VISION & INTRODUCTION -->
                        <div v-if="activeTab === 'vision'" class="space-y-6 animate-[fadeIn_0.3s_ease]">
                            <div>
                                <span class="bg-indigo-50 text-indigo-700 font-black text-[10px] tracking-wider uppercase px-2.5 py-1 rounded-md">Visi Produk & Pendahuluan</span>
                                <h3 class="text-2xl font-black text-slate-800 mt-2">1. Pengenalan Smart Inventory App</h3>
                                <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                                    Dokumen ini disusun sebagai panduan cetak biru (*blueprint*) pengembangan aplikasi **Smart Inventory**, yang memadukan keunggulan performa backend Laravel 12 dengan reaktivitas Vue 3 (via Inertia.js) dan fleksibilitas *hybrid mobile app* menggunakan Capacitor.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
                                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="p-1 bg-indigo-100 text-indigo-700 rounded-lg">⭐</span>
                                        <h4 class="font-extrabold text-slate-800 text-xs">Grading Product System</h4>
                                    </div>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">Memisahkan stok berdasarkan kualitas barang ke dalam 3 jenis: Grade A, Grade B, dan Reject.</p>
                                </div>
                                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="p-1 bg-indigo-100 text-indigo-700 rounded-lg">📍</span>
                                        <h4 class="font-extrabold text-slate-800 text-xs">Location-Aware Stocking</h4>
                                    </div>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">Memetakan letak fisik barang di rak penyimpanan gudang secara presisi (`rak_lokasi`).</p>
                                </div>
                                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="p-1 bg-indigo-100 text-indigo-700 rounded-lg">📷</span>
                                        <h4 class="font-extrabold text-slate-800 text-xs">Mobile Barcode Scanning</h4>
                                    </div>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">Menggunakan kamera perangkat mobile secara native (via Capacitor) untuk mempercepat proses scan SKU.</p>
                                </div>
                                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="p-1 bg-indigo-100 text-indigo-700 rounded-lg">💰</span>
                                        <h4 class="font-extrabold text-slate-800 text-xs">Auto Financial Ledger</h4>
                                    </div>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">Menghubungkan setiap mutasi barang masuk/keluar ke arus kas buku besar perusahaan secara otomatis.</p>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: AUDIT & ANALYSIS -->
                        <div v-if="activeTab === 'audit'" class="space-y-6 animate-[fadeIn_0.3s_ease]">
                            <div>
                                <span class="bg-indigo-50 text-indigo-700 font-black text-[10px] tracking-wider uppercase px-2.5 py-1 rounded-md">Analisis Kondisi & Pros-Cons</span>
                                <h3 class="text-2xl font-black text-slate-800 mt-2">2. Analisis Audit Awal & Kesenjangan</h3>
                                <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                                    Sebelum pengerjaan penuh, kami melakukan audit mendalam pada struktur migrasi dan dependencies untuk mendeteksi celah desain yang perlu diperbaiki.
                                </p>
                            </div>

                            <div class="space-y-4">
                                <h4 class="font-extrabold text-slate-800 text-sm flex items-center gap-2 border-b border-slate-100 pb-2">
                                    <span class="text-emerald-500">✔</span> Kelebihan Sistem (Pros)
                                </h4>
                                <ul class="space-y-2.5 text-xs text-slate-500 pl-4 list-disc leading-relaxed">
                                    <li><strong class="text-slate-700">Arsitektur Super Cepat:</strong> Laravel 12 + Inertia.js + Vue 3 memberikan sensasi aplikasi SPA yang responsif tanpa perlu overhead pengelolaan REST API terpisah.</li>
                                    <li><strong class="text-slate-700">Capacitor Hybrid Mobile:</strong> Satu basis kode frontend web yang sama dapat dibungkus menjadi aplikasi native Android/iOS untuk akses kamera.</li>
                                    <li><strong class="text-slate-700">Audit Finansial Terikat:</strong> Penjualan (keluar) dan Restock (masuk) diikat secara transaksional dengan entri keuangan untuk menghitung laba bersih.</li>
                                </ul>

                                <h4 class="font-extrabold text-slate-800 text-sm flex items-center gap-2 border-b border-slate-100 pt-4 pb-2">
                                    <span class="text-amber-500">⚠</span> Celah Desain & Mitigasi (Cons)
                                </h4>
                                <ul class="space-y-2.5 text-xs text-slate-500 pl-4 list-disc leading-relaxed">
                                    <li><strong class="text-slate-700">Ketiadaan Kategori Produk:</strong> <span class="text-rose-600 font-bold">[DISELESAIKAN]</span> Kami memodifikasi tabel dengan menambahkan kolom string kategori agar catalog dapat difilter rapi.</li>
                                    <li><strong class="text-slate-700">Redundansi Otorisasi Pengguna:</strong> <span class="text-rose-600 font-bold">[DISELESAIKAN]</span> Membuang tabel kustom `role_users` dan murni mengimplementasikan library Spatie Laravel Permission yang stabil.</li>
                                    <li><strong class="text-slate-700">Keamanan Penyimpanan:</strong> <span class="text-rose-600 font-bold">[DISELESAIKAN]</span> Menggunakan Intervention Image untuk melakukan kompresi asinkron di server sebelum foto disimpan.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- TAB 3: FUNCTIONAL FEATURES -->
                        <div v-if="activeTab === 'features'" class="space-y-6 animate-[fadeIn_0.3s_ease]">
                            <div>
                                <span class="bg-indigo-50 text-indigo-700 font-black text-[10px] tracking-wider uppercase px-2.5 py-1 rounded-md">Kebutuhan Fungsional</span>
                                <h3 class="text-2xl font-black text-slate-800 mt-2">3. Spesifikasi Kebutuhan & Fitur</h3>
                            </div>

                            <div class="space-y-4">
                                <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/50 space-y-2">
                                    <h4 class="font-extrabold text-slate-800 text-xs flex items-center justify-between">
                                        <span>F1. SKU & Foto Produk Katalog</span>
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[9px] font-black rounded uppercase">SELESAI</span>
                                    </h4>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">CRUD katalog produk dengan auto-generator SKU berbasis inisial kategori serta kompresi otomatis foto produk.</p>
                                </div>
                                <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/50 space-y-2">
                                    <h4 class="font-extrabold text-slate-800 text-xs flex items-center justify-between">
                                        <span>F2. Multi-Grade Stock & Rak Locator</span>
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[9px] font-black rounded uppercase">SELESAI</span>
                                    </h4>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">Display persediaan terbagi per tab kualitas Grade A, B, dan Reject. Koordinat Rak Lokasi yang dinamis, disertai visual alert jika stok di bawah batas aman.</p>
                                </div>
                                <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/50 space-y-2">
                                    <h4 class="font-extrabold text-slate-800 text-xs flex items-center justify-between">
                                        <span>F3. Kamera Pemindai Mobile & USB</span>
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[9px] font-black rounded uppercase">SELESAI</span>
                                    </h4>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">Pemindai QR/Barcode responsif. Memanfaatkan Capacitor native API di mobile, dan listener keyboard instan untuk barcode scanner fisik USB di browser.</p>
                                </div>
                                <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/50 space-y-2">
                                    <h4 class="font-extrabold text-slate-800 text-xs flex items-center justify-between">
                                        <span>F4. Auto-Ledger Buku Besar & Invois PDF</span>
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[9px] font-black rounded uppercase">SELESAI</span>
                                    </h4>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">Mengunci transaksi di level database. Setiap mutasi barang otomatis menerbitkan entri debit/kredit keuangan, lengkap dengan ekspor Surat Jalan PDF (DomPDF).</p>
                                </div>
                                <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/50 space-y-2">
                                    <h4 class="font-extrabold text-slate-800 text-xs flex items-center justify-between">
                                        <span>F5. Dashboard Analitik & Excel Ekspor</span>
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[9px] font-black rounded uppercase">SELESAI</span>
                                    </h4>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">Visualisasi perbandingan omset dan pengeluaran bulanan. Pencatatan manual kas operasional, dan fitur unduh riwayat Excel Buku Besar secara instan.</p>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: ARCHITECTURE & ELOQUENT MAP -->
                        <div v-if="activeTab === 'architecture'" class="space-y-6 animate-[fadeIn_0.3s_ease]">
                            <div>
                                <span class="bg-indigo-50 text-indigo-700 font-black text-[10px] tracking-wider uppercase px-2.5 py-1 rounded-md">Arsitektur Kode & Database</span>
                                <h3 class="text-2xl font-black text-slate-800 mt-2">4. Pemetaan Relasi Eloquent</h3>
                                <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                                    Untuk menjamin integritas data pergudangan berskala tinggi, seluruh model dihubungkan dengan relasi database Eloquent yang presisi.
                                </p>
                            </div>

                            <!-- Interactive Eloquent Schema Map (Tailwind Wow visual style) -->
                            <div class="p-6 bg-slate-900 text-white rounded-3xl space-y-4 shadow-xl font-mono text-xs overflow-x-auto">
                                <div class="text-indigo-400 font-bold border-b border-slate-800 pb-2">// ELOQUENT RELATIONSHIP ARCHITECTURE</div>
                                <div class="space-y-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-indigo-300 font-bold">Product</span>
                                        <span class="text-slate-500">--------▶</span>
                                        <span class="text-emerald-300">hasMany</span>
                                        <span class="text-slate-500">----▶</span>
                                        <span class="text-indigo-300">Stock</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-indigo-300 font-bold">Product</span>
                                        <span class="text-slate-500">--------▶</span>
                                        <span class="text-emerald-300">hasMany</span>
                                        <span class="text-slate-500">----▶</span>
                                        <span class="text-indigo-300">Transaction</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-indigo-300 font-bold">Transaction</span>
                                        <span class="text-slate-500">----▶</span>
                                        <span class="text-emerald-300">belongsTo</span>
                                        <span class="text-slate-500">--▶</span>
                                        <span class="text-indigo-300">User / Operator</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-indigo-300 font-bold">Transaction</span>
                                        <span class="text-slate-500">----▶</span>
                                        <span class="text-emerald-300">hasOne</span>
                                        <span class="text-slate-500 font-bold text-amber-300">[Auto-Ledger]</span>
                                        <span class="text-indigo-300">Financial</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3.5">
                                <h4 class="font-extrabold text-slate-800 text-sm">Validasi Konkurensi & Keandalan</h4>
                                <p class="text-xs text-slate-400 leading-relaxed">
                                    Saat restock atau penjualan terjadi di gudang oleh operator, sistem menggunakan transaksi database tertutup (`DB::transaction`) dan melakukan penguncian baris (`lockForUpdate()`) di SQL untuk mencegah terjadinya pembacaan stok yang kadaluarsa (*race conditions*) atau *overselling*.
                                </p>
                            </div>
                        </div>

                        <!-- TAB 5: RISKS & MITIGATIONS -->
                        <div v-if="activeTab === 'risks'" class="space-y-6 animate-[fadeIn_0.3s_ease]">
                            <div>
                                <span class="bg-indigo-50 text-indigo-700 font-black text-[10px] tracking-wider uppercase px-2.5 py-1 rounded-md">Risiko & Otorisasi Pengguna</span>
                                <h3 class="text-2xl font-black text-slate-800 mt-2">5. Pengelolaan Risiko Keamanan & RBAC</h3>
                            </div>

                            <!-- Role Matrix Table -->
                            <div class="overflow-hidden border border-slate-100 rounded-2xl shadow-sm">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-slate-50 font-bold text-slate-500">
                                            <th class="p-3.5">Fitur Sistem</th>
                                            <th class="p-3.5">Role Admin</th>
                                            <th class="p-3.5">Role Operator Gudang</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-slate-500 font-medium">
                                        <tr>
                                            <td class="p-3.5 font-bold text-slate-700">Manajemen Katalog Produk</td>
                                            <td class="p-3.5 text-emerald-600 font-bold">Ya (CRUD)</td>
                                            <td class="p-3.5 text-rose-500 font-bold">Tidak</td>
                                        </tr>
                                        <tr>
                                            <td class="p-3.5 font-bold text-slate-700">Melihat Jumlah Stok & Letak Rak</td>
                                            <td class="p-3.5 text-emerald-600 font-bold">Ya</td>
                                            <td class="p-3.5 text-emerald-600 font-bold">Ya</td>
                                        </tr>
                                        <tr>
                                            <td class="p-3.5 font-bold text-slate-700">Mencatat Mutasi Barang (Scan/Input)</td>
                                            <td class="p-3.5 text-emerald-600 font-bold">Ya</td>
                                            <td class="p-3.5 text-emerald-600 font-bold">Ya</td>
                                        </tr>
                                        <tr>
                                            <td class="p-3.5 font-bold text-slate-700">Akses Arus Keuangan (Charts/Profit)</td>
                                            <td class="p-3.5 text-emerald-600 font-bold">Ya (Akses Penuh)</td>
                                            <td class="p-3.5 text-rose-500 font-bold">Tidak (Terproteksi Middleware)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="space-y-4">
                                <h4 class="font-extrabold text-slate-800 text-sm">Matriks Mitigasi Risiko Teknis</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="p-4 rounded-2xl border border-rose-100 bg-rose-50/20 space-y-1">
                                        <span class="text-xs font-bold text-rose-700 block">Balapan Transaksi (Race Condition)</span>
                                        <p class="text-[10px] text-slate-400 leading-relaxed">Mitigasi: Menggunakan penguncian level SQL `lockForUpdate()` di Eloquent sebelum memvalidasi dan memotong stok.</p>
                                    </div>
                                    <div class="p-4 rounded-2xl border border-indigo-100 bg-indigo-50/20 space-y-1">
                                        <span class="text-xs font-bold text-indigo-700 block">Ukuran File Gambar Besar</span>
                                        <p class="text-[10px] text-slate-400 leading-relaxed">Mitigasi: Intervention Image memotong resolusi foto dan mengompres ukuran menjadi di bawah 500kb saat tersimpan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
