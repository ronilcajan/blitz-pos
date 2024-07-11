<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex flex-col items-center min-h-screen pt-6 bg-base-300 sm:justify-center sm:pt-0">
        <div>
            <Link href="/">
                <ApplicationLogo class="fill-current" />
            </Link>
        </div>

        <div class="w-full col-span-12 px-8 py-6 mt-6 shadow-md sm:max-w-md card bg-base-100 xl:col-span-3">
            <slot />
        </div>
    </div>
</template>
