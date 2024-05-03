<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();

</script>
<template>
    <div class="flex items-center">
        <img :src="$page.props.logo" alt="Laravel" class="h-8" />
    </div>
</template>
