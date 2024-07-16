<template>
    <div class="flex-grow p-2 lg:gap-x-12 lg:p-4 lg:pt-2 flex flex-col justify-center items-center border-dashed border-2">
        <div class="flex flex-col gap-3 justify-center items-center w-2/3">
            <p class="text-lg font-semibold">
                No {{ title.toLocaleLowerCase() }} found!
            </p>
            <p class="text-center text-muted text-sm mb-5">
                Your {{ title.toLocaleLowerCase() }} will be displayed here.</p>
            
                <slot />
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    title: String
})
</script>