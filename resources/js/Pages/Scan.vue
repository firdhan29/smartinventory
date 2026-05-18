<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        required: true
    }
});

// Platform Detection
const isCapacitor = ref(typeof window !== 'undefined' && window.Capacitor !== undefined);

// Scanner States
const skuInput = ref('');
const isScanning = ref(false);
const scanError = ref(null);
const scannedProduct = ref(null);
const successMessage = ref(null);

// Fast Transaction Form
const form = useForm({
    product_id: '',
    type: 'keluar',
    grade: 'A',
    quantity: 1,
    harga_aktual: '',
    notes: 'Scan logging'
});

// Search and Match SKU
const handleSKULookup = () => {
    scanError.value = null;
    scannedProduct.value = null;
    successMessage.value = null;

    const query = skuInput.value.trim().toUpperCase();
    if (!query) return;

    const matched = props.products.find(p => p.sku.toUpperCase() === query);
    
    if (matched) {
        scannedProduct.value = matched;
        form.product_id = matched.id;
        form.harga_aktual = form.type === 'masuk' ? parseFloat(matched.harga_beli) : parseFloat(matched.harga_jual);
    } else {
        scanError.value = `SKU "${query}" tidak ditemukan di database katalog.`;
    }
};

// Simulate Camera QR Scanning (with sound & vibrations standard on native mobile apps)
const triggerScan = () => {
    isScanning.value = true;
    scanError.value = null;
    scannedProduct.value = null;
    successMessage.value = null;

    // Simulate camera access and barcode read delay (1.8s)
    setTimeout(() => {
        isScanning.value = false;
        
        // Randomly pick an existing SKU from dummy database for testing simulation
        if (props.products.length > 0) {
            const randomIndex = Math.floor(Math.random() * props.products.length);
            const randomProduct = props.products[randomIndex];
            skuInput.value = randomProduct.sku;
            handleSKULookup();
            
            // Trigger haptic vibration simulation
            if (typeof navigator !== 'undefined' && navigator.vibrate) {
                navigator.vibrate(100);
            }
        } else {
            scanError.value = "Tidak ada data produk dalam seeder untuk disimulasikan.";
        }
    }, 1800);
};

// Handle fast transaction type toggle
const handleTypeToggle = (type) => {
    form.type = type;
    if (scannedProduct.value) {
        form.harga_aktual = type === 'masuk' 
            ? parseFloat(scannedProduct.value.harga_beli) 
            : parseFloat(scannedProduct.value.harga_jual);
    }
};

// Fast Submit
const submitFastTransaction = () => {
    form.post(route('transactions.store'), {
        onSuccess: () => {
            successMessage.value = `Transaksi ${form.type.toUpperCase()} sebanyak ${form.quantity} pcs untuk ${scannedProduct.value.name} berhasil disimpan!`;
            // Reset scan state
            skuInput.value = '';
            scannedProduct.value = null;
            form.reset();
        },
        onError: (errs) => {
            if (errs.error) {
                scanError.value = errs.error;
            }
        }
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <Head title="Scan QR / Barcode" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                         Pemindai Barcode
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">Gunakan kamera HP atau input manual untuk pencatatan inventaris super cepat.</p>
                </div>
                
                <!-- Platform Environment Tag -->
                <span 
                    class="px-2.5 py-1 text-[10px] font-extrabold rounded-lg tracking-wider border shadow-sm"
                    :class="isCapacitor ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-slate-100 text-slate-600 border-slate-200'"
                >
                    {{ isCapacitor ? 'CAPACITOR MOBILE' : 'WEB BROWSER' }}
                </span>
            </div>
        </template>

        <div class="py-6 bg-slate-50 min-h-[calc(100vh-140px)]">
            <div class="mx-auto max-w-md px-4 space-y-6">

                <!-- Alert Messages -->
                <div v-if="successMessage" class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-bold rounded-2xl flex items-start gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-emerald-500 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>{{ successMessage }}</span>
                </div>

                <div v-if="scanError" class="p-4 bg-rose-50 border border-rose-100 text-rose-700 text-xs font-bold rounded-2xl flex items-start gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-rose-500 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" />
                    </svg>
                    <span>{{ scanError }}</span>
                </div>

                <!-- SCAN VIEWPORT CONSOLE -->
                <div class="relative overflow-hidden bg-slate-900 border border-slate-950 rounded-3xl shadow-xl aspect-square flex flex-col items-center justify-center p-6 group text-white">
                    
                    <!-- Simulating Camera Preview Background -->
                    <div 
                        class="absolute inset-0 bg-slate-800 transition-all duration-700 flex items-center justify-center"
                        :class="isScanning ? 'opacity-30 bg-slate-700 scale-105' : 'opacity-20'"
                    >
                        <!-- Stylized concentric radar circles -->
                        <div class="absolute w-64 h-64 border-2 border-dashed border-indigo-500/25 rounded-full animate-spin"></div>
                        <div class="absolute w-44 h-44 border-2 border-indigo-400/20 rounded-full animate-ping"></div>
                    </div>

                    <!-- Scanning laser line -->
                    <div 
                        v-if="isScanning" 
                        class="absolute left-0 right-0 h-1 bg-gradient-to-r from-red-500 via-rose-500 to-red-500 shadow-lg shadow-rose-500/80 w-full top-0 animate-[scan_1.5s_infinite]"
                    ></div>

                    <!-- Viewport bounding brackets -->
                    <div class="absolute top-12 left-12 w-8 h-8 border-t-4 border-l-4 border-indigo-500 rounded-tl-xl"></div>
                    <div class="absolute top-12 right-12 w-8 h-8 border-t-4 border-r-4 border-indigo-500 rounded-tr-xl"></div>
                    <div class="absolute bottom-12 left-12 w-8 h-8 border-b-4 border-l-4 border-indigo-500 rounded-bl-xl"></div>
                    <div class="absolute bottom-12 right-12 w-8 h-8 border-b-4 border-r-4 border-indigo-500 rounded-br-xl"></div>

                    <!-- Scanner Icons & Prompt -->
                    <div class="relative z-10 text-center space-y-4 px-6 select-none" v-if="!isScanning">
                        <div class="p-5 bg-white/10 border border-white/5 rounded-3xl w-24 h-24 mx-auto flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-indigo-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-lg text-slate-100">Ready to Scan</h3>
                            <p class="text-xs text-slate-400 mt-1 max-w-[200px] mx-auto leading-relaxed">Dekatkan kamera ke kode barcode / QR barang atau klik tombol di bawah.</p>
                        </div>
                    </div>

                    <div class="relative z-10 text-center space-y-3" v-else>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-500 text-white text-xs font-black rounded-full uppercase tracking-wider animate-pulse">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                            Live Scanning
                        </span>
                        <p class="text-xs text-slate-400 font-medium">Membaca data sensor kamera smartphone...</p>
                    </div>
                </div>

                <!-- SCAN & SEARCH CONTROLS -->
                <div class="space-y-4">
                    <button 
                        @click="triggerScan"
                        :disabled="isScanning"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 rounded-2xl shadow-lg shadow-indigo-100 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>
                        Picu Scan Kamera Mobile
                    </button>

                    <!-- Fallback SKU input search (Mendengarkan USB scanner) -->
                    <div class="relative">
                        <input 
                            v-model="skuInput"
                            @keyup.enter="handleSKULookup"
                            type="text" 
                            placeholder="Cari SKU manual / Colok Barcode Scanner..." 
                            class="w-full pl-4 pr-24 py-3 bg-white border border-slate-200 focus:border-indigo-500 rounded-2xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition-all font-semibold"
                        />
                        <button 
                            @click="handleSKULookup"
                            class="absolute right-2 top-2 bottom-2 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-all flex items-center justify-center"
                        >
                            Lookup SKU
                        </button>
                    </div>
                </div>

                <!-- SCANNED PRODUCT FORM CARD (Slides/Fades in when a matching product is scanned/found) -->
                <div 
                    v-if="scannedProduct" 
                    class="bg-white border border-indigo-100 rounded-3xl shadow-lg p-5 space-y-5 animate-[fadeIn_0.4s_ease]"
                >
                    <!-- Scanned Product Title Area -->
                    <div class="flex gap-4">
                        <div class="w-16 h-16 bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 flex items-center justify-center shrink-0">
                            <img 
                                v-if="scannedProduct.foto" 
                                :src="`/storage/${scannedProduct.foto}`" 
                                class="w-full h-full object-cover"
                            />
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                        </div>
                        <div class="flex-1 flex flex-col justify-center">
                            <h4 class="font-extrabold text-slate-800 leading-tight">{{ scannedProduct.name }}</h4>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">{{ scannedProduct.sku }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase">{{ scannedProduct.kategori }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Rapid Transaction Inputs -->
                    <form @submit.prevent="submitFastTransaction" class="space-y-4 pt-3 border-t border-slate-100">
                        
                        <!-- Restock vs Sell Toggle Switches -->
                        <div>
                            <InputLabel value="Tipe Transaksi Cepat" class="mb-1.5" />
                            <div class="flex p-1 bg-slate-100 rounded-xl">
                                <button 
                                    type="button"
                                    @click="handleTypeToggle('keluar')"
                                    class="flex-1 py-2 text-xs font-bold rounded-lg transition-all text-center flex items-center justify-center gap-1"
                                    :class="form.type === 'keluar' ? 'bg-white text-slate-800 shadow-sm border border-slate-200/50' : 'text-slate-500'"
                                >
                                    🔴 Keluar (Jual)
                                </button>
                                <button 
                                    type="button"
                                    @click="handleTypeToggle('masuk')"
                                    class="flex-1 py-2 text-xs font-bold rounded-lg transition-all text-center flex items-center justify-center gap-1"
                                    :class="form.type === 'masuk' ? 'bg-white text-slate-800 shadow-sm border border-slate-200/50' : 'text-slate-500'"
                                >
                                    🟢 Masuk (Restock)
                                </button>
                            </div>
                        </div>

                        <!-- Grade and Quantity in Grid -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="fast_grade" value="Grade Kualitas" />
                                <select 
                                    id="fast_grade"
                                    v-model="form.grade"
                                    class="w-full mt-1 border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-sm"
                                    required
                                >
                                    <option value="A">Grade A (Prima)</option>
                                    <option value="B">Grade B (Defect)</option>
                                    <option value="Reject">Reject (Rusak)</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel for="fast_quantity" value="Jumlah Pcs (Qty)" />
                                <input 
                                    id="fast_quantity"
                                    type="number"
                                    class="w-full mt-1 border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-sm font-bold"
                                    v-model="form.quantity"
                                    min="1"
                                    required
                                />
                            </div>
                        </div>

                        <!-- Actual price unit -->
                        <div>
                            <InputLabel for="fast_price" value="Harga Transaksi Per Pcs (Rp)" />
                            <input 
                                id="fast_price"
                                type="number"
                                class="w-full mt-1 border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-sm font-bold"
                                v-model="form.harga_aktual"
                                required
                                min="0"
                            />
                        </div>

                        <!-- Real-time total prediction ledger -->
                        <div 
                            v-if="form.quantity && form.harga_aktual"
                            class="p-4 rounded-xl border flex justify-between items-center text-sm font-bold"
                            :class="form.type === 'masuk' ? 'bg-rose-50/50 border-rose-100 text-rose-800' : 'bg-indigo-50/50 border-indigo-100 text-indigo-800'"
                        >
                            <span class="text-xs">Subtotal Pembukuan:</span>
                            <span class="text-base font-black">{{ formatCurrency(form.quantity * form.harga_aktual) }}</span>
                        </div>

                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-6 py-3 text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-2xl shadow-md transition-all duration-300"
                        >
                            Simpan Mutasi Barcode
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Custom high fidelity scan line keyframes */
@keyframes scan {
    0% { top: 0%; }
    50% { top: 100%; }
    100% { top: 0%; }
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
