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
    },
    auditLogs: {
        type: Array,
        default: () => []
    },
    isAdmin: {
        type: Boolean,
        default: false
    },
    isFinance: {
        type: Boolean,
        default: false
    }
});

// Active Navigation Tab
const activeTab = ref('ledger');

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
    notes: ''
});

// Employees Reactive Database
const employees = ref([
    { id: 1, name: 'Budi Santoso', position: 'Operator Gudang', base_salary: 4500000, bank: 'BCA - 8920182739', status: 'Aktif' },
    { id: 2, name: 'Siti Aminah', position: 'Finance Specialist', base_salary: 5500000, bank: 'Mandiri - 132009827382', status: 'Aktif' },
    { id: 3, name: 'Rian Hidayat', position: 'Logistik & Kurir', base_salary: 4000000, bank: 'BRI - 0029384729103', status: 'Aktif' },
    { id: 4, name: 'Dewi Lestari', position: 'Supervisor Gudang', base_salary: 6500000, bank: 'BCA - 8920394820', status: 'Aktif' }
]);

const showEmployeeModal = ref(false);
const isEditingEmployee = ref(false);
const employeeForm = ref({
    id: null,
    name: '',
    position: 'Operator Gudang',
    base_salary: '',
    bank: '',
    status: 'Aktif'
});

const openEmployeeModal = (emp = null) => {
    if (emp) {
        isEditingEmployee.value = true;
        employeeForm.value = { ...emp };
    } else {
        isEditingEmployee.value = false;
        employeeForm.value = {
            id: null,
            name: '',
            position: 'Operator Gudang',
            base_salary: '',
            bank: '',
            status: 'Aktif'
        };
    }
    showEmployeeModal.value = true;
};

const saveEmployee = () => {
    if (!employeeForm.value.name || !employeeForm.value.base_salary) return;
    
    if (isEditingEmployee.value) {
        const index = employees.value.findIndex(e => e.id === employeeForm.value.id);
        if (index !== -1) {
            employees.value[index] = { ...employeeForm.value };
        }
    } else {
        const newId = employees.value.length ? Math.max(...employees.value.map(e => e.id)) + 1 : 1;
        employees.value.push({
            ...employeeForm.value,
            id: newId
        });
    }
    showEmployeeModal.value = false;
};

const deleteEmployee = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data karyawan ini?')) {
        employees.value = employees.value.filter(e => e.id !== id);
    }
};

// Payroll Reactive Processing
const showPayrollModal = ref(false);
const selectedEmployeeForPayroll = ref(null);
const payrollForm = ref({
    month: 'Mei 2026',
    bonus: 0,
    notes: ''
});

const openPayrollModal = (emp) => {
    selectedEmployeeForPayroll.value = emp;
    payrollForm.value = {
        month: 'Mei 2026',
        bonus: 0,
        notes: `Gaji Karyawan [${emp.name}] - Bulan Mei 2026`
    };
    showPayrollModal.value = true;
};

const submitPayroll = () => {
    const base = Number(selectedEmployeeForPayroll.value.base_salary);
    const bonus = Number(payrollForm.value.bonus) || 0;
    const total = base + bonus;

    form.type = 'pengeluaran';
    form.amount = total;
    form.date = new Date().toISOString().split('T')[0];
    form.notes = payrollForm.value.notes || `Gaji Karyawan [${selectedEmployeeForPayroll.value.name}] - ${payrollForm.value.month}`;

    form.post(route('financials.store'), {
        onSuccess: () => {
            showPayrollModal.value = false;
            activeTab.value = 'ledger'; // Switch back to Ledger view to see the new entry
            form.reset();
        }
    });
};

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

// Edit Ledger Entry State & Handlers
const showEditModal = ref(false);
const editForm = useForm({
    id: null,
    type: 'pengeluaran',
    amount: '',
    date: '',
    notes: ''
});

const openEditModal = (f) => {
    editForm.id = f.id;
    editForm.type = f.type;
    editForm.amount = f.amount;
    editForm.date = f.date;
    editForm.notes = f.notes || '';
    showEditModal.value = true;
};

const submitEdit = () => {
    editForm.put(route('financials.update', editForm.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        }
    });
};

const deleteFinancial = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus catatan keuangan Buku Besar ini? Tindakan ini akan dicatat dalam histori audit.')) {
        router.delete(route('financials.destroy', id));
    }
};

const clearAuditLogs = () => {
    if (confirm('Apakah Anda yakin ingin menghapus dan membersihkan seluruh riwayat audit keuangan ini secara permanen dari database?')) {
        router.post(route('financials.clear-logs'));
    }
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
                
                <!-- Sub-navigation Tabs (High Visual WOW) -->
                <div class="flex flex-wrap items-center p-1.5 bg-slate-100 rounded-2xl gap-1 border border-slate-200/50">
                    <button 
                        @click="activeTab = 'ledger'"
                        class="px-5 py-3 text-xs font-bold rounded-xl transition-all flex items-center gap-2"
                        :class="activeTab === 'ledger' ? 'bg-white text-slate-800 shadow-sm scale-[1.02]' : 'text-slate-500 hover:text-slate-850 hover:bg-slate-200/40'"
                    >
                        📂 Buku Besar (Ledger)
                    </button>
                    <button 
                        @click="activeTab = 'sheet'"
                        class="px-5 py-3 text-xs font-bold rounded-xl transition-all flex items-center gap-2"
                        :class="activeTab === 'sheet' ? 'bg-white text-slate-800 shadow-sm scale-[1.02]' : 'text-slate-500 hover:text-slate-850 hover:bg-slate-200/40'"
                    >
                        📊 Laporan Keuangan (Spreadsheet)
                    </button>
                    <button 
                        @click="activeTab = 'karyawan'"
                        class="px-5 py-3 text-xs font-bold rounded-xl transition-all flex items-center gap-2"
                        :class="activeTab === 'karyawan' ? 'bg-white text-slate-800 shadow-sm scale-[1.02]' : 'text-slate-500 hover:text-slate-850 hover:bg-slate-200/40'"
                    >
                        👥 Data Karyawan
                    </button>
                    <button 
                        @click="activeTab = 'payroll'"
                        class="px-5 py-3 text-xs font-bold rounded-xl transition-all flex items-center gap-2"
                        :class="activeTab === 'payroll' ? 'bg-white text-slate-800 shadow-sm scale-[1.02]' : 'text-slate-500 hover:text-slate-850 hover:bg-slate-200/40'"
                    >
                        💸 Penggajian Karyawan
                    </button>
                    <button 
                        v-if="isAdmin || isFinance"
                        @click="activeTab = 'audit'"
                        class="px-5 py-3 text-xs font-bold rounded-xl transition-all flex items-center gap-2"
                        :class="activeTab === 'audit' ? 'bg-white text-slate-800 shadow-sm scale-[1.02]' : 'text-slate-500 hover:text-slate-850 hover:bg-slate-200/40'"
                    >
                        📋 Histori Edit (Audit)
                    </button>
                </div>

                <!-- TAB 1: BUKU BESAR / LEDGER LOGS -->
                <div v-if="activeTab === 'ledger'" class="space-y-6 animate-[fadeIn_0.3s_ease]">
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
                                            <th v-if="isAdmin || isFinance" class="px-6 py-4 text-right">Tindakan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-sm">
                                        <tr v-if="financials.data.length === 0">
                                            <td :colspan="isAdmin || isFinance ? 6 : 5" class="text-center py-16 text-slate-400 font-medium">
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
                                                    <span class="font-bold text-slate-700">{{ f.notes || 'Penyesuaian Manual (Non-Barang)' }}</span>
                                                    <span class="text-xs text-slate-400 mt-0.5">Transaksi manual kas operasional</span>
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
                                            <td v-if="isAdmin || isFinance" class="px-6 py-4 text-right whitespace-nowrap">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button 
                                                        @click="openEditModal(f)"
                                                        class="p-1.5 text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors hover:scale-105 active:scale-95"
                                                        title="Edit Entri Keuangan"
                                                    >
                                                        ✏️
                                                    </button>
                                                    <button 
                                                        @click="deleteFinancial(f.id)"
                                                        class="p-1.5 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors hover:scale-105 active:scale-95"
                                                        title="Hapus Entri Keuangan"
                                                    >
                                                        🗑️
                                                    </button>
                                                </div>
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

                <!-- TAB 2: LAPORAN KEUANGAN (EXCEL SPREADSHEET STYLE) -->
                <div v-if="activeTab === 'sheet'" class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm animate-[fadeIn_0.3s_ease] space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="font-extrabold text-slate-800 text-lg flex items-center gap-2">
                                <span class="p-1 bg-emerald-100 text-emerald-700 rounded-lg text-sm">📊</span>
                                Interactive Excel Ledger Spreadsheet
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Pembukuan dengan antarmuka grid spreadsheet profesional yang terintegrasi penuh.</p>
                        </div>
                    </div>

                    <!-- Excel Menu Bar simulator (Visual WOW) -->
                    <div class="flex items-center gap-2 bg-slate-50 p-2 border border-slate-200 rounded-xl overflow-x-auto text-[11px] font-semibold text-slate-600 select-none">
                        <span class="px-2 py-1 bg-white rounded border border-slate-200 cursor-not-allowed">File</span>
                        <span class="px-2 py-1 bg-white rounded border border-slate-200 cursor-not-allowed text-emerald-700 font-extrabold">Edit</span>
                        <span class="px-2 py-1 bg-white rounded border border-slate-200 cursor-not-allowed">View</span>
                        <span class="px-2 py-1 bg-white rounded border border-slate-200 cursor-not-allowed">Format</span>
                        <div class="h-4 w-[1px] bg-slate-200 mx-1"></div>
                        <span class="p-1 hover:bg-slate-200 rounded cursor-not-allowed"><b>B</b></span>
                        <span class="p-1 hover:bg-slate-200 rounded cursor-not-allowed"><i>I</i></span>
                        <span class="p-1 hover:bg-slate-200 rounded cursor-not-allowed"><u>U</u></span>
                        <div class="h-4 w-[1px] bg-slate-200 mx-1"></div>
                        <span class="px-2 py-1 bg-white rounded border border-slate-200 cursor-not-allowed flex items-center gap-1">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> 100% Online
                        </span>
                    </div>

                    <!-- Formula Bar simulator -->
                    <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden text-xs bg-slate-50">
                        <div class="bg-slate-200/80 px-3.5 py-2 font-mono font-bold text-slate-500 border-r border-slate-200">fx</div>
                        <div class="px-4 py-2 font-mono text-indigo-700 font-extrabold w-full select-all overflow-x-auto whitespace-nowrap">
                            =SUM(PEMASUKAN: {{ formatCurrency(stats.total_income) }}) - SUM(PENGELUARAN: {{ formatCurrency(stats.total_expense) }}) = LABA_BERSIH: {{ formatCurrency(stats.net_profit) }}
                        </div>
                    </div>

                    <!-- Spreadsheet Table -->
                    <div class="overflow-x-auto border border-slate-200 rounded-2xl shadow-inner bg-slate-100 max-h-[500px]">
                        <table class="w-full text-xs font-mono border-collapse min-w-[800px] select-all">
                            <thead>
                                <tr class="bg-slate-100 text-slate-500 text-center font-bold border-b border-slate-200">
                                    <th class="w-10 bg-slate-200/60 border-r border-slate-200 text-center select-none py-1.5"></th>
                                    <th class="border-r border-slate-200 px-3 py-1.5 w-24">A</th>
                                    <th class="border-r border-slate-200 px-3 py-1.5 w-40">B</th>
                                    <th class="border-r border-slate-200 px-3 py-1.5 w-24">C</th>
                                    <th class="border-r border-slate-200 px-3 py-1.5">D</th>
                                    <th class="px-3 py-1.5 w-44">E</th>
                                </tr>
                                <tr class="bg-slate-100/50 text-slate-400 font-bold border-b border-slate-200 select-none">
                                    <th class="bg-slate-200/80 border-r border-slate-200 py-2"></th>
                                    <th class="border-r border-slate-250 px-3">REF_ID</th>
                                    <th class="border-r border-slate-250 px-3">TANGGAL</th>
                                    <th class="border-r border-slate-250 px-3">FLOW_TYPE</th>
                                    <th class="border-r border-slate-250 px-3 text-left">NAMA_TRANSAKSI_MUTASI</th>
                                    <th class="px-3 text-right">TOTAL_NOMINAL (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100 text-slate-700">
                                <tr v-if="financials.data.length === 0">
                                    <td class="bg-slate-100/50 border-r border-slate-200 text-center py-8 font-sans font-medium text-slate-400" colspan="6">
                                        Tidak ada entri data dalam Sheet.
                                    </td>
                                </tr>
                                <tr 
                                    v-for="(f, index) in financials.data" 
                                    :key="f.id"
                                    class="hover:bg-slate-50 transition-colors border-b border-slate-150"
                                >
                                    <td class="bg-slate-100/80 border-r border-slate-200 text-center py-2 select-none font-sans font-bold text-slate-400">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="border-r border-slate-150 px-3 text-center text-slate-400 select-all">
                                        #TX{{ f.id }}
                                    </td>
                                    <td class="border-r border-slate-150 px-3 text-center select-all">
                                        {{ f.date }}
                                    </td>
                                    <td class="border-r border-slate-150 px-3 text-center select-all font-bold">
                                        <span :class="f.type === 'pemasukan' ? 'text-emerald-600' : 'text-rose-500'">
                                            {{ f.type.toUpperCase() }}
                                        </span>
                                    </td>
                                    <td class="border-r border-slate-150 px-3 text-left select-all font-sans">
                                        {{ f.transaction ? `${f.transaction.product?.name} (Grade ${f.transaction.grade} - ${f.transaction.quantity} Pcs)` : (f.notes || 'Arus Kas Operasional') }}
                                    </td>
                                    <td 
                                        class="px-3 text-right select-all font-bold text-base"
                                        :class="f.type === 'pemasukan' ? 'text-indigo-600' : 'text-rose-500'"
                                    >
                                        {{ f.type === 'pemasukan' ? '+' : '-' }}{{ f.amount.toLocaleString('id-ID') }}
                                    </td>
                                </tr>
                                
                                <!-- Sheet Bottom Formulas (Excel double line borders) -->
                                <tr class="bg-emerald-50 font-bold border-t-2 border-emerald-300">
                                    <td class="bg-emerald-100/50 border-r border-slate-200 text-center py-2.5 font-sans text-slate-500 select-none">Σ</td>
                                    <td class="border-r border-emerald-100 px-3 text-center" colspan="2">FORMULA</td>
                                    <td class="border-r border-emerald-100 px-3 text-center">SUM_IN</td>
                                    <td class="border-r border-emerald-100 px-3 text-left font-sans text-emerald-800">Total Arus Pemasukan Bersih (Kredit)</td>
                                    <td class="px-3 text-right text-emerald-700 text-base">{{ stats.total_income.toLocaleString('id-ID') }}</td>
                                </tr>
                                <tr class="bg-rose-50 font-bold border-t border-rose-200">
                                    <td class="bg-rose-100/50 border-r border-slate-200 text-center py-2.5 font-sans text-slate-500 select-none">Σ</td>
                                    <td class="border-r border-rose-100 px-3 text-center" colspan="2">FORMULA</td>
                                    <td class="border-r border-rose-100 px-3 text-center">SUM_OUT</td>
                                    <td class="border-r border-rose-100 px-3 text-left font-sans text-rose-800">Total Beban Pengeluaran Operasional (Debet)</td>
                                    <td class="px-3 text-right text-rose-600 text-base">{{ stats.total_expense.toLocaleString('id-ID') }}</td>
                                </tr>
                                <tr class="bg-indigo-50 font-bold border-t-4 border-indigo-500 border-double">
                                    <td class="bg-indigo-100/50 border-r border-slate-200 text-center py-3 font-sans text-slate-500 select-none">🟰</td>
                                    <td class="border-r border-indigo-100 px-3 text-center" colspan="2">LABA</td>
                                    <td class="border-r border-indigo-100 px-3 text-center">NET_PROFIT</td>
                                    <td class="border-r border-indigo-100 px-3 text-left font-sans text-indigo-850">Hasil Laba Bersih Perusahaan (Selisih)</td>
                                    <td 
                                        class="px-3 text-right text-base text-lg font-black"
                                        :class="stats.net_profit >= 0 ? 'text-indigo-700' : 'text-rose-600'"
                                    >
                                        {{ stats.net_profit.toLocaleString('id-ID') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: DATA KARYAWAN -->
                <div v-if="activeTab === 'karyawan'" class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm animate-[fadeIn_0.3s_ease] space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="font-extrabold text-slate-800 text-lg flex items-center gap-2">
                                👥 Manajemen Karyawan Gudang & Kantor
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Kelola identitas, jabatan, detail rekening, dan gaji pokok karyawan Smart Inventory.</p>
                        </div>
                        <button 
                            @click="openEmployeeModal()"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-750 hover:shadow-lg transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                        >
                            ➕ Tambah Karyawan Baru
                        </button>
                    </div>

                    <!-- Employees List Table -->
                    <div class="border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50 font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                        <th class="px-6 py-4">Nama Lengkap</th>
                                        <th class="px-6 py-4">Posisi / Jabatan</th>
                                        <th class="px-6 py-4">Gaji Pokok (Bulanan)</th>
                                        <th class="px-6 py-4">Nomor Rekening Bank</th>
                                        <th class="px-6 py-4 text-center">Status</th>
                                        <th class="px-6 py-4 text-right">Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-650 font-medium">
                                    <tr v-if="employees.length === 0">
                                        <td colspan="6" class="text-center py-12 text-slate-400">Belum ada data karyawan gudang.</td>
                                    </tr>
                                    <tr v-for="emp in employees" :key="emp.id" class="hover:bg-slate-50/40 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">
                                                    {{ emp.name.split(' ').map(n=>n[0]).join('') }}
                                                </div>
                                                <span class="font-extrabold text-slate-800 text-sm">{{ emp.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span 
                                                class="inline-flex items-center px-2.5 py-1 text-[10px] font-black rounded-md uppercase"
                                                :class="{
                                                    'bg-indigo-50 text-indigo-700': emp.position === 'Supervisor Gudang',
                                                    'bg-emerald-50 text-emerald-700': emp.position === 'Operator Gudang',
                                                    'bg-purple-50 text-purple-700': emp.position === 'Finance Specialist',
                                                    'bg-amber-50 text-amber-700': emp.position === 'Logistik & Kurir'
                                                }"
                                            >
                                                {{ emp.position }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-bold text-slate-800">
                                            {{ formatCurrency(emp.base_salary) }}
                                        </td>
                                        <td class="px-6 py-4 font-mono font-semibold">
                                            {{ emp.bank }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                {{ emp.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right space-x-1.5">
                                            <button 
                                                @click="openEmployeeModal(emp)" 
                                                class="px-2.5 py-1 border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 text-indigo-600 rounded font-bold transition-all"
                                            >
                                                Edit
                                            </button>
                                            <button 
                                                @click="deleteEmployee(emp.id)" 
                                                class="px-2.5 py-1 border border-slate-200 hover:border-rose-300 hover:bg-rose-50 text-rose-500 rounded font-bold transition-all"
                                            >
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: PENGGAJIAN KARYAWAN -->
                <div v-if="activeTab === 'payroll'" class="space-y-6 animate-[fadeIn_0.3s_ease]">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- Left Panel: Disburse Salaries list (2 cols) -->
                        <div class="lg:col-span-2 bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-6">
                            <div>
                                <h3 class="font-extrabold text-slate-800 text-lg">Disbursement Gaji Karyawan</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Pilih karyawan aktif untuk memproses transfer gaji bulanan dan mencatatnya otomatis ke Ledger.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div 
                                    v-for="emp in employees.filter(e => e.status === 'Aktif')" 
                                    :key="emp.id"
                                    class="border border-slate-100 hover:border-slate-200 hover:shadow-md transition-all duration-300 rounded-2xl p-4 bg-slate-50/50 flex flex-col justify-between space-y-4"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ emp.name.split(' ').map(n=>n[0]).join('') }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-extrabold text-slate-800 text-sm leading-snug">{{ emp.name }}</span>
                                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">{{ emp.position }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-slate-100 pt-3 flex items-center justify-between gap-2">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] text-slate-400 font-bold">Gaji Pokok</span>
                                            <span class="text-sm font-extrabold text-slate-800">{{ formatCurrency(emp.base_salary) }}</span>
                                        </div>
                                        <button 
                                            @click="openPayrollModal(emp)"
                                            class="inline-flex items-center gap-1 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-sm hover:shadow shadow-emerald-100 transition-all duration-200"
                                        >
                                            💸 Bayar Gaji
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: Payroll History Ledger -->
                        <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
                            <div>
                                <h3 class="font-extrabold text-slate-800 text-lg">Histori Payroll Bulan Ini</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Catatan pencairan gaji karyawan terintegrasi ledger kas.</p>
                            </div>

                            <div class="space-y-3">
                                <template v-for="f in financials.data">
                                    <div 
                                        v-if="f.type === 'pengeluaran' && (f.notes && f.notes.includes('Gaji Karyawan'))"
                                        :key="f.id"
                                        class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl space-y-1.5"
                                    >
                                        <div class="flex items-center justify-between text-[10px] font-bold text-slate-400">
                                            <span>#TX{{ f.id }}</span>
                                            <span>{{ formatDate(f.date) }}</span>
                                        </div>
                                        <span class="text-xs font-bold text-slate-800 block">{{ f.notes }}</span>
                                        <span class="text-sm font-black text-rose-500 block text-right">-{{ formatCurrency(f.amount) }}</span>
                                    </div>
                                </template>
                                
                                <div class="text-center py-6 text-[11px] text-slate-400 font-medium bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                                    Histori pengeluaran payroll sinkron otomatis dengan Buku Besar utama.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- TAB 5: HISTORI EDIT / AUDIT LOGS -->
                <div v-if="activeTab === 'audit' && (isAdmin || isFinance)" class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm animate-[fadeIn_0.3s_ease] space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="font-extrabold text-slate-800 text-lg flex items-center gap-2">
                                📋 Log Audit Pembukuan Keuangan
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Histori lengkap pengeditan, pembuatan, dan penghapusan arus kas Buku Besar oleh Staf.
                            </p>
                        </div>
                        <!-- Admin Only Clear Button -->
                        <button 
                            v-if="isAdmin"
                            @click="clearAuditLogs"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 hover:text-rose-800 hover:shadow-lg transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                        >
                            🗑️ Bersihkan Semua Riwayat Audit
                        </button>
                    </div>

                    <!-- Timeline Feed -->
                    <div v-if="auditLogs.length === 0" class="text-center py-16 text-slate-400 font-medium border border-dashed border-slate-200 rounded-2xl">
                        Belum ada riwayat perubahan (pembuatan, edit, hapus) kas Buku Besar.
                    </div>
                    <div v-else class="relative border-l border-slate-100 pl-6 ml-4 space-y-8 py-2">
                        <div 
                            v-for="log in auditLogs" 
                            :key="log.id"
                            class="relative"
                        >
                            <!-- Timeline Dot Indicator -->
                            <span 
                                class="absolute -left-[31px] top-1 w-4 h-4 rounded-full border-4 border-white flex items-center justify-center shadow-sm"
                                :class="{
                                    'bg-emerald-500 ring-4 ring-emerald-50': log.action === 'STORE',
                                    'bg-amber-500 ring-4 ring-amber-50': log.action === 'EDIT',
                                    'bg-rose-500 ring-4 ring-rose-50': log.action === 'DELETE'
                                }"
                            ></span>

                            <!-- Log Content Card -->
                            <div class="bg-slate-50/50 border border-slate-100/70 rounded-2xl p-4 sm:p-5 space-y-4 hover:shadow-sm transition-shadow">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 pb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[10px]">
                                            {{ log.user ? log.user.name.split(' ').map(n=>n[0]).join('') : 'SYS' }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-slate-800">
                                                {{ log.user ? log.user.name : 'System' }} 
                                                <span class="text-[10px] text-slate-400 font-medium">({{ log.user && log.user.roles && log.user.roles[0] ? log.user.roles[0].name : (isAdmin ? 'Admin' : 'Finance') }})</span>
                                            </span>
                                            <span class="text-[9px] text-slate-400 mt-0.5">ID Ledger Keuangan: #{{ log.financial_id }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span 
                                            class="px-2 py-0.5 text-[9px] font-black rounded uppercase tracking-wider"
                                            :class="{
                                                'bg-emerald-100 text-emerald-800': log.action === 'STORE',
                                                'bg-amber-100 text-amber-800': log.action === 'EDIT',
                                                'bg-rose-100 text-rose-800': log.action === 'DELETE'
                                            }"
                                        >
                                            {{ log.action === 'STORE' ? 'Buat Baru' : (log.action === 'EDIT' ? 'Edit Data' : 'Hapus Data') }}
                                        </span>
                                        <span class="text-[10px] font-semibold text-slate-400 font-mono">
                                            {{ new Date(log.created_at).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Value snapshot side-by-side compare -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                                    <!-- Old Values Snapshot (For EDIT and DELETE) -->
                                    <div v-if="log.old_values" class="bg-white border border-slate-100 rounded-xl p-3 space-y-2">
                                        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider flex items-center gap-1">
                                            🔴 Data Lama (Sebelum)
                                        </span>
                                        <div class="space-y-1 text-[11px] text-slate-650">
                                            <div><strong>Tipe:</strong> <span class="font-bold uppercase" :class="log.old_values.type === 'pemasukan' ? 'text-emerald-600' : 'text-rose-500'">{{ log.old_values.type }}</span></div>
                                            <div><strong>Nominal:</strong> <span class="font-bold">{{ formatCurrency(log.old_values.amount) }}</span></div>
                                            <div><strong>Tanggal:</strong> {{ formatDate(log.old_values.date) }}</div>
                                            <div class="border-t border-slate-50 pt-1.5 mt-1 text-slate-400">
                                                <strong>Keterangan:</strong> {{ log.old_values.notes || 'Tanpa Keterangan' }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- New Values Snapshot (For STORE and EDIT) -->
                                    <div v-if="log.new_values" class="bg-white border border-slate-100 rounded-xl p-3 space-y-2">
                                        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider flex items-center gap-1">
                                            🟢 Data Baru (Sesudah)
                                        </span>
                                        <div class="space-y-1 text-[11px] text-slate-650">
                                            <div><strong>Tipe:</strong> <span class="font-bold uppercase" :class="log.new_values.type === 'pemasukan' ? 'text-emerald-600' : 'text-rose-500'">{{ log.new_values.type }}</span></div>
                                            <div><strong>Nominal:</strong> <span class="font-bold">{{ formatCurrency(log.new_values.amount) }}</span></div>
                                            <div><strong>Tanggal:</strong> {{ formatDate(log.new_values.date) }}</div>
                                            <div class="border-t border-slate-50 pt-1.5 mt-1 text-slate-400">
                                                <strong>Keterangan:</strong> {{ log.new_values.notes || 'Tanpa Keterangan' }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- If DELETE only has old values, show full single deleted card layout -->
                                    <div v-if="log.action === 'DELETE'" class="bg-rose-50/20 border border-rose-100 rounded-xl p-3 space-y-2 col-span-2">
                                        <span class="text-[10px] font-black uppercase text-rose-700 tracking-wider flex items-center gap-1">
                                            🚫 Data Terhapus
                                        </span>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px] text-slate-600">
                                            <div><strong>Tipe:</strong> <span class="font-bold text-rose-600 uppercase">{{ log.old_values.type }}</span></div>
                                            <div><strong>Nominal:</strong> <span class="font-black text-rose-600">{{ formatCurrency(log.old_values.amount) }}</span></div>
                                            <div><strong>Tanggal:</strong> {{ formatDate(log.old_values.date) }}</div>
                                        </div>
                                        <div class="border-t border-rose-100/50 pt-1.5 mt-1 text-[11px] text-slate-400">
                                            <strong>Keterangan Lama:</strong> {{ log.old_values.notes || 'Tanpa Keterangan' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                            min="1"
                            step="any"
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

        <!-- EMPLOYEE FORM MODAL -->
        <Modal :show="showEmployeeModal" @close="showEmployeeModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">
                    {{ isEditingEmployee ? 'Edit Data Karyawan' : 'Tambah Karyawan Baru' }}
                </h3>
                <p class="text-xs text-slate-400 mb-6">
                    Lengkapi informasi identitas, gaji pokok, dan rekening bank karyawan berikut.
                </p>

                <form @submit.prevent="saveEmployee" class="space-y-4">
                    <div>
                        <InputLabel for="emp_name" value="Nama Lengkap Karyawan" />
                        <TextInput 
                            id="emp_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="employeeForm.name"
                            required
                            placeholder="Contoh: Budi Santoso"
                        />
                    </div>

                    <div>
                        <InputLabel for="emp_position" value="Posisi / Jabatan" />
                        <select 
                            id="emp_position"
                            v-model="employeeForm.position"
                            class="w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            required
                        >
                            <option value="Operator Gudang">Operator Gudang</option>
                            <option value="Finance Specialist">Finance Specialist</option>
                            <option value="Supervisor Gudang">Supervisor Gudang</option>
                            <option value="Logistik & Kurir">Logistik & Kurir</option>
                        </select>
                    </div>

                    <div>
                        <InputLabel for="emp_salary" value="Gaji Pokok Bulanan (Rupiah)" />
                        <TextInput 
                            id="emp_salary"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="employeeForm.base_salary"
                            required
                            min="0"
                            placeholder="Contoh: 4500000"
                        />
                    </div>

                    <div>
                        <InputLabel for="emp_bank" value="Nomor Rekening & Nama Bank" />
                        <TextInput 
                            id="emp_bank"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="employeeForm.bank"
                            required
                            placeholder="Contoh: BCA - 8920182739"
                        />
                    </div>

                    <div>
                        <InputLabel for="emp_status" value="Status Karyawan" />
                        <select 
                            id="emp_status"
                            v-model="employeeForm.status"
                            class="w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            required
                        >
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>

                    <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                        <SecondaryButton @click="showEmployeeModal = false">Batal</SecondaryButton>
                        <PrimaryButton type="submit" class="bg-indigo-600 hover:bg-indigo-755 text-white">
                            {{ isEditingEmployee ? 'Simpan Perubahan' : 'Tambah Karyawan' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- PAYROLL DISBURSEMENT MODAL -->
        <Modal :show="showPayrollModal" @close="showPayrollModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">
                    💸 Proses Pencairan Gaji Karyawan
                </h3>
                <p class="text-xs text-slate-400 mb-6">
                    Lakukan verifikasi nominal pencairan gaji bulanan untuk karyawan berikut. Transaksi pengeluaran akan otomatis dibukukan di ledger kas.
                </p>

                <div v-if="selectedEmployeeForPayroll" class="p-4 bg-slate-50 border border-slate-100 rounded-2xl mb-4 space-y-2 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Karyawan:</span>
                        <span class="font-extrabold text-slate-800">{{ selectedEmployeeForPayroll.name }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Posisi:</span>
                        <span class="font-bold text-indigo-650 bg-indigo-50 px-2 py-0.5 rounded">{{ selectedEmployeeForPayroll.position }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Rekening Transfer:</span>
                        <span class="font-mono font-bold text-slate-650">{{ selectedEmployeeForPayroll.bank }}</span>
                    </div>
                </div>

                <form @submit.prevent="submitPayroll" class="space-y-4">
                    <div>
                        <InputLabel for="pay_month" value="Periode Pembayaran Gaji (Bulan & Tahun)" />
                        <TextInput 
                            id="pay_month"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="payrollForm.month"
                            required
                            placeholder="Contoh: Mei 2026"
                        />
                    </div>

                    <div>
                        <InputLabel for="pay_salary" value="Gaji Pokok (Rupiah - Tetap)" />
                        <TextInput 
                            id="pay_salary"
                            type="number"
                            class="mt-1 block w-full bg-slate-100/70 select-none cursor-not-allowed"
                            :value="selectedEmployeeForPayroll?.base_salary"
                            disabled
                        />
                    </div>

                    <div>
                        <InputLabel for="pay_bonus" value="Dana Bonus / Lembur Tambahan (Rupiah)" />
                        <TextInput 
                            id="pay_bonus"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="payrollForm.bonus"
                            min="0"
                            placeholder="0"
                        />
                    </div>

                    <div>
                        <InputLabel for="pay_notes" value="Keterangan Pembukuan Ledger" />
                        <textarea 
                            id="pay_notes"
                            v-model="payrollForm.notes"
                            rows="2"
                            class="w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            required
                        ></textarea>
                    </div>

                    <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                        <SecondaryButton @click="showPayrollModal = false">Batal</SecondaryButton>
                        <PrimaryButton type="submit" class="bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800" :disabled="form.processing">
                            💸 Cairkan & Catat Gaji
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- EDIT LEDGER ENTRY MODAL -->
        <Modal :show="showEditModal" @close="showEditModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">
                    ✏️ Edit Entri Buku Besar
                </h3>
                <p class="text-xs text-slate-400 mb-6">
                    Memperbarui data pembukuan Buku Besar. Perubahan ini akan otomatis mencatat riwayat audit dengan data lama dan data baru.
                </p>

                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <InputLabel for="edit_date" value="Tanggal Pembukuan" />
                        <TextInput 
                            id="edit_date" 
                            type="date" 
                            v-model="editForm.date" 
                            class="mt-1 block w-full text-xs" 
                            required 
                        />
                        <InputError :message="editForm.errors.date" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="edit_type" value="Tipe Arus Kas" />
                        <select 
                            id="edit_type" 
                            v-model="editForm.type"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-700 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-semibold"
                            required
                        >
                            <option value="pemasukan">Pemasukan (+)</option>
                            <option value="pengeluaran">Pengeluaran (-)</option>
                        </select>
                        <InputError :message="editForm.errors.type" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="edit_amount" value="Nominal Kas (Rupiah)" />
                        <TextInput 
                            id="edit_amount" 
                            type="number" 
                            step="any"
                            v-model="editForm.amount" 
                            class="mt-1 block w-full text-xs font-bold text-indigo-700" 
                            required 
                        />
                        <InputError :message="editForm.errors.amount" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="edit_notes" value="Keterangan / Catatan Transaksi" />
                        <textarea 
                            id="edit_notes" 
                            v-model="editForm.notes" 
                            rows="3"
                            class="mt-1 block w-full rounded-2xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-xs"
                            placeholder="Contoh: Biaya Listrik PLN Kantor Utama..."
                        ></textarea>
                        <InputError :message="editForm.errors.notes" class="mt-1" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <SecondaryButton @click="showEditModal = false">Batal</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': editForm.processing }" :disabled="editForm.processing">
                            Simpan Perubahan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
