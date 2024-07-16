<script setup>
import { computed } from 'vue';
const props = defineProps({
    class: {
        type: String,
        default: '', // Default value to avoid issues if not provided
    },
});

const classes = computed(() => {
    return ['',props.class];
});

</script>

<template>
    <td :class="classes">
        <slot></slot>
    </td>
</template>