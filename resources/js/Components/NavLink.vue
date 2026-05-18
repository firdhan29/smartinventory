<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-500 text-sm font-extrabold leading-5 text-indigo-600 focus:outline-none transition-all duration-300 hover:scale-105 active:scale-95'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-semibold leading-5 text-slate-500 hover:text-indigo-600 hover:border-indigo-200 focus:outline-none transition-all duration-300 hover:-translate-y-[1px] hover:scale-105 active:scale-95',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
