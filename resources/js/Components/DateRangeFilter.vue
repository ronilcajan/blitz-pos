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
  
      <div v-if="quickSelection === 'customRange'" class="flex gap-2 items-center">
        <!-- <DateRangePicker v-model="dateRange" @change="handleDateRangeChange" /> -->
        <date-picker v-model="dateRange" />

      </div>
  
      <div class="flex justify-end">
        <button
          @click="emitDateRangeChange"
          class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors duration-200 ease-in-out"
        >
          Apply
        </button>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import DatePicker from 'vue-datepicker-next';
  import 'vue-datepicker-next/index.css';

  const startDate = ref(null);
  const endDate = ref(null);
  const dateRange = ref([null, null]);
  const quickSelection = ref('');
  
  const quickSelections = [
    { label: 'Today', value: 'today' },
    { label: 'This Week', value: 'thisWeek' },
    { label: 'This Month', value: 'thisMonth' },
    { label: 'Last Month', value: 'lastMonth' },
    { label: 'Custom Range', value: 'customRange' },
  ];
  
  const applyQuickSelection = (selection) => {
    quickSelection.value = selection;
    const now = new Date();
  
    switch (selection) {
      case 'today':
        startDate.value = new Date(now.setHours(0, 0, 0, 0));
        endDate.value = new Date(now.setHours(23, 59, 59, 999));
        break;
      case 'thisWeek':
        const firstDayOfWeek = now.getDate() - now.getDay();
        startDate.value = new Date(now.setDate(firstDayOfWeek));
        endDate.value = new Date(now.setDate(firstDayOfWeek + 6));
        break;
      case 'thisMonth':
        startDate.value = new Date(now.getFullYear(), now.getMonth(), 1);
        endDate.value = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        break;
      case 'lastMonth':
        startDate.value = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        endDate.value = new Date(now.getFullYear(), now.getMonth(), 0);
        break;
      case 'customRange':
        alert('Custom range selection not implemented yet');
        startDate.value = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        endDate.value = new Date(now.getFullYear(), now.getMonth(), 0);
        dateRange.value = [null, null];
        break;
    }
  };
  
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
  