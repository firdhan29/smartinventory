<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Doughnut } from 'vue-chartjs';
import { 
    Chart as ChartJS, 
    Title, 
    Tooltip, 
    Legend, 
    ArcElement, 
    CategoryScale, 
    LinearScale 
} from 'chart.js';
import { computed } from 'vue';

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale);

const props = defineProps({
    stats: {
        type: Object,
        required: true
    },
    recentTransactions: {
        type: Array,
        default: () => []
    },
    lowStocks: {
        type: Array,
        default: () => []
    },
    chartStockGrades: {
        type: Object,
        required: true
    }
});

// Format Currency to IDR (Indonesian Rupiah)
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};

// Format Date
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Chart.js Configuration
const chartData = computed(() => {
    return {
        labels: props.chartStockGrades.labels,
        datasets: [
            {
                backgroundColor: [
                    'rgba(99, 102, 241, 0.85)', // Grade A (Indigo)
                    'rgba(245, 158, 11, 0.85)', // Grade B (Amber)
                    'rgba(239, 68, 68, 0.85)'   // Reject (Red)
                ],
                hoverBackgroundColor: [
                    'rgba(99, 102, 241, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(239, 68, 68, 1)'
                ],
                borderWidth: 2,
                borderColor: '#ffffff',
                data: props.chartStockGrades.data
            }
        ]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                boxWidth: 12,
                font: {
                    family: "'Inter', sans-serif",
                    size: 12
                },
                padding: 15
            }
        },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            titleFont: { size: 13, weight: 'bold' },
            bodyFont: { size: 12 },
            padding: 10,
            cornerRadius: 8
        }
    },
    cutout: '65%'
};
</script>

<template>
    <Head title="Dashboard Smart Inventory" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 tracking-tight">
                        Ringkasan Inventaris
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Selamat datang kembali, <span class="font-semibold text-indigo-600">{{ $page.props.auth.user.name }}</span>! Kelola pergerakan barang dan keuangan real-time di sini.
                    </p>
                </div>
                <div class="flex items-center gap-2" v-if="$page.props.auth.user.roles && ($page.props.auth.user.roles.includes('admin') || $page.props.auth.user.roles.includes('operator'))">
                    <Link 
                        :href="route('scan')"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-xl shadow-lg shadow-indigo-100 hover:shadow-indigo-200 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>
                        Mulai Scan
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8 bg-slate-50 min-h-[calc(100vh-140px)]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
                
                <!-- KPI Widgets Section -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 animate-fade-in"
                    :class="($page.props.auth.user.roles && ($page.props.auth.user.roles.includes('admin') || $page.props.auth.user.roles.includes('finance'))) ? 'lg:grid-cols-5' : 'lg:grid-cols-2'"
                >
                    <!-- Total Products Card -->
                    <div class="relative overflow-hidden bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 group">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-50 rounded-full group-hover:scale-125 transition-all duration-500 opacity-60"></div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total SKU</span>
                            <div class="p-2.5 bg-indigo-50 rounded-xl text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-3xl font-extrabold text-slate-800">{{ stats.total_products }}</h3>
                            <p class="text-xs text-slate-500 mt-1 font-medium">Item Terdaftar</p>
                        </div>
                    </div>

                    <!-- Total Stock Card -->
                    <div class="relative overflow-hidden bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 group">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-purple-50 rounded-full group-hover:scale-125 transition-all duration-500 opacity-60"></div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Stok</span>
                            <div class="p-2.5 bg-purple-50 rounded-xl text-purple-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-3xl font-extrabold text-slate-800">{{ stats.total_stock || 0 }}</h3>
                            <p class="text-xs text-slate-500 mt-1 font-medium">Pcs Barang Gudang</p>
                        </div>
                    </div>

                    <!-- Total Income Card -->
                    <div v-if="$page.props.auth.user.roles && ($page.props.auth.user.roles.includes('admin') || $page.props.auth.user.roles.includes('finance'))"
                        class="relative overflow-hidden bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 group"
                    >
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-50 rounded-full group-hover:scale-125 transition-all duration-500 opacity-60"></div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pemasukan</span>
                            <div class="p-2.5 bg-emerald-50 rounded-xl text-emerald-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-xl font-bold text-slate-800 tracking-tight whitespace-nowrap">{{ formatCurrency(stats.total_income) }}</h3>
                            <p class="text-xs text-emerald-600 mt-1 font-semibold">Omset Terakumulasi</p>
                        </div>
                    </div>

                    <!-- Total Expense Card -->
                    <div v-if="$page.props.auth.user.roles && ($page.props.auth.user.roles.includes('admin') || $page.props.auth.user.roles.includes('finance'))"
                        class="relative overflow-hidden bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 group"
                    >
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-rose-50 rounded-full group-hover:scale-125 transition-all duration-500 opacity-60"></div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pengeluaran</span>
                            <div class="p-2.5 bg-rose-50 rounded-xl text-rose-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.306-4.306a11.95 11.95 0 0 1 5.814 5.519l2.74 1.22m0 0-5.94 2.281m5.94-2.28-2.28 5.941" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-xl font-bold text-slate-800 tracking-tight whitespace-nowrap">{{ formatCurrency(stats.total_expense) }}</h3>
                            <p class="text-xs text-rose-500 mt-1 font-medium">Beli Barang & Ops</p>
                        </div>
                    </div>

                    <!-- Net Profit Card (Full Gradient for visual WOW factor) -->
                    <div v-if="$page.props.auth.user.roles && ($page.props.auth.user.roles.includes('admin') || $page.props.auth.user.roles.includes('finance'))"
                        class="relative overflow-hidden bg-gradient-to-br from-indigo-900 to-slate-900 border border-indigo-950 rounded-2xl p-5 shadow-lg shadow-indigo-900/10 group col-span-1 sm:col-span-2 lg:col-span-1"
                    >
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-800 rounded-full group-hover:scale-125 transition-all duration-500 opacity-30"></div>
                        <div class="flex items-center justify-between relative z-10">
                            <span class="text-xs font-bold text-indigo-200 uppercase tracking-wider">Laba Bersih</span>
                            <div class="p-2.5 bg-indigo-800/40 border border-indigo-700/30 rounded-xl text-indigo-300">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 relative z-10">
                            <h3 class="text-xl font-extrabold text-white tracking-tight whitespace-nowrap" :class="{'text-rose-300': stats.net_profit < 0}">
                                {{ formatCurrency(stats.net_profit) }}
                            </h3>
                            <p class="text-xs text-indigo-300 mt-1 font-medium">Laba Aktual Bersih</p>
                        </div>
                    </div>
                </div>

                <!-- Lower Section: Recent Logs & Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Left: Recent Transactions (Span 2) -->
                    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden lg:col-span-2">
                        <div class="px-6 py-5 border-b border-slate-50 flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg">Log Mutasi Terkini</h3>
                                <p class="text-xs text-slate-400">5 riwayat perpindahan barang gudang terbaru</p>
                            </div>
                            <Link 
                                :href="route('transactions.index')" 
                                class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors flex items-center gap-1"
                            >
                                Lihat Semua
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </Link>
                        </div>

                        <div class="divide-y divide-slate-100 overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[500px]">
                                <thead>
                                    <tr class="bg-slate-50/50 text-slate-400 text-xs uppercase tracking-wider font-bold">
                                        <th class="px-6 py-3">Produk</th>
                                        <th class="px-6 py-3">Tipe</th>
                                        <th class="px-6 py-3">Grade / Qty</th>
                                        <th class="px-6 py-3">Waktu</th>
                                        <th class="px-6 py-3">Oleh</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    <tr v-if="recentTransactions.length === 0">
                                        <td colspan="5" class="text-center py-8 text-slate-400 font-medium">
                                            Belum ada log transaksi.
                                        </td>
                                    </tr>
                                    <tr 
                                        v-for="tx in recentTransactions" 
                                        :key="tx.id"
                                        class="hover:bg-slate-50/50 transition-colors"
                                    >
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-700">{{ tx.product.name }}</span>
                                                <span class="text-xs font-mono text-indigo-600">{{ tx.product.sku }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span 
                                                class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full capitalize"
                                                :class="tx.type === 'masuk' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100'"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full" :class="tx.type === 'masuk' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                                Barang {{ tx.type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <span 
                                                    class="inline-flex items-center justify-center w-6 h-6 text-xs font-extrabold rounded bg-slate-100 text-slate-700 border border-slate-200"
                                                    :class="{
                                                        'bg-indigo-50 text-indigo-700 border-indigo-100': tx.grade === 'A',
                                                        'bg-amber-50 text-amber-700 border-amber-100': tx.grade === 'B',
                                                        'bg-rose-50 text-rose-700 border-rose-100': tx.grade === 'Reject'
                                                    }"
                                                >
                                                    {{ tx.grade }}
                                                </span>
                                                <span class="font-semibold text-slate-800">{{ tx.quantity }} pcs</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                                            {{ formatDate(tx.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 font-medium">
                                            {{ tx.user ? tx.user.name : 'System' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Right: Charts & Stock Alerts (Span 1) -->
                    <div class="space-y-8 lg:col-span-1">
                        
                        <!-- Stock Distribution Doughnut Chart -->
                        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-6">
                            <h3 class="font-bold text-slate-800 text-lg">Distribusi Kualitas Stok</h3>
                            <p class="text-xs text-slate-400 mb-6">Porsi stok berdasarkan Grading Product</p>
                            
                            <div class="relative h-56 flex items-center justify-center">
                                <Doughnut 
                                    v-if="chartStockGrades.data.some(d => d > 0)"
                                    :data="chartData" 
                                    :options="chartOptions" 
                                />
                                <div v-else class="text-center text-slate-400 text-sm font-medium py-12">
                                    Tidak ada data persediaan stok untuk divisualisasikan.
                                </div>
                            </div>
                        </div>

                        <!-- Safety Stock Alert Card -->
                        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-bold text-slate-800 text-lg">Alert Safety Stock</h3>
                                <span class="bg-rose-50 text-rose-700 text-xs font-bold px-2 py-0.5 rounded border border-rose-100">
                                    {{ lowStocks.length }} SKU
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mb-4">Produk Grade A di bawah batas pengaman (≤ 5 Pcs)</p>
                            
                            <div class="space-y-3 max-h-[220px] overflow-y-auto pr-1">
                                <div v-if="lowStocks.length === 0" class="text-center py-6 text-slate-400 text-sm font-medium">
                                    ✅ Semua stok Grade A aman di atas batas minimum.
                                </div>
                                <div 
                                    v-for="stk in lowStocks" 
                                    :key="stk.id"
                                    class="flex items-center justify-between p-3 rounded-xl border border-slate-100 hover:bg-rose-50/20 hover:border-rose-100 transition-all duration-300"
                                >
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 leading-snug">{{ stk.product.name }}</span>
                                        <span class="text-xs font-mono text-slate-400">{{ stk.product.sku }} | {{ stk.rak_lokasi || 'Tanpa Rak' }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-extrabold text-rose-600 block">{{ stk.quantity }} Pcs</span>
                                        <span class="text-[10px] font-bold text-rose-400 uppercase tracking-wider">Restock Segera</span>
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
