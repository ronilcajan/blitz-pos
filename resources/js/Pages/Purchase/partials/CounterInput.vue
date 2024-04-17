<script setup>

const props = defineProps({ 
    initialValue: {
        type: Number,
        default: 1
    }
})

let count = props.initialValue;

const decrement = () => {
    count--;
}
const increment = () => {
    count++;

}

</script>
<template>
    <div class="join bg-base-100 border mb-3">
      <button class="btn btn-ghost join-item btn-xs" @click="decrement">–</button>
      <input class="input join-item input-ghost input-xs w-10 text-center" v-model="props.initialValue">
      <button class="btn btn-ghost join-item btn-xs" @click="increment">+</button>
    </div>
</template>
  

  