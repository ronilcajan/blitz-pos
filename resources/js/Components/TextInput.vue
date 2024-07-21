<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    default: '',
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
        class="input input-bordered mt-2"
        v-model="model"
        ref="input"
    />
</template>
