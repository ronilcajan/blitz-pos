<script setup>

const props = defineProps({
    id: {
        type: String,
        required: true
    }
});

const printDiv = (divName) => {
    let printContents = document.getElementById(divName).innerHTML;
    let printWindow = window.open('', '_blank');
    let headTags = Array.from(document.head.children);
    printWindow.document.write('<html><head><title>Print</title>');
    headTags.forEach(tag => {
        if (tag.tagName === 'LINK' || tag.tagName === 'STYLE') {
            printWindow.document.write(tag.outerHTML);
        }
    });
    printWindow.document.write('</head><body>');
    printWindow.document.write(printContents);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.onload = function() {
        printWindow.print();
    }
}

</script>
<template>
   <DangerButton type="button"
        class="btn btn-sm"
        @click="printDiv(id)">
        <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linejoin="round" stroke-width="1" d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z"/>
        </svg>
        Print
    </DangerButton>
</template>
