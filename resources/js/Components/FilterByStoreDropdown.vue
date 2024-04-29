<script setup>
const props = defineProps({
    stores: Object,
    required: true,
});

const model = defineModel({
    type: String,
});
</script>

<template>
    <div>
        <div class="w-full" v-show="$page.props.auth.user.isSuperAdmin">
            <select v-model="model" class="select select-bordered select-sm w-full">
                <option selected value="">Filter by store</option>
                <option v-for="store in stores" :value="store.name" :key="store.id">
                    {{ store.name }}
                </option>
            </select>
        </div>
    </div>
</template>
