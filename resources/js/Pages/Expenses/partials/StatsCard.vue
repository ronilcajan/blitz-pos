<script setup>
import { usePage } from '@inertiajs/vue3'

const page = usePage();

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    expenses: String,
});
const formatNumberWithCommas = (number) => {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

</script>

<template>
    <div class="stat">
        <div class="stat-title">{{ title }}</div>
        <div class="stat-value">{{ $page.props.auth.user.currency+ ' ' +formatNumberWithCommas(expenses) }}</div>
        <div class="stat-desc">
            <slot/>
        </div>
    </div>
</template>
