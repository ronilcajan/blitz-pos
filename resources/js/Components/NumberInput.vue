<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: Number,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        type="number"
        class="input input-bordered mb-4"
        v-model="model"
        ref="input"
    />
</template>
