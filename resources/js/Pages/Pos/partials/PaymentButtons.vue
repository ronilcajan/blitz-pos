<script setup>
const emit = defineEmits(['setPayment'])
defineProps({
    currency: String
})

</script>
<template>
    <div class="flex gap-2 justify-end mt-1 flex-wrap">
      <button class="btn btn-sm" @click="$emit('setPayment',20)">{{ $page.props.auth.user.currency+ ' ' + currency }} 20</button>
      <button class="btn btn-sm" @click="$emit('setPayment',50)">{{ $page.props.auth.user.currency+ ' ' +currency }} 50</button>
      <button class="btn btn-sm" @click="$emit('setPayment',100)">{{ $page.props.auth.user.currency+ ' ' +currency }} 100</button>
      <button class="btn btn-sm" @click="$emit('setPayment',200)">{{ $page.props.auth.user.currency+ ' ' +currency }} 200</button>
      <button class="btn btn-sm" @click="$emit('setPayment',500)">{{ $page.props.auth.user.currency+ ' ' +currency }} 500</button>
      <button class="btn btn-sm" @click="$emit('setPayment',1000)">{{ $page.props.auth.user.currency+ ' ' +currency }} 1,000</button>
      <button class="btn btn-sm" @click="$emit('setPayment',5000)">{{ $page.props.auth.user.currency+ ' ' +currency }} 5,000</button>
      <button class="btn btn-sm" @click="$emit('setPayment',10000)">{{ $page.props.auth.user.currency+ ' ' +currency }} 10,000</button>
      <DangerButton class="btn btn-sm" @click="$emit('setPayment','reset')">Reset</DangerButton>
    </div>
  </template>
