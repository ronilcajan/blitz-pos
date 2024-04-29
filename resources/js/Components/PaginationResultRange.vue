<script setup>
defineProps({ data: Object })
</script>

<template>
   <div class="flex justify-center">
        <small>
            Showing {{ data.from }} to  {{ data.to }} of  {{ data.total }} results
        </small>
    </div>
  </template>

