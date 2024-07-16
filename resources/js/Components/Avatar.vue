<script setup>

const props = defineProps({
    src: String
})

</script>

<template>
    <div class="avatar btn btn-circle btn-ghost" v-if="src">
        <div class="w-10 rounded-full">
            <img :src="src" />
        </div>
    </div>
</template>