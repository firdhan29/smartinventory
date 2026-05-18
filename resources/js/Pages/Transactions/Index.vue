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

const props = defineProps({
    transactions: {
        type: Object,
        required: true
    },
    products: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({ search: '', type: '', grade: '' })
    }
});

// Search & Filters State
const search = ref(props.filters.search || '');
const filterType = ref(props.filters.type || '');
const filterGrade = ref(props.filters.grade || '');

// Modal State
const showCreateModal = ref(false);

const form = useForm({
    product_id: '',
    type: 'masuk',
    grade: 'A',
    quantity: 1,
    harga_aktual: '',
    notes: ''
});

// Watch inputs to trigger search/filters
let searchTimeout;
watch([search, filterType, filterGrade], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('transactions.index'),
            { search: search.value, type: filterType.value, grade: filterGrade.value },
            { preserveState: true, replace: true }
        );
    }, 300);
});

// Find selected product to auto-populate prices
const selectedProductDetails = computed(() => {
    if (!form.product_id) return null;
    return props.products.find(p => p.id === form.product_id);
});

// Watch product and type selections to auto-set default actual price
watch([() => form.product_id, () => form.type], () => {
    const details = selectedProductDetails.value;
    if (details) {
        if (form.type === 'masuk') {
            form.harga_aktual = parseFloat(details.harga_beli);
        } else {
            form.harga_aktual = parseFloat(details.harga_jual);
        }
    } else {
        form.harga_aktual = '';
    }
});

const openCreateModal = (type = 'masuk') => {
    form.reset();
    form.clearErrors();
    form.type = type;
    showCreateModal.value = true;
};

const submitCreate = () => {
    form.post(route('transactions.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
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
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Log Mutasi Barang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 tracking-tight">
                        Mutasi Stok Barang
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Catat aktivitas pengeluaran stok (Penjualan/Keluar) dan pemasukan stok (Pembelian/Masuk) berskala aman.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        @click="openCreateModal('masuk')"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl hover:bg-emerald-100/70 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-emerald-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Restock (Barang Masuk)
                    </button>
                    <button 
                        @click="openCreateModal('keluar')"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100/70 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-rose-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>
                        Jual (Barang Keluar)
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 bg-slate-50 min-h-[calc(100vh-140px)]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                
                <!-- Search & Filters -->
                <div class="bg-white p-4 border border-slate-100 rounded-2xl shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="relative w-full md:w-80">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                            </svg>
                        </span>
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Cari SKU, nama produk, catatan..." 
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        />
                    </div>

                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <select 
                            v-model="filterType"
                            class="w-full md:w-44 px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-sm text-slate-700 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        >
                            <option value="">Semua Tipe</option>
                            <option value="masuk">Barang Masuk</option>
                            <option value="keluar">Barang Keluar</option>
                        </select>

                        <select 
                            v-model="filterGrade"
                            class="w-full md:w-44 px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-sm text-slate-700 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        >
                            <option value="">Semua Grade</option>
                            <option value="A">Grade A (Prima)</option>
                            <option value="B">Grade B (Defect)</option>
                            <option value="Reject">Reject (Rusak)</option>
                        </select>
                    </div>
                </div>

                <!-- Mutation Logs Table -->
                <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-400 text-xs uppercase tracking-wider font-bold border-b border-slate-100">
                                    <th class="px-6 py-4">Waktu & Tanggal</th>
                                    <th class="px-6 py-4">Informasi Produk</th>
                                    <th class="px-6 py-4">Tipe Mutasi</th>
                                    <th class="px-6 py-4">Grade & Qty</th>
                                    <th class="px-6 py-4">Harga Aktual</th>
                                    <th class="px-6 py-4">Subtotal Keuangan</th>
                                    <th class="px-6 py-4">Notes / Keterangan</th>
                                    <th class="px-6 py-4">Operator</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="9" class="text-center py-16 text-slate-400 font-medium">
                                        Belum ada log mutasi barang tercatat dalam sistem.
                                    </td>
                                </tr>
                                <tr 
                                    v-for="tx in transactions.data" 
                                    :key="tx.id"
                                    class="hover:bg-slate-50/30 transition-colors"
                                >
                                    <td class="px-6 py-4 text-slate-500 whitespace-nowrap font-medium">
                                        {{ formatDate(tx.created_at) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-700">{{ tx.product.name }}</span>
                                            <span class="text-xs font-mono font-bold text-indigo-600 mt-0.5">{{ tx.product.sku }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span 
                                            class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-extrabold rounded-full uppercase"
                                            :class="tx.type === 'masuk' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100'"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full" :class="tx.type === 'masuk' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                            {{ tx.type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span 
                                                class="inline-flex items-center justify-center w-6 h-6 text-xs font-extrabold rounded"
                                                :class="{
                                                    'bg-indigo-50 text-indigo-700 border border-indigo-100': tx.grade === 'A',
                                                    'bg-amber-50 text-amber-700 border border-amber-100': tx.grade === 'B',
                                                    'bg-rose-50 text-rose-700 border border-rose-100': tx.grade === 'Reject'
                                                }"
                                            >
                                                {{ tx.grade }}
                                            </span>
                                            <span class="font-bold text-slate-800">{{ tx.quantity }} pcs</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 font-bold whitespace-nowrap">
                                        {{ formatCurrency(tx.harga_aktual) }}
                                    </td>
                                    <td class="px-6 py-4 font-black whitespace-nowrap" :class="tx.type === 'keluar' ? 'text-indigo-600' : 'text-rose-500'">
                                        <span v-if="tx.type === 'keluar'">+</span>
                                        <span v-else>-</span>
                                        {{ formatCurrency(tx.quantity * tx.harga_aktual) }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 max-w-xs truncate" :title="tx.notes">
                                        {{ tx.notes || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-700 font-semibold">
                                        {{ tx.user ? tx.user.name : 'System' }}
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <a 
                                            :href="route('transactions.pdf', tx.id)"
                                            target="_blank"
                                            class="inline-flex items-center justify-center gap-1 px-3 py-1.5 border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-800 rounded-lg transition-all"
                                        >
                                            📄 Cetak PDF
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transactions.links.length > 3" class="flex justify-center py-5 border-t border-slate-100">
                        <nav class="flex items-center gap-1">
                            <template v-for="(link, key) in transactions.links" :key="key">
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

        <!-- CREATE TRANSACTION FORM MODAL -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">
                    Catat Transaksi {{ form.type === 'masuk' ? 'Barang Masuk' : 'Barang Keluar' }}
                </h3>
                <p class="text-xs text-slate-400 mb-6">
                    Aktivitas mutasi ini akan mengubah stok gudang dan mencatat transaksi ke pembukuan keuangan secara otomatis.
                </p>

                <!-- Custom Error Message from Controller DB::transaction catch -->
                <div 
                    v-if="form.errors.error" 
                    class="p-4 bg-rose-50 border border-rose-100 text-rose-700 text-xs font-bold rounded-xl mb-5 flex items-start gap-2 animate-pulse"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 shrink-0 mt-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" />
                    </svg>
                    <span>{{ form.errors.error }}</span>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <InputLabel for="product_id" value="Pilih Produk" />
                        <select 
                            id="product_id"
                            v-model="form.product_id"
                            class="w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="" disabled>-- Pilih Produk di Katalog --</option>
                            <option v-for="p in products" :key="p.id" :value="p.id">
                                {{ p.name }} [{{ p.sku }}]
                            </option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.product_id" />
                    </div>

                    <!-- Selected Product Helper details -->
                    <div 
                        v-if="selectedProductDetails" 
                        class="p-3 bg-slate-50 border border-slate-100 rounded-xl grid grid-cols-2 gap-2 text-xs"
                    >
                        <div>
                            <span class="text-slate-400 block font-semibold uppercase">Modal Beli:</span>
                            <span class="font-bold text-slate-700">{{ formatCurrency(selectedProductDetails.harga_beli) }}</span>
                        </div>
                        <div>
                            <span class="text-indigo-400 block font-semibold uppercase">Patokan Jual:</span>
                            <span class="font-bold text-indigo-700">{{ formatCurrency(selectedProductDetails.harga_jual) }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="grade" value="Kualitas (Grade)" />
                            <select 
                                id="grade"
                                v-model="form.grade"
                                class="w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="A">Grade A (Prima)</option>
                                <option value="B">Grade B (Defect)</option>
                                <option value="Reject">Reject (Rusak)</option>
                            </select>
                            <InputError class="mt-1" :message="form.errors.grade" />
                        </div>
                        
                        <div>
                            <InputLabel for="quantity" value="Jumlah Pcs (Qty)" />
                            <TextInput 
                                id="quantity"
                                type="number"
                                class="mt-1 block w-full"
                                v-model="form.quantity"
                                min="1"
                                required
                            />
                            <InputError class="mt-1" :message="form.errors.quantity" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="harga_aktual" :value="form.type === 'masuk' ? 'Harga Beli Riil (Per Pcs)' : 'Harga Jual Riil (Per Pcs)'" />
                        <TextInput 
                            id="harga_aktual"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.harga_aktual"
                            required
                            min="0"
                            placeholder="Masukkan harga kesepakatan"
                        />
                        <InputError class="mt-1" :message="form.errors.harga_aktual" />
                        <p class="text-[10px] text-slate-400 mt-1">Mengisi harga riil di sini sangat penting karena sistem pembukuan keuangan akan menghitung profit berdasarkan selisih harga ini.</p>
                    </div>

                    <div>
                        <InputLabel for="notes" value="Keterangan / Notes (Opsional)" />
                        <textarea 
                            id="notes"
                            v-model="form.notes"
                            rows="2"
                            class="w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            placeholder="Catat nomor invois, PO supplier, atau nama pelanggan..."
                        ></textarea>
                        <InputError class="mt-1" :message="form.errors.notes" />
                    </div>

                    <!-- Total Ledger Preview -->
                    <div 
                        v-if="form.quantity && form.harga_aktual" 
                        class="p-4 rounded-xl border flex justify-between items-center"
                        :class="form.type === 'masuk' ? 'bg-rose-50/50 border-rose-100 text-rose-800' : 'bg-indigo-50/50 border-indigo-100 text-indigo-800'"
                    >
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider block opacity-75">Prediksi Ledgers:</span>
                            <span class="text-xs font-semibold">Tipe: {{ form.type === 'masuk' ? 'Pengeluaran (Beli)' : 'Pemasukan (Jual)' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-black block">
                                {{ formatCurrency(form.quantity * form.harga_aktual) }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                        <SecondaryButton @click="showCreateModal = false">Batal</SecondaryButton>
                        <PrimaryButton 
                            :class="[form.type === 'masuk' ? 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 ring-emerald-500' : 'bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 ring-indigo-500', { 'opacity-25': form.processing }]" 
                            :disabled="form.processing"
                        >
                            Simpan Mutasi
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
