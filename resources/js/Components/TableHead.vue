<script setup>
import { computed } from 'vue';
const props = defineProps({
    class: {
        type: String,
    },
});

const classes = computed(() => {
    return [ 'font-bold',][props.class];
});

</script>
<template>
    <th :class="classes">
      <slot></slot>
    </th>
</template>
