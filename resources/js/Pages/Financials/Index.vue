<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Line } from 'vue-chartjs';
import { 
    Chart as ChartJS, 
    Title, 
    Tooltip, 
    Legend, 
    LineElement, 
    PointElement, 
    LinearScale, 
    CategoryScale,
    Filler
} from 'chart.js';

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, LinearScale, CategoryScale, Filler);

const props = defineProps({
    financials: {
        type: Object,
        required: true
    },
    stats: {
        type: Object,
        required: true
    },
    chartData: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({ type: '', start_date: '', end_date: '' })
    }
});

// Filters State
const filterType = ref(props.filters.type || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

// Modals State
const showManualModal = ref(false);

const form = useForm({
    type: 'pengeluaran',
    amount: '',
    date: new Date().toISOString().split('T')[0],
    notes: '' // notes are validated in form, but controller safely skips saving notes columns
});

// Watch filters to reload page
watch([filterType, startDate, endDate], () => {
    router.get(
        route('financials.index'),
        { type: filterType.value, start_date: startDate.value, end_date: endDate.value },
        { preserveState: true, replace: true }
    );
});

const openManualModal = (type = 'pengeluaran') => {
    form.reset();
    form.clearErrors();
    form.type = type;
    form.date = new Date().toISOString().split('T')[0];
    showManualModal.value = true;
};

const submitManual = () => {
    form.post(route('financials.store'), {
        onSuccess: () => {
            showManualModal.value = false;
            form.reset();
        }
    });
};

// Cashflow Chart Configuration
const chartConfig = computed(() => {
    return {
        labels: props.chartData.labels,
        datasets: [
            {
                label: 'Pemasukan (Omset)',
                borderColor: '#10b981', // Emerald
                backgroundColor: 'rgba(16, 185, 129, 0.08)',
                borderWidth: 3,
                pointBackgroundColor: '#10b981',
                pointHoverRadius: 7,
                tension: 0.35,
                fill: true,
                data: props.chartData.income
            },
            {
                label: 'Pengeluaran (Modal & Ops)',
                borderColor: '#ef4444', // Red/Rose
                backgroundColor: 'rgba(239, 68, 68, 0.08)',
                borderWidth: 3,
                pointBackgroundColor: '#ef4444',
                pointHoverRadius: 7,
                tension: 0.35,
                fill: true,
                data: props.chartData.expense
            }
        ]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
            labels: {
                boxWidth: 15,
                font: {
                    family: "'Inter', sans-serif",
                    size: 12,
                    weight: 'bold'
                },
                padding: 20
            }
        },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            titleFont: { size: 13, weight: 'bold' },
            bodyFont: { size: 12 },
            padding: 12,
            cornerRadius: 8
        }
    },
    scales: {
        y: {
            grid: { color: 'rgba(241, 245, 249, 1)' },
            ticks: {
                callback: function(value) {
                    return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'k';
                },
                font: { family: "'Inter', sans-serif", size: 11 }
            }
        },
        x: {
            grid: { display: false },
            ticks: { font: { family: "'Inter', sans-serif", size: 11 } }
        }
    }
};

// Formatting helpers
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Arus Kas & Pembukuan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 tracking-tight">
                        Ledger Keuangan
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Pembukuan arus kas terintegrasi. Mutasi barang dan biaya operasional gudang otomatis dicatat di sini.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a 
                        :href="route('financials.export')"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl hover:bg-emerald-100/70 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                    >
                        📊 Unduh Excel
                    </a>
                    <button 
                        @click="openManualModal('pengeluaran')"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                    >
                        📸 Catat Ops
                    </button>
                    <button 
                        @click="openManualModal('pemasukan')"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-xl hover:bg-indigo-100/70 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                    >
                        💰 Pemasukan Lainnya
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 bg-slate-50 min-h-[calc(100vh-140px)]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                
                <!-- Financial Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <!-- Income Stats Card -->
                    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-50 rounded-full group-hover:scale-125 transition-all duration-500 opacity-60"></div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Pemasukan</span>
                        <h3 class="text-3xl font-extrabold text-slate-800 mt-3 tracking-tight">{{ formatCurrency(stats.total_income) }}</h3>
                        <p class="text-xs text-emerald-600 mt-1 font-semibold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Dari Penjualan & Lainnya
                        </p>
                    </div>

                    <!-- Expense Stats Card -->
                    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-rose-50 rounded-full group-hover:scale-125 transition-all duration-500 opacity-60"></div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Pengeluaran</span>
                        <h3 class="text-3xl font-extrabold text-slate-800 mt-3 tracking-tight">{{ formatCurrency(stats.total_expense) }}</h3>
                        <p class="text-xs text-rose-500 mt-1 font-medium flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            Modal Barang & Ops Gudang
                        </p>
                    </div>

                    <!-- Net Profit Card (Highly Visual WOW) -->
                    <div 
                        class="border rounded-2xl p-5 shadow-lg relative overflow-hidden group"
                        :class="stats.net_profit >= 0 ? 'bg-gradient-to-br from-indigo-900 to-slate-900 border-indigo-950 shadow-indigo-900/10' : 'bg-gradient-to-br from-rose-900 to-slate-900 border-rose-950 shadow-rose-900/10'"
                    >
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white rounded-full group-hover:scale-125 transition-all duration-500 opacity-10"></div>
                        <span class="text-xs font-bold text-indigo-200 uppercase tracking-wider block">Selisih Untung Riil (Laba Bersih)</span>
                        <h3 class="text-3xl font-extrabold text-white mt-3 tracking-tight">{{ formatCurrency(stats.net_profit) }}</h3>
                        <p class="text-xs text-indigo-300 mt-1 font-semibold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full" :class="stats.net_profit >= 0 ? 'bg-emerald-400' : 'bg-rose-400'"></span>
                            {{ stats.net_profit >= 0 ? 'Surplus Finansial' : 'Defisit Anggaran' }}
                        </p>
                    </div>
                </div>

                <!-- Cashflow Line Chart -->
                <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-slate-800 text-lg">Analisis Arus Kas Bulanan</h3>
                    <p class="text-xs text-slate-400 mb-6">Laporan tren perbandingan pemasukan vs pengeluaran dari 6 bulan terakhir.</p>
                    <div class="h-80 relative">
                        <Line 
                            v-if="chartData.labels.length > 0"
                            :data="chartConfig" 
                            :options="chartOptions" 
                        />
                        <div v-else class="text-center py-16 text-slate-400 font-medium">
                            Belum ada data bulanan untuk digambarkan ke dalam grafik.
                        </div>
                    </div>
                </div>

                <!-- Filters & Ledger Logs -->
                <div class="space-y-4">
                    <div class="bg-white p-4 border border-slate-100 rounded-2xl shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                        <h3 class="font-bold text-slate-800 text-base">Riwayat Buku Besar</h3>
                        
                        <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                            <!-- Type Filter -->
                            <select 
                                v-model="filterType"
                                class="w-full sm:w-40 px-3 py-2 bg-slate-50 border border-slate-100 rounded-xl text-xs text-slate-700 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-semibold"
                            >
                                <option value="">Semua Jenis</option>
                                <option value="pemasukan">Pemasukan (+)</option>
                                <option value="pengeluaran">Pengeluaran (-)</option>
                            </select>

                            <!-- Date Range Filters -->
                            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                                <input 
                                    v-model="startDate"
                                    type="date"
                                    class="w-full sm:w-36 px-3 py-2 bg-slate-50 border border-slate-100 rounded-xl text-xs text-slate-700 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-semibold"
                                />
                                <span class="text-slate-400 text-xs font-bold">s/d</span>
                                <input 
                                    v-model="endDate"
                                    type="date"
                                    class="w-full sm:w-36 px-3 py-2 bg-slate-50 border border-slate-100 rounded-xl text-xs text-slate-700 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-semibold"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[700px]">
                                <thead>
                                    <tr class="bg-slate-50/50 text-slate-400 text-xs uppercase tracking-wider font-bold border-b border-slate-100">
                                        <th class="px-6 py-4">Tanggal Pembukuan</th>
                                        <th class="px-6 py-4">Tipe Ledger</th>
                                        <th class="px-6 py-4">Deskripsi / Mutasi Barang</th>
                                        <th class="px-6 py-4">Grade & Qty</th>
                                        <th class="px-6 py-4 text-right">Nominal Arus Kas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    <tr v-if="financials.data.length === 0">
                                        <td colspan="5" class="text-center py-16 text-slate-400 font-medium">
                                            Tidak ada entri pembukuan yang sesuai filter/tanggal.
                                        </td>
                                    </tr>
                                    <tr 
                                        v-for="f in financials.data" 
                                        :key="f.id"
                                        class="hover:bg-slate-50/30 transition-colors"
                                    >
                                        <td class="px-6 py-4 text-slate-500 whitespace-nowrap font-medium">
                                            {{ formatDate(f.date) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span 
                                                class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-extrabold rounded-full uppercase"
                                                :class="f.type === 'pemasukan' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100'"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full" :class="f.type === 'pemasukan' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                                {{ f.type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div v-if="f.transaction" class="flex flex-col">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-slate-700">Stock {{ f.transaction.type === 'masuk' ? 'Restocked' : 'Sold Out' }}</span>
                                                    <span class="text-[10px] text-slate-400 font-mono">Invois #TX{{ f.transaction.id }}</span>
                                                </div>
                                                <span class="text-xs text-slate-500 mt-0.5">{{ f.transaction.product?.name }} [{{ f.transaction.product?.sku }}]</span>
                                            </div>
                                            <div v-else class="flex flex-col">
                                                <span class="font-bold text-slate-700">Penyesuaian Manual (Non-Barang)</span>
                                                <span class="text-xs text-slate-400 mt-0.5">Biaya operasional atau pemasukan manual</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div v-if="f.transaction" class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-700 text-xs font-bold rounded">
                                                    Grade {{ f.transaction.grade }}
                                                </span>
                                                <span class="text-slate-600 font-semibold">{{ f.transaction.quantity }} Pcs</span>
                                            </div>
                                            <span v-else class="text-slate-400">-</span>
                                        </td>
                                        <td 
                                            class="px-6 py-4 text-right font-black text-base whitespace-nowrap"
                                            :class="f.type === 'pemasukan' ? 'text-indigo-600' : 'text-rose-500'"
                                        >
                                            <span v-if="f.type === 'pemasukan'">+</span>
                                            <span v-else>-</span>
                                            {{ formatCurrency(f.amount) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="financials.links.length > 3" class="flex justify-center py-5 border-t border-slate-100">
                            <nav class="flex items-center gap-1">
                                <template v-for="(link, key) in financials.links" :key="key">
                                    <span 
                                        v-if="link.url === null"
                                        v-html="link.label"
                                        class="px-3.5 py-2 text-xs text-slate-400 bg-white border border-slate-100 rounded-lg select-none"
                                    ></span>
                                    <Link
                                        v-else
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-3.5 py-2 text-xs font-semibold rounded-lg border transition-all"
                                        :class="link.active ? 'bg-indigo-600 text-white border-indigo-600 shadow shadow-indigo-100' : 'bg-white text-slate-600 border-slate-100 hover:bg-slate-50'"
                                    />
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- MANUAL LEDGER ENTRY MODAL -->
        <Modal :show="showManualModal" @close="showManualModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">
                    Catat Ledger Manual ({{ form.type === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran' }})
                </h3>
                <p class="text-xs text-slate-400 mb-6">
                    Gunakan modal ini untuk mencatat biaya non-barang seperti gaji karyawan, listrik, sewa gudang, atau pemasukan lainnya.
                </p>

                <form @submit.prevent="submitManual" class="space-y-4">
                    <div>
                        <InputLabel for="manual_date" value="Tanggal Pembukuan" />
                        <TextInput 
                            id="manual_date"
                            type="date"
                            class="mt-1 block w-full"
                            v-model="form.date"
                            required
                        />
                        <InputError class="mt-1" :message="form.errors.date" />
                    </div>

                    <div>
                        <InputLabel for="manual_amount" value="Nominal Dana (Rupiah)" />
                        <TextInput 
                            id="manual_amount"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.amount"
                            required
                            min="0.01"
                            placeholder="Contoh: 1500000"
                        />
                        <InputError class="mt-1" :message="form.errors.amount" />
                    </div>

                    <div>
                        <InputLabel for="manual_notes" value="Keterangan Rincian (Notes)" />
                        <textarea 
                            id="manual_notes"
                            v-model="form.notes"
                            rows="3"
                            class="w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            placeholder="Contoh: Biaya bayar tagihan listrik PLN bulan Mei 2026..."
                            required
                        ></textarea>
                        <InputError class="mt-1" :message="form.errors.notes" />
                    </div>

                    <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                        <SecondaryButton @click="showManualModal = false">Batal</SecondaryButton>
                        <PrimaryButton 
                            :class="[form.type === 'pemasukan' ? 'bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800' : 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800', { 'opacity-25': form.processing }]" 
                            :disabled="form.processing"
                        >
                            Catat Ledger
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
