<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    products: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({ search: '', category: '' })
    }
});

// Search & Filter State
const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');

// Modals State
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedProduct = ref(null);

// Forms
const form = useForm({
    sku: '',
    name: '',
    kategori: '',
    harga_beli: '',
    harga_jual: '',
    foto: null
});

// Debounce search and apply filters
let searchTimeout;
watch([search, selectedCategory], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('products.index'),
            { search: search.value, category: selectedCategory.value },
            { preserveState: true, replace: true }
        );
    }, 300);
});

// Currency Formatter
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

// SKU Auto-generator based on category and random/time
const generateSKU = () => {
    const category = form.kategori.trim();
    if (!category) return;
    
    // Get initials of category (up to 3 chars)
    const prefix = category
        .toUpperCase()
        .split(' ')
        .map(word => word[0])
        .join('')
        .substring(0, 3);
        
    const randomNum = Math.floor(100 + Math.random() * 900);
    const timestamp = Date.now().toString().slice(-4);
    
    form.sku = `${prefix}-${randomNum}${timestamp}`;
};

// Image Upload Preview
const imagePreview = ref(null);
const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.foto = file;
        const reader = new FileReader();
        reader.onload = (event) => {
            imagePreview.value = event.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Open Modals
const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    imagePreview.value = null;
    showCreateModal.value = true;
};

const openEditModal = (product) => {
    selectedProduct.value = product;
    form.reset();
    form.clearErrors();
    form.sku = product.sku;
    form.name = product.name;
    form.kategori = product.kategori;
    form.harga_beli = product.harga_beli;
    form.harga_jual = product.harga_jual;
    form.foto = null; // Don't bind file object unless changed
    imagePreview.value = product.foto ? `/storage/${product.foto}` : null;
    showEditModal.value = true;
};

const openDeleteModal = (product) => {
    selectedProduct.value = product;
    showDeleteModal.value = true;
};

// Submit Actions
const submitCreate = () => {
    form.post(route('products.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
            imagePreview.value = null;
        }
    });
};

const submitUpdate = () => {
    // Laravel has issues with PUT requests sending Multipart Form Data.
    // So we use post request with _method: 'PUT' override.
    form.transform((data) => ({
        ...data,
        _method: 'PUT'
    })).post(route('products.update', selectedProduct.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            form.reset();
            imagePreview.value = null;
        }
    });
};

const submitDelete = () => {
    router.delete(route('products.destroy', selectedProduct.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        }
    });
};
</script>

