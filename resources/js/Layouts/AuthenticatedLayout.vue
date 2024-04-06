<script setup>
import { ref } from 'vue';
import HeaderBar from '@/Shared/HeaderBar.vue';
import SideBar from '@/Shared/SideBar.vue';


</script>
<template>
    <div class="drawer min-h-screen bg-base-200 lg:drawer-open">
        <input id="my-drawer" type="checkbox" class="drawer-toggle" />
		<!-- content -->
		<main class="drawer-content">
            <div class="grid grid-cols-12 grid-rows-[min-content] gap-y-12 p-4 lg:gap-x-12 lg:p-10">
            <HeaderBar />
                <slot />
            </div>
		</main>
		<!-- /content -->
        <SideBar />
    </div>
		
</template>