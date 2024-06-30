<script setup>

    const props = defineProps({
        productIds: {
            type: Array,
            required: true,
        },
        exportExcelRoute: {
            type: String,
            required: true,
        },
        exportPDFRoute: {
            type: String,
            required: true,
        },
    })
</script>
<template>
    <div class="dropdown dropdown-bottom dropdown-end">
        <SecondaryButton tabindex="0" role="button"  class="btn-sm btn-outline">
            <svg  xmlns="http://www.w3.org/2000/svg"  width="18"  height="18"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-tool"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 10h3v-3l-3.5 -3.5a6 6 0 0 1 8 8l6 6a2 2 0 0 1 -3 3l-6 -6a6 6 0 0 1 -8 -8l3.5 3.5" /></svg> Actions
        </SecondaryButton>
        <div tabindex="0" class="dropdown-content menu rounded bg-base-100 z-[1] w-44 shadow">
            <div class="font-bold border-b p-1 py-2">Exports</div>
            <div class="border-b gap-2">
                <a :href="exportExcelRoute" class="flex py-2 px-1 my-1 gap-1 items-center hover:bg-gray-100" title="Excel export">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="22"  height="22"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-spreadsheet"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M8 11h8v7h-8z" /><path d="M8 15h8" /><path d="M11 11v7" /></svg>
                    Excel export
                </a>
                <a :href="exportPDFRoute" class="flex py-2 px-1 my-1 gap-1 items-center hover:bg-gray-100" title="Excel export">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="22"  height="22"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-type-pdf"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" /><path d="M5 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" /><path d="M17 18h2" /><path d="M20 15h-3v6" /><path d="M11 15v6h1a2 2 0 0 0 2 -2v-2a2 2 0 0 0 -2 -2h-1z" /></svg>
                    PDF export
                </a>
            </div>
            <div class="font-bold border-b p-1 py-2">Import</div>
            <div class="gap-2 border-b">
                <button @click="$emit('open-import-modal')" class="flex py-2 px-1 my-1 gap-1 items-center hover:bg-gray-100 w-full" title="Excel import">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="22"  height="22"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-spreadsheet"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M8 11h8v7h-8z" /><path d="M8 15h8" /><path d="M11 11v7" /></svg>
                    Excel import
                </button>
            </div>
            <div class="font-bold border-b p-1 py-2">Bulk action</div>
            <div class="gap-2 " :class="{'pointer-events-none opacity-50': !productIds.length}">
                <button class="flex py-2 px-1 my-1 gap-1 items-center w-full hover:bg-gray-100 hover:text-orange-600"
                @click="$emit('delete-all-selected')">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="22"  height="22"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    Delete
                </button>
            </div>
        </div>
    </div>
</template>
