<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3'

const model = defineModel({
    status: String,
});

const status = ref('');

watch(status, value => {
	router.get('/expenses',
	{ status: value },
	{ preserveState: true, replace:true, preserveScroll: true } )
})

</script>
<template>
<div class="dropdown dropdown-bottom dropdown-end">
    <SecondaryButton tabindex="0" role="button"  class="btn-sm btn-outline">
        <svg class="w-4 h-4 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M18.796 4H5.204a1 1 0 0 0-.753 1.659l5.302 6.058a1 1 0 0 1 .247.659v4.874a.5.5 0 0 0 .2.4l3 2.25a.5.5 0 0 0 .8-.4v-7.124a1 1 0 0 1 .247-.659l5.302-6.059c.566-.646.106-1.658-.753-1.658Z"/>
        </svg> Filter
    </SecondaryButton>
    <div tabindex="0" class="dropdown-content z-[9] card card-compact w-64 p-1 shadow-lg mt-1 bg-base-100 ">
        <div class="card-body">
            <div class="flex">
                <h3 class="card-title">Status</h3>
                <button @click="status = ''" class="btn btn-xs btn-outline ml-auto">Clear</button>
            </div>
            <label class="label cursor-pointer justify-start gap-3">
                <input type="radio" v-model="status" class="radio radio-sm" name="status" value="all" :checked="model === 'all' || model === ''" />
                <span class="label-text">All</span>
            </label>
            <label class="label cursor-pointer justify-start gap-3">
                <input type="radio" v-model="status" class="radio radio-sm" name="status" value="approved" :checked="status === 'approved'" />
                <span class="label-text">Approved</span>
            </label>
            <label class="label cursor-pointer justify-start gap-3">
                <input type="radio" v-model="status"  class="radio radio-sm" name="status" value="pending" :checked="status === 'pending'"/>
                <span class="label-text">Pending</span>
            </label>
            <label class="label cursor-pointer justify-start gap-3">
                <input type="radio" v-model="status" class="radio radio-sm" name="status" value="rejected" :checked="status === 'rejected'"/>
                <span class="label-text">Rejected</span>
            </label>
        </div>
    </div>
</div>
</template>

