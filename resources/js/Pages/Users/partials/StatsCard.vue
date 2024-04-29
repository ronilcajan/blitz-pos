<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    users: Object,
    filter: {
        type: String,
        default: 'all',
    },
});

const value = computed(() => {
    let count = 0;
    const filter = props.filter;
    if (filter === 'all') {
        count = props.users.length;
    } else {
        count = props.users.filter(user => user.status === filter).length;
    }
    return count;
});

const formatNumberWithCommas = (number) => {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

</script>

<template>
    <div class="stat">
        <div class="stat-title">{{ title }}</div>
        <div class="stat-value">{{ formatNumberWithCommas(value) }}</div>
    </div>
</template>
