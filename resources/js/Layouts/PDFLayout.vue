<script setup>
import { computed } from 'vue';
import HeaderBar from '@/Shared/HeaderBar.vue';
import SideBar from '@/Shared/SideBar.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const impersonating = computed(() =>
    page.props.auth.user.impersonate !== null ? true : false
)

</script>
<template>

    <main class="drawer-content">
        <div class="p-4 lg:gap-x-12 lg:p-10 lg:pt-2 min-h-screen mt-2">
            <slot />

        </div>

    </main>


</template>
