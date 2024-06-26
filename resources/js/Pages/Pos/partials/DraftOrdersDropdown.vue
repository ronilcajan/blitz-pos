<script setup>
import { watch } from 'vue';
import { router } from '@inertiajs/vue3'

const props = defineProps({
    orders: Object
})

const model = defineModel({
    status: String,
});

watch(model, value => {
	router.get('/products',
	{ type: value },
	{ preserveState: true, replace:true, preserveScroll: true } )
})
</script>
<template>
<div class="dropdown dropdown-bottom dropdown-end">
    <div class="indicator">
        <span v-if="orders.lenght > 0" class="indicator-item badge badge-primary">
            {{ orders.lenght }}
        </span>
        <PrimaryButton tabindex="0" role="button" class="btn-circle btn-outline btn-sm" title="Draft orders">
            <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-list"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l11 0" /><path d="M9 12l11 0" /><path d="M9 18l11 0" /><path d="M5 6l0 .01" /><path d="M5 12l0 .01" /><path d="M5 18l0 .01" /></svg>
        </PrimaryButton>
    </div>
    <div tabindex="0" class="dropdown-content z-[9] card card-compact w-64 p-1 shadow-lg mt-1 bg-base-100 ">
        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
            <li class="ml-3 font-bold uppercase mb-3">Draft Orders</li>
            <li><a>Item 1</a></li>
            <li><a>Item 2</a></li>
        </ul>
    </div>
</div>
</template>

