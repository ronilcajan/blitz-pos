<script setup>
import { usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted  } from 'vue'
import { useCurrencyInput } from 'vue-currency-input'

const props = defineProps({
        modelValue: {
        type: Number,
        required: true
  },
});
const page = usePage();
// Emit event for two-way binding and change event
const emit = defineEmits(['update:modelValue', 'change'])
const options = { 
  "currency": page.props.auth.user.currency,
  "currencyDisplay": "symbol",
  "precision": 2,
  "hideCurrencySymbolOnFocus": true,
  "hideGroupingSeparatorOnFocus": true,
  "hideNegligibleDecimalDigitsOnFocus": true,
  "autoDecimalDigits": false,
  "useGrouping": true,
  "accountingSign": false
 }
// Create a ref for the input element
const inputRef = ref(null)

// Initialize the currency input functionality
const { inputRef: currencyInputElement, setValue  } = useCurrencyInput(options)

// Watch for changes in the modelValue prop
watch(() => props.modelValue, (newValue) => {
    if (inputRef.value) {
        inputRef.value.value = newValue
    }
    setValue(newValue)
    console.log(newValue);
    
})

// Emit the updated value and change event on input
const onInput = (event) => {
    const value = parseFloat(event.target.value.replace(/[^0-9.-]+/g, ''))
    emit('update:modelValue', value)
    emit('change', value)  // Emit the change event
}

onMounted(() => {
    if (currencyInputElement.value.hasAttribute('autofocus')) {
        currencyInputElement.value.focus();
    }
});
defineExpose({ focus: () => currencyInputElement.value.focus() });

</script>

<template>
  <input
    ref="currencyInputElement"
    :value="modelValue"
    @input="onInput" @change="onInput"
  />
</template>
