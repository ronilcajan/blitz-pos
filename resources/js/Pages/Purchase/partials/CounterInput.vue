<script setup>
const emit = defineEmits(['decreasedBy', 'increaseBy'])

const model = defineModel({
    type: Number,
    required: true,
});

</script>
<template>
    <div class="join bg-base-100 border mb-3">
      <button class="btn btn-ghost join-item btn-sm" @click="$emit('decreasedBy',1)">–</button>
      <input class="input join-item input-ghost input-sm w-14 text-center" v-model="model" type="number" step="0.01" min="0.00">
      <button class="btn btn-ghost join-item btn-sm" @click="$emit('increaseBy',1)">+</button>
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
