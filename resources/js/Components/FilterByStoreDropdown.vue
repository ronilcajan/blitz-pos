<script setup>
import { watch } from 'vue';
import { router } from '@inertiajs/vue3'

const props = defineProps(
    {
        stores: Object,
        url: String,
    });

const model = defineModel({
    type: String,
});

watch(model, value => {
	router.get(props.url,
	{ store: value },
	{ preserveState: true, replace:true, preserveScroll: true })
})

</script>

<template>
    <div class="w-full" v-show="$page.props.auth.user.isSuperAdmin">
        <select v-model="model" class="select select-bordered select-sm w-full">
            <option selected value="">Filter by store</option>
            <option v-for="store in stores" :value="store.name" :key="store.id">
                {{ store.name }}
            </option>
        </select>
    </div>
</template>
