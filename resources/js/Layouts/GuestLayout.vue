<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-base-300 flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div>
            <Link href="/">
                <ApplicationLogo class="fill-current" />
            </Link>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-8 py-6 card col-span-12 bg-base-100 shadow-md xl:col-span-3"
        >
        <slot />
        </div>
    </div>
</template>
