<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    users: {
        type: Object,
        required: true
    },
    roles: {
        type: Array,
        required: true
    }
});

// Modals State
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedUser = ref(null);

// Forms
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'operator'
});

// Open Modals
const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    showCreateModal.value = true;
};

const openEditModal = (user) => {
    selectedUser.value = user;
    form.reset();
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.role = user.roles && user.roles.length > 0 ? user.roles[0].name : 'operator';
    showEditModal.value = true;
};

const openDeleteModal = (user) => {
    selectedUser.value = user;
    showDeleteModal.value = true;
};

// Submit Actions
const submitCreate = () => {
    form.post(route('users.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
};

const submitUpdate = () => {
    form.put(route('users.update', selectedUser.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            form.reset();
        }
    });
};

const submitDelete = () => {
    router.delete(route('users.destroy', selectedUser.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        }
    });
};

// Helper for formatting date
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

// Helper to get role display name and styling
const getRoleDetails = (roleName) => {
    switch (roleName) {
        case 'admin':
            return {
                label: 'Administrator',
                badgeClass: 'bg-indigo-50 text-indigo-700 border-indigo-100'
            };
        case 'operator':
            return {
                label: 'Operator Gudang',
                badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-100'
            };
        case 'finance':
            return {
                label: 'Finance (Keuangan)',
                badgeClass: 'bg-amber-50 text-amber-700 border-amber-100'
            };
        default:
            return {
                label: roleName || 'Pengguna',
                badgeClass: 'bg-slate-50 text-slate-700 border-slate-100'
            };
    }
};
</script>

<template>
    <Head title="Manajemen Pengguna" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 tracking-tight">
                        Manajemen Pengguna
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Kelola akun hak akses staf gudang untuk Operator Gudang dan tim Finance Keuangan.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        @click="openCreateModal"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl shadow-lg shadow-indigo-100 hover:shadow-indigo-200 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                        </svg>
                        Tambah Pengguna
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 bg-slate-50 min-h-[calc(100vh-140px)]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                
                <!-- Info Glassmorphism Banner -->
                <div class="bg-gradient-to-r from-indigo-900 to-slate-900 text-white rounded-2xl p-6 shadow-md relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-white rounded-full group-hover:scale-125 transition-all duration-500 opacity-5"></div>
                    <h3 class="text-lg font-bold">Pengaturan Otorisasi & Peran Staf</h3>
                    <p class="text-xs text-indigo-200 mt-2 max-w-2xl leading-relaxed">
                        Anda dapat mendaftarkan akun baru untuk staf gudang dengan membaginya ke dalam 3 peran terintegrasi:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 pt-4 border-t border-indigo-950/40">
                        <div class="p-3 bg-white/5 rounded-xl border border-white/10">
                            <span class="text-xs font-bold text-indigo-300 block">1. Administrator</span>
                            <span class="text-[11px] text-indigo-100/80 mt-1 block">Akses penuh ke seluruh modul sistem gudang, katalog barang, stok, riwayat transaksi mutasi, ledger keuangan, dan manajemen akun staf.</span>
                        </div>
                        <div class="p-3 bg-white/5 rounded-xl border border-white/10">
                            <span class="text-xs font-bold text-emerald-300 block">2. Operator Gudang</span>
                            <span class="text-[11px] text-indigo-100/80 mt-1 block">Akses terbatas untuk cek stok barang, mencatat log transaksi masuk/keluar barang (mutasi), cetak Surat Jalan PDF, dan memindai barcode/QR.</span>
                        </div>
                        <div class="p-3 bg-white/5 rounded-xl border border-white/10">
                            <span class="text-xs font-bold text-amber-300 block">3. Finance (Keuangan)</span>
                            <span class="text-[11px] text-indigo-100/80 mt-1 block">Akses khusus untuk cek persediaan stok barang, melihat buku besar ledger kas keuangan, mencatat pengeluaran operasional (seperti gaji staf/PLN), dan ekspor Excel laporan laba rugi.</span>
                        </div>
                    </div>
                </div>

                <!-- Users List Table -->
                <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-400 text-xs uppercase tracking-wider font-bold border-b border-slate-100">
                                    <th class="px-6 py-4">Nama & Email</th>
                                    <th class="px-6 py-4">Peran / Role</th>
                                    <th class="px-6 py-4">Tanggal Bergabung</th>
                                    <th class="px-6 py-4 text-right">Aksi Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr v-if="users.data.length === 0">
                                    <td colspan="4" class="text-center py-16 text-slate-400 font-medium">
                                        Tidak ada akun operator atau finance yang terdaftar.
                                    </td>
                                </tr>
                                <tr 
                                    v-for="user in users.data" 
                                    :key="user.id"
                                    class="hover:bg-slate-50/30 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <!-- Beautiful Initial Avatar -->
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-sm border shadow-sm"
                                                :class="user.roles && user.roles[0]?.name === 'finance' 
                                                    ? 'bg-amber-50 text-amber-700 border-amber-100' 
                                                    : 'bg-emerald-50 text-emerald-700 border-emerald-100'"
                                            >
                                                {{ user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-800 text-sm">{{ user.name }}</span>
                                                <span class="text-xs text-slate-400 font-mono mt-0.5">{{ user.email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span 
                                            class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-extrabold rounded-full border"
                                            :class="getRoleDetails(user.roles && user.roles[0]?.name).badgeClass"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full" 
                                                :class="user.roles && user.roles[0]?.name === 'finance' ? 'bg-amber-500' : 'bg-emerald-500'"
                                            ></span>
                                            {{ getRoleDetails(user.roles && user.roles[0]?.name).label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 font-medium whitespace-nowrap">
                                        {{ formatDate(user.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                @click="openEditModal(user)"
                                                class="inline-flex items-center justify-center gap-1 px-3 py-1.5 border border-slate-200 text-xs font-semibold text-slate-600 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-all"
                                            >
                                                Edit
                                            </button>
                                            <button 
                                                @click="openDeleteModal(user)"
                                                class="p-1.5 border border-rose-100 text-rose-500 rounded-lg hover:bg-rose-50 hover:text-rose-700 transition-all"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.links.length > 3" class="flex justify-center py-5 border-t border-slate-100">
                        <nav class="flex items-center gap-1">
                            <template v-for="(link, key) in users.links" :key="key">
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

        <!-- CREATE USER MODAL -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">Tambah Pengguna Baru</h3>
                <p class="text-xs text-slate-400 mb-6">Input email, sandi, nama lengkap dan pilih tingkat otorisasi peran.</p>
                
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nama Lengkap" />
                        <TextInput 
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            placeholder="Contoh: Budi Santoso"
                            required
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Alamat Email" />
                        <TextInput 
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            placeholder="Contoh: budi@gudang.com"
                            required
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="password" value="Kata Sandi" />
                            <TextInput 
                                id="password"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password"
                                required
                            />
                            <InputError class="mt-1" :message="form.errors.password" />
                        </div>
                        <div>
                            <InputLabel for="password_confirmation" value="Konfirmasi Sandi" />
                            <TextInput 
                                id="password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password_confirmation"
                                required
                            />
                            <InputError class="mt-1" :message="form.errors.password_confirmation" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="role" value="Peran Otorisasi (Hak Akses)" />
                        <select 
                            id="role"
                            v-model="form.role"
                            class="w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            required
                        >
                            <option value="operator">Operator Gudang (Mutasi & Scan)</option>
                            <option value="finance">Finance / Keuangan (Cek Stok, Ledger, Ops & Gaji)</option>
                            <option value="admin">Administrator (Akses Penuh)</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.role" />
                    </div>

                    <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                        <SecondaryButton @click="showCreateModal = false">Batal</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Simpan Pengguna
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- EDIT USER MODAL -->
        <Modal :show="showEditModal" @close="showEditModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">Edit Data Pengguna</h3>
                <p class="text-xs text-slate-400 mb-6">Kosongkan kata sandi jika tidak ingin mengubahnya.</p>
                
                <form @submit.prevent="submitUpdate" class="space-y-4">
                    <div>
                        <InputLabel for="edit_name" value="Nama Lengkap" />
                        <TextInput 
                            id="edit_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="edit_email" value="Alamat Email" />
                        <TextInput 
                            id="edit_email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="edit_password" value="Kata Sandi Baru" />
                            <TextInput 
                                id="edit_password"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password"
                                placeholder="Kosongkan jika tidak diubah"
                            />
                            <InputError class="mt-1" :message="form.errors.password" />
                        </div>
                        <div>
                            <InputLabel for="edit_password_confirmation" value="Konfirmasi Sandi Baru" />
                            <TextInput 
                                id="edit_password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password_confirmation"
                                placeholder="Kosongkan jika tidak diubah"
                            />
                            <InputError class="mt-1" :message="form.errors.password_confirmation" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="edit_role" value="Peran Otorisasi (Hak Akses)" />
                        <select 
                            id="edit_role"
                            v-model="form.role"
                            class="w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            required
                        >
                            <option value="operator">Operator Gudang (Mutasi & Scan)</option>
                            <option value="finance">Finance / Keuangan (Cek Stok, Ledger, Ops & Gaji)</option>
                            <option value="admin">Administrator (Akses Penuh)</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.role" />
                    </div>

                    <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                        <SecondaryButton @click="showEditModal = false">Batal</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Perbarui Pengguna
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- DELETE USER CONFIRM MODAL -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false" max-width="sm">
            <div class="p-6 text-center">
                <div class="p-4 bg-rose-50 text-rose-500 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Apakah Anda Yakin?</h3>
                <p class="text-sm text-slate-500 max-w-xs mx-auto mt-2">
                    Tindakan ini akan menghapus akun <span class="font-bold text-slate-700">"{{ selectedUser?.name }}"</span> dari sistem pergudangan ini.
                </p>
                <div class="mt-6 flex justify-center gap-2">
                    <SecondaryButton @click="showDeleteModal = false">Batal</SecondaryButton>
                    <DangerButton @click="submitDelete">Ya, Hapus Akun</DangerButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
