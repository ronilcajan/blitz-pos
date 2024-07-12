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
            <div class="grid grid-cols-12 grid-rows-[min-content] p-4">

                <HeaderBar />
            </div>
            <div class="min-h-screen p-3 lg:gap-x-12 lg:p-4 lg:pt-2">

                <slot />

                <footer class="w-full p-4 mt-20 footer footer-center">
                    <aside>
                        <p class="text-center">Copyright © 2024 - All rights reserved by {{ $page.props.app_name }}</p>
                    </aside>
                </footer>
            </div>

		</main>

		<!-- /content -->
        <aside class="z-10 drawer-side" >
            <label for="my-drawer" class="drawer-overlay"></label>
            <!-- sidebar menu -->
            <nav class="flex flex-col min-h-screen gap-2 px-6 py-10 overflow-y-auto w-72 bg-base-100">

                <div class="flex items-center gap-2 mx-4 font-black">
                    <Link :href="route('admin.dashboard')">
                        <ApplicationLogo class="fill-current" />
                    </Link>
                </div>

                <ul class="gap-2 mt-4 menu">
                    <li>
                        <NavLink :active="$page.url.startsWith('/admin/dashboard') " :href="route('admin.dashboard')">
                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-home"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                            Dashboard
                        </NavLink>
                    </li>
                    <li>
                        <NavLink :active="$page.url.startsWith('/admin/stores') " href="/admin/stores">
                            <svg  xmlns="http://www.w3.org/2000/svg"  width="22"  height="22"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-building-store"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 7v1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1h-18l2 -4h14l2 4" /><path d="M5 21l0 -10.15" /><path d="M19 21l0 -10.15" /><path d="M9 21v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4" /></svg>
                            Store
                        </NavLink>
                    </li>
                    <li>
                        <NavLink :active="$page.url.startsWith('/admin/users') " href="/admin/users">
                            <svg  xmlns="http://www.w3.org/2000/svg"  width="22"  height="22"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-users"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                            Users
                        </NavLink>
                    </li>
                </ul>
            </nav>
                <!-- /sidebar menu -->
        </aside>

    </div>


</template>
