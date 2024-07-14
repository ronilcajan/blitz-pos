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

    <div class="min-h-screen drawer bg-base-200 lg:drawer-open">
        <input id="my-drawer" type="checkbox" class="drawer-toggle" />
        <!-- content -->
        <main class="drawer-content">
            <div v-if="impersonating" class="fixed top-0 z-50 flex justify-between w-full p-2 bg-primary start-0">
                <div class="flex items-center mx-auto text-white">
                    <p class="flex items-center text-sm font-normal ">
                        <span
                            class="inline-flex items-center justify-center flex-shrink-0 w-6 h-6 p-1 rounded-full me-3 dark:bg-gray-600">
                            <svg class="w-3 h-3 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor" viewBox="0 0 18 19">
                                <path
                                    d="M15 1.943v12.114a1 1 0 0 1-1.581.814L8 11V5l5.419-3.871A1 1 0 0 1 15 1.943ZM7 4H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2v5a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2V4ZM4 17v-5h1v5H4ZM16 5.183v5.634a2.984 2.984 0 0 0 0-5.634Z" />
                            </svg>
                            <span class="sr-only">Light bulb</span>
                        </span>
                        <span>You're impersonating {{ $page.props.auth.user.name }}.
                            <NavLink :href="route('user.leave')"
                                class="inline font-medium underline underline-offset-2 decoration-600 decoration-solid hover:no-underline">
                                Leave Now</NavLink>
                        </span>
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-12 grid-rows-[min-content] p-4 pt-4 pb-0" :class="{ 'mt-10': impersonating }">

                <HeaderBar />
            </div>
            <div class="min-h-screen p-2 lg:gap-x-12 lg:p-4 lg:pt-2">

                <slot />

                <footer class="w-full p-4 mt-20 footer footer-center">
                    <aside>
                        <p class="text-center">Copyright © 2024 - All rights reserved by {{ $page.props.app_name }}</p>
                    </aside>
                </footer>
            </div>

        </main>

        <!-- /content -->
        <SideBar />

    </div>


</template>
