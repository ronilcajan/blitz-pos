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
    <div class="drawer min-h-screen bg-base-200 lg:drawer-open">
        <input id="my-drawer" type="checkbox" class="drawer-toggle" />
		<!-- content -->

		<main class="drawer-content">
            <div class="grid grid-cols-12 grid-rows-[min-content] p-4">
                <div v-if="impersonating" tabindex="-1" class="fixed bg-primary top-0 start-0 z-50 flex justify-between w-full p-2">
                    <div class="flex items-center mx-auto text-white">
                        <p class="flex items-center text-sm font-normal ">
                            <span class="inline-flex p-1 me-3  rounded-full dark:bg-gray-600 w-6 h-6 items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 19">
                                    <path d="M15 1.943v12.114a1 1 0 0 1-1.581.814L8 11V5l5.419-3.871A1 1 0 0 1 15 1.943ZM7 4H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2v5a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2V4ZM4 17v-5h1v5H4ZM16 5.183v5.634a2.984 2.984 0 0 0 0-5.634Z"/>
                                </svg>
                                <span class="sr-only">Light bulb</span>
                            </span>
                            <span>You're impersonating {{ $page.props.auth.user.name }}.
                                <NavLink :href="route('user.leave')" class="inline font-medium underline underline-offset-2 decoration-600 decoration-solid hover:no-underline">
                                    Leave Now</NavLink></span>
                        </p>
                    </div>
                </div>
                <HeaderBar />
            </div>
            <div class="p-4 lg:gap-x-12 lg:p-10 lg:pt-2 min-h-screen mt-2">
                <slot />

                <footer class="footer footer-center mt-20 p-4 w-full">
                    <aside>
                        <p class="text-center">Copyright © 2024 - All rights reserved by ACME Industries Ltd</p>
                    </aside>
                </footer>
            </div>

		</main>

		<!-- /content -->
        <SideBar />

    </div>


</template>
