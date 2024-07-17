<template>
    <div class="flex flex-col gap-4">
      <div class="flex gap-2">
        <template v-for="option in quickSelections" :key="option.value">
          <button
            @click="applyQuickSelection(option.value)"
            :class="['px-4 py-2 rounded', quickSelection === option.value ? 'bg-blue-500 text-white' : 'bg-gray-200']"
            class="transition-colors duration-200 ease-in-out hover:bg-blue-600 hover:text-white"
          >
            {{ option.label }}
          </button>
        </template>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';

  const quickSelections = [
    { label: 'Today', value: 'today' },
    { label: 'This Week', value: 'thisWeek' },
    { label: 'This Month', value: 'thisMonth' },
    { label: 'Last Month', value: 'lastMonth' },
  ];
  
  const handleDateRangeChange = (range) => {
    startDate.value = range[0];
    endDate.value = range[1];
    quickSelection.value = 'customRange';
  };
  
  // Emit the date range change event to update the sales bar chart
  const emitDateRangeChange = () => {
    const payload = { startDate: startDate.value, endDate: endDate.value };
    // Emit the event or call the method to update the chart data
    // e.g., this.$emit('dateRangeChange', payload);
    // Use the emit method to pass the payload to the parent component
    this.$emit('dateRangeChange', payload);
  };
  </script>
  
  <style scoped>
  /* If you need additional styling, you can add it here */
  </style>
  