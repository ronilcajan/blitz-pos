<template>
    <div class="overflow-y-auto">
        <table class="table table-zebra">
            <!-- Slot for TableHeader -->
            <slot name="table-header"></slot>
            
            <!-- Slot for TableBody -->
            <slot name="table-body"></slot>
        </table>
    </div>
</template>
  