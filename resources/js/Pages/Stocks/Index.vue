<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    stocks: {
        type: Object,
        required: true
    },
    lowStocks: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({ grade: '', search: '' })
    }
});

// Search & Filter State
const search = ref(props.filters.search || '');
const activeGrade = ref(props.filters.grade || '');

// Edit Stock Modal
const showEditModal = ref(false);
const selectedStock = ref(null);

const form = useForm({
    rak_lokasi: '',
    quantity: 0
});

// Watch and trigger Inertia reload on search/filter changes
let searchTimeout;
watch([search, activeGrade], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('stocks.index'),
            { search: search.value, grade: activeGrade.value },
            { preserveState: true, replace: true }
        );
    }, 300);
});

// Toggle Grade Filter via Tab
const setGradeFilter = (grade) => {
    activeGrade.value = activeGrade.value === grade ? '' : grade;
};

const openEditModal = (stock) => {
    selectedStock.value = stock;
    form.reset();
    form.clearErrors();
    form.rak_lokasi = stock.rak_lokasi || 'Belum Dialokasikan';
    form.quantity = stock.quantity;
    showEditModal.value = true;
};

const submitUpdate = () => {
    form.put(route('stocks.update', selectedStock.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Stok & Rak Gudang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 tracking-tight">
                        Stok & Lokasi Rak
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Pantau sebaran persediaan barang berdasarkan kualitas (Grade) dan koordinat letak fisik di gudang.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8 bg-slate-50 min-h-[calc(100vh-140px)]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                
                <!-- Safety Stock Alerts (Collapsible/Dismissable banner in high visual WOW style) -->
                <div 
                    v-if="lowStocks.length > 0"
                    class="bg-gradient-to-r from-rose-500 to-red-600 rounded-2xl p-5 text-white shadow-lg shadow-rose-500/10 flex flex-col md:flex-row gap-4 items-center justify-between"
                >
                    <div class="flex items-center gap-3.5">
                        <div class="p-2 bg-white/10 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 animate-bounce">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg leading-tight">Peringatan: Safety Stock Menipis!</h4>
                            <p class="text-xs text-rose-100 mt-0.5">Terdapat {{ lowStocks.length }} produk Grade A yang berada di bawah atau sama dengan batas pengaman (5 Pcs).</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 max-w-xs overflow-x-auto whitespace-nowrap bg-white/10 p-2 rounded-xl border border-white/5 scrollbar-none">
                        <div 
                            v-for="stk in lowStocks" 
                            :key="stk.id" 
                            class="bg-white/10 px-3 py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 border border-white/10"
                        >
                            <span>{{ stk.product.sku }}</span>
                            <span class="bg-white text-rose-600 px-1 rounded font-black">{{ stk.quantity }}</span>
                        </div>
                    </div>
                </div>

                <!-- Filters & Grid Tabs -->
                <div class="bg-white p-4 border border-slate-100 rounded-2xl shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                    <!-- Grade Filter Tabs -->
                    <div class="flex items-center p-1 bg-slate-100 rounded-xl w-full md:w-auto overflow-x-auto">
                        <button 
                            @click="setGradeFilter('')"
                            class="flex-1 md:flex-none px-5 py-2 text-xs font-bold rounded-lg transition-all"
                            :class="activeGrade === '' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                        >
                            Semua Grade
                        </button>
                        <button 
                            @click="setGradeFilter('A')"
                            class="flex-1 md:flex-none px-5 py-2 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-1.5"
                            :class="activeGrade === 'A' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-100' : 'text-slate-500 hover:text-slate-800'"
                        >
                            <span class="w-1.5 h-1.5 rounded-full" :class="activeGrade === 'A' ? 'bg-white' : 'bg-indigo-500'"></span>
                            Grade A (Prima)
                        </button>
                        <button 
                            @click="setGradeFilter('B')"
                            class="flex-1 md:flex-none px-5 py-2 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-1.5"
                            :class="activeGrade === 'B' ? 'bg-amber-500 text-white shadow-md shadow-amber-100' : 'text-slate-500 hover:text-slate-800'"
                        >
                            <span class="w-1.5 h-1.5 rounded-full" :class="activeGrade === 'B' ? 'bg-white' : 'bg-amber-500'"></span>
                            Grade B (Defect)
                        </button>
                        <button 
                            @click="setGradeFilter('Reject')"
                            class="flex-1 md:flex-none px-5 py-2 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-1.5"
                            :class="activeGrade === 'Reject' ? 'bg-rose-600 text-white shadow-md shadow-rose-100' : 'text-slate-500 hover:text-slate-800'"
                        >
                            <span class="w-1.5 h-1.5 rounded-full" :class="activeGrade === 'Reject' ? 'bg-white' : 'bg-rose-500'"></span>
                            Reject (Rusak)
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-80">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                            </svg>
                        </span>
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Cari SKU, nama, lokasi..." 
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        />
                    </div>
                </div>

                <!-- Stock List Table -->
                <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-400 text-xs uppercase tracking-wider font-bold border-b border-slate-100">
                                    <th class="px-6 py-4">Informasi Produk</th>
                                    <th class="px-6 py-4">Kualitas (Grade)</th>
                                    <th class="px-6 py-4">Kuantitas Stok</th>
                                    <th class="px-6 py-4">Lokasi Rak Fisik</th>
                                    <th class="px-6 py-4">Terakhir Diupdate</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr v-if="stocks.data.length === 0">
                                    <td colspan="6" class="text-center py-16 text-slate-400 font-medium">
                                        Tidak ada data stok yang sesuai dengan filter pencarian.
                                    </td>
                                </tr>
                                <tr 
                                    v-for="stk in stocks.data" 
                                    :key="stk.id"
                                    class="hover:bg-slate-50/30 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <!-- Miniature Product Thumbnail -->
                                            <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                                                <img 
                                                    v-if="stk.product.foto" 
                                                    :src="`/storage/${stk.product.foto}`" 
                                                    class="w-full h-full object-cover"
                                                />
                                                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-slate-400">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                                </svg>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-700 leading-snug">{{ stk.product.name }}</span>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-xs font-mono font-bold text-indigo-600">{{ stk.product.sku }}</span>
                                                    <span class="text-[10px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded uppercase font-bold">{{ stk.product.kategori }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span 
                                            class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full border"
                                            :class="{
                                                'bg-indigo-50 text-indigo-700 border-indigo-100': stk.grade === 'A',
                                                'bg-amber-50 text-amber-700 border-amber-100': stk.grade === 'B',
                                                'bg-rose-50 text-rose-700 border-rose-100': stk.grade === 'Reject'
                                            }"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full" :class="{'bg-indigo-500': stk.grade==='A', 'bg-amber-500': stk.grade==='B', 'bg-rose-500': stk.grade==='Reject'}"></span>
                                            Grade {{ stk.grade }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1.5">
                                            <span 
                                                class="font-extrabold text-base"
                                                :class="stk.quantity <= 5 && stk.grade === 'A' ? 'text-rose-600 animate-pulse' : 'text-slate-800'"
                                            >
                                                {{ stk.quantity }}
                                            </span>
                                            <span class="text-slate-400 font-medium text-xs">Pcs</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="p-1 bg-slate-50 text-slate-500 rounded border border-slate-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25ga7.5 7.5 0 1 1 15 0Z" />
                                                </svg>
                                            </span>
                                            <span class="font-bold text-slate-700">{{ stk.rak_lokasi || 'Belum Dialokasikan' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                                        {{ new Date(stk.updated_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button 
                                            @click="openEditModal(stk)"
                                            class="inline-flex items-center justify-center gap-1 px-3 py-1.5 border border-slate-200 text-xs font-semibold text-indigo-600 hover:bg-indigo-50/50 hover:border-indigo-200 rounded-lg transition-all"
                                        >
                                            Ubah Lokasi/Stok
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="stocks.links.length > 3" class="flex justify-center py-5 border-t border-slate-100">
                        <nav class="flex items-center gap-1">
                            <template v-for="(link, key) in stocks.links" :key="key">
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

        <!-- EDIT STOCK & SHELF MODAL -->
        <Modal :show="showEditModal" @close="showEditModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">Penyesuaian Rak & Stok</h3>
                <p class="text-xs text-slate-400 mb-6">Pindahkan koordinat penyimpanan atau sesuaikan kuantitas fisik (Adjusment).</p>
                
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 mb-6 flex gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 border border-indigo-100 overflow-hidden flex items-center justify-center shrink-0">
                        <img 
                            v-if="selectedStock?.product.foto" 
                            :src="`/storage/${selectedStock?.product.foto}`" 
                            class="w-full h-full object-cover"
                        />
                        <span v-else class="text-xs font-black text-indigo-600">{{ selectedStock?.grade }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-700 leading-snug">{{ selectedStock?.product.name }}</span>
                        <span class="text-xs font-mono text-slate-400">{{ selectedStock?.product.sku }} | Grade {{ selectedStock?.grade }}</span>
                    </div>
                </div>

                <form @submit.prevent="submitUpdate" class="space-y-5">
                    <div>
                        <InputLabel for="rak_lokasi" value="Koordinat Rak Lokasi Gudang" />
                        <TextInput 
                            id="rak_lokasi"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.rak_lokasi"
                            placeholder="Contoh: Rak A-02, Rak B-05"
                            required
                        />
                        <p class="text-[10px] text-slate-400 mt-1">Gunakan format lokasi yang seragam demi kerapian pemetaan pergudangan.</p>
                    </div>

                    <div>
                        <InputLabel for="quantity" value="Jumlah Kuantitas Fisik (Pcs)" />
                        <TextInput 
                            id="quantity"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.quantity"
                            required
                            min="0"
                        />
                        <p class="text-[10px] text-amber-600 mt-1 font-semibold">Perhatian: Merubah kuantitas di sini akan memicu penyesuaian stok langsung tanpa log mutasi formal!</p>
                    </div>

                    <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                        <SecondaryButton @click="showEditModal = false">Batal</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Simpan Penyesuaian
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
