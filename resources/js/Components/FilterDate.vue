<script setup>
import { watch } from 'vue';
import { router } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';

const props = defineProps({
    dateRange:  Object,
    url: String
});

const clearDates = () => {
    props.dateRange.from_date = '';
    props.dateRange.to_date = '';
  router.visit(props.url);
};

watch(props.dateRange, (value) => {

    if (value.from_date && value.to_date) {

        if(value.from_date >  value.to_date){
        useToast().error('From date must be less than or equal to To date!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });

            return;
        }

        const newQuery = { ...route.params, from_date: value.from_date, to_date: value.to_date };
        router.visit(props.url, {
            method: 'get',
            data: newQuery,
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    }
});

</script>
<template>
<div class="dropdown dropdown-bottom dropdown-end">
    <SecondaryButton tabindex="0" role="button" class="btn-sm btn-outline">
        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M18.796 4H5.204a1 1 0 0 0-.753 1.659l5.302 6.058a1 1 0 0 1 .247.659v4.874a.5.5 0 0 0 .2.4l3 2.25a.5.5 0 0 0 .8-.4v-7.124a1 1 0 0 1 .247-.659l5.302-6.059c.566-.646.106-1.658-.753-1.658Z"/>
        </svg> Date Filter
    </SecondaryButton>
    <div tabindex="0" class="dropdown-content z-[9] card card-compact w-64 p-1 shadow-lg mt-1 bg-base-100">
        <div class="card-body">
            <div class="flex">
                <p class="font-bold">Select Date Range</p>
                <button @click="clearDates" class="btn btn-xs btn-outline ml-auto">Clear</button>
            </div>
            <div>
                <InputLabel>From date</InputLabel>
                <input class="input input-sm w-full" type="date" v-model="dateRange.from_date">
            </div>
            <div>
                <InputLabel>To date</InputLabel>
                <input class="input input-sm w-full" type="date" v-model="dateRange.to_date">
            </div>
        </div>
    </div>
</div>
</template>

