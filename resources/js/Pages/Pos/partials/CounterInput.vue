<script setup>
const emit = defineEmits(['decreasedBy', 'increaseBy'])

const model = defineModel({
    type: Number,
    required: true,
});

</script>
<template>
    <div class="join bg-base-100 border">
      <button type="button" class="btn btn-ghost join-item btn-xs" @click="$emit('decreasedBy',1)">–</button>
      <input class="input join-item input-ghost w-8 p-0 input-xs text-center" v-model="model" type="number" step="0.01" min="0.00">
      <button type="button" class="btn btn-ghost join-item btn-xs" @click="$emit('increaseBy',1)">+</button>
    </div>
</template>


<style scoped>
/* Remove spinner from number input */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0; /* Optional */
}
</style>