<template>
    <Head title="Katalog Produk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 tracking-tight">
                        Katalog Produk
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Kelola data produk, kode SKU, patokan harga, dan kelengkapan foto katalog barang.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        @click="openCreateModal"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl shadow-lg shadow-indigo-100 hover:shadow-indigo-200 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Produk
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 bg-slate-50 min-h-[calc(100vh-140px)]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                
                <!-- Search & Filters -->
                <div class="bg-white p-4 border border-slate-100 rounded-2xl shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="relative w-full md:w-96">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                            </svg>
                        </span>
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Cari SKU, Nama Produk, Kategori..." 
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        />
                    </div>

                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <select 
                            v-model="selectedCategory"
                            class="w-full md:w-56 px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-sm text-slate-700 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                </div>

                <!-- Catalog Grid -->
                <div v-if="products.data.length === 0" class="bg-white border border-slate-100 rounded-2xl p-16 text-center shadow-sm">
                    <div class="p-4 bg-indigo-50 text-indigo-500 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Tidak ada produk ditemukan</h3>
                    <p class="text-sm text-slate-500 max-w-sm mx-auto mt-1">
                        Coba bersihkan pencarian atau tambahkan produk baru ke dalam katalog sistem.
                    </p>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div 
                        v-for="p in products.data" 
                        :key="p.id"
                        class="bg-white border border-slate-100 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group"
                    >
                        <!-- Product Image Area -->
                        <div class="relative bg-slate-100 h-48 w-full flex items-center justify-center overflow-hidden">
                            <img 
                                v-if="p.foto"
                                :src="`/storage/${p.foto}`" 
                                :alt="p.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <!-- Beautiful Placeholder -->
                            <div v-else class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-purple-600 opacity-80 flex flex-col items-center justify-center p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-white/90">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                                <span class="text-[10px] font-extrabold tracking-widest text-indigo-100 uppercase mt-2">NO PHOTO</span>
                            </div>

                            <!-- SKU and Category Overlays -->
                            <span class="absolute top-3 left-3 bg-slate-900/80 backdrop-blur-sm text-white text-[11px] font-bold font-mono px-2.5 py-1 rounded-lg">
                                {{ p.sku }}
                            </span>
                            <span class="absolute top-3 right-3 bg-white/95 backdrop-blur-sm text-indigo-700 text-[10px] font-extrabold px-2.5 py-1 rounded-lg shadow-sm capitalize border border-indigo-50">
                                {{ p.kategori }}
                            </span>
                        </div>

                        <!-- Product Content Area -->
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="font-bold text-slate-800 group-hover:text-indigo-600 transition-colors text-base line-clamp-1">
                                    {{ p.name }}
                                </h4>
                                
                                <div class="grid grid-cols-2 gap-2 mt-4">
                                    <div class="p-2 bg-slate-50 rounded-lg">
                                        <span class="text-[10px] font-bold text-slate-400 block uppercase">Harga Beli</span>
                                        <span class="text-xs font-bold text-slate-700">{{ formatCurrency(p.harga_beli) }}</span>
                                    </div>
                                    <div class="p-2 bg-indigo-50/50 rounded-lg">
                                        <span class="text-[10px] font-bold text-indigo-400 block uppercase">Harga Jual</span>
                                        <span class="text-xs font-bold text-indigo-700">{{ formatCurrency(p.harga_jual) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Stats: Stock Grades -->
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5 text-slate-500 font-medium">
                                    <span>Stok:</span>
                                    <span class="font-bold text-slate-700">
                                        {{ p.stocks ? p.stocks.reduce((acc, s) => acc + s.quantity, 0) : 0 }} pcs
                                    </span>
                                </div>
                                <div class="flex gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500" title="Grade A"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500" title="Grade B"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500" title="Reject"></span>
                                </div>
                            </div>

                            <!-- Actions Row -->
                            <div class="mt-4 pt-3 border-t border-slate-100 flex gap-2">
                                <button 
                                    @click="openEditModal(p)"
                                    class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 border border-slate-200 text-xs font-semibold text-slate-600 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-all"
                                >
                                    Edit
                                </button>
                                <button 
                                    @click="openDeleteModal(p)"
                                    class="p-1.5 border border-rose-100 text-rose-500 rounded-lg hover:bg-rose-50 hover:text-rose-700 transition-all"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination Component -->
                <div v-if="products.links.length > 3" class="flex justify-center mt-8">
                    <nav class="flex items-center gap-1">
                        <template v-for="(link, key) in products.links" :key="key">
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

        <!-- CREATE PRODUCT MODAL -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-6">Tambah Produk Baru</h3>
                
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <InputLabel for="kategori" value="Kategori Produk" />
                        <select 
                            id="kategori"
                            v-model="form.kategori"
                            @change="generateSKU"
                            class="w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="" disabled>Pilih Kategori</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Pakaian">Pakaian</option>
                            <option value="Alas Kaki">Alas Kaki</option>
                            <option value="Makanan">Makanan</option>
                            <option value="Aksesoris">Aksesoris</option>
                            <option value="Operasional">Operasional Gudang</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.kategori" />
                    </div>

                    <div>
                        <InputLabel for="sku" value="SKU (Stock Keeping Unit)" />
                        <div class="flex gap-2">
                            <TextInput 
                                id="sku"
                                type="text"
                                class="mt-1 block w-full uppercase"
                                v-model="form.sku"
                                placeholder="CONTOH: ELK-389429"
                                required
                            />
                            <button 
                                type="button"
                                @click="generateSKU"
                                class="mt-1 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg border border-slate-300 transition-all flex items-center justify-center whitespace-nowrap"
                                title="Auto-generate SKU berdasarkan kategori"
                            >
                                Generate SKU
                            </button>
                        </div>
                        <InputError class="mt-1" :message="form.errors.sku" />
                    </div>

                    <div>
                        <InputLabel for="name" value="Nama Produk" />
                        <TextInput 
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            placeholder="Contoh: TV LED LG 32 Inch"
                            required
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="harga_beli" value="Harga Beli (Modal)" />
                            <TextInput 
                                id="harga_beli"
                                type="number"
                                class="mt-1 block w-full"
                                v-model="form.harga_beli"
                                placeholder="100000"
                                required
                            />
                            <InputError class="mt-1" :message="form.errors.harga_beli" />
                        </div>
                        <div>
                            <InputLabel for="harga_jual" value="Harga Jual" />
                            <TextInput 
                                id="harga_jual"
                                type="number"
                                class="mt-1 block w-full"
                                v-model="form.harga_jual"
                                placeholder="150000"
                                required
                            />
                            <InputError class="mt-1" :message="form.errors.harga_jual" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="foto" value="Foto Produk (Kompresi Otomatis)" />
                        <input 
                            id="foto"
                            type="file"
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            @change="handleImageChange"
                        />
                        <InputError class="mt-1" :message="form.errors.foto" />
                        
                        <!-- Preview Image -->
                        <div v-if="imagePreview" class="mt-4 relative w-32 h-32 border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                            <img :src="imagePreview" class="w-full h-full object-cover" />
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                        <SecondaryButton @click="showCreateModal = false">Batal</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Simpan Produk
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- EDIT PRODUCT MODAL -->
        <Modal :show="showEditModal" @close="showEditModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-6">Edit Data Produk</h3>
                
                <form @submit.prevent="submitUpdate" class="space-y-4">
                    <div>
                        <InputLabel for="edit_kategori" value="Kategori Produk" />
                        <select 
                            id="edit_kategori"
                            v-model="form.kategori"
                            class="w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="" disabled>Pilih Kategori</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Pakaian">Pakaian</option>
                            <option value="Alas Kaki">Alas Kaki</option>
                            <option value="Makanan">Makanan</option>
                            <option value="Aksesoris">Aksesoris</option>
                            <option value="Operasional">Operasional Gudang</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.kategori" />
                    </div>

                    <div>
                        <InputLabel for="edit_sku" value="SKU (Stock Keeping Unit)" />
                        <TextInput 
                            id="edit_sku"
                            type="text"
                            class="mt-1 block w-full uppercase"
                            v-model="form.sku"
                            required
                        />
                        <InputError class="mt-1" :message="form.errors.sku" />
                    </div>

                    <div>
                        <InputLabel for="edit_name" value="Nama Produk" />
                        <TextInput 
                            id="edit_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="edit_harga_beli" value="Harga Beli (Modal)" />
                            <TextInput 
                                id="edit_harga_beli"
                                type="number"
                                class="mt-1 block w-full"
                                v-model="form.harga_beli"
                                required
                            />
                            <InputError class="mt-1" :message="form.errors.harga_beli" />
                        </div>
                        <div>
                            <InputLabel for="edit_harga_jual" value="Harga Jual" />
                            <TextInput 
                                id="edit_harga_jual"
                                type="number"
                                class="mt-1 block w-full"
                                v-model="form.harga_jual"
                                required
                            />
                            <InputError class="mt-1" :message="form.errors.harga_jual" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="edit_foto" value="Foto Produk (Kompresi Otomatis)" />
                        <input 
                            id="edit_foto"
                            type="file"
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            @change="handleImageChange"
                        />
                        <InputError class="mt-1" :message="form.errors.foto" />
                        
                        <!-- Preview Image -->
                        <div v-if="imagePreview" class="mt-4 relative w-32 h-32 border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                            <img :src="imagePreview" class="w-full h-full object-cover" />
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                        <SecondaryButton @click="showEditModal = false">Batal</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Perbarui Produk
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- DELETE CONFIRM MODAL -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false" max-width="sm">
            <div class="p-6 text-center">
                <div class="p-4 bg-rose-50 text-rose-500 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Apakah Anda Yakin?</h3>
                <p class="text-sm text-slate-500 max-w-xs mx-auto mt-2">
                    Tindakan ini akan menghapus produk <span class="font-bold text-slate-700">"{{ selectedProduct?.name }}"</span> beserta seluruh relasi stok barang gudang di dalamnya.
                </p>
                <div class="mt-6 flex justify-center gap-2">
                    <SecondaryButton @click="showDeleteModal = false">Batal</SecondaryButton>
                    <DangerButton @click="submitDelete">Ya, Hapus Produk</DangerButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
