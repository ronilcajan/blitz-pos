<script setup>
import CounterInput from './CounterInput.vue';

const props = defineProps({
    item: Object,
});

const emit = defineEmits(['deleteItem'])

const formatNumberWithCommas = (number) => {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>

<template>
    <div class="border border-gray-100 p-2 rounded flex justify-between items-center">
        <div class="flex gap-3 justify-center items-center">
            <div class="avatar">
                <div class="w-16 rounded-full" v-if="item.image">
                    <img :src="item.image" />
                </div>
            </div>
            <div class="">
                <div class="font-bold">
                {{ item.product }}
                </div>
                <div class="text-sm">
                {{ item.details }}
                </div>
                <div class="text-sm ">
                    {{  $page.props.auth.user.currency + " " +formatNumberWithCommas(item.price) }}/{{item.unit}}
                </div>
            </div>
        </div>
        <div class="font-bold hidden lg:flex justify-center items-center gap-2">
            <CounterInput v-model="item.qty" @increase-by="(n) => item.qty += n" @decreased-by="(n) => item.qty > 1 ? item.qty -= n : 0"/>
        </div>
        <div class="flex flex-col items-end ">
            <p class="font-bold mb-2 text-right text-primary">{{  $page.props.auth.user.currency + " " + formatNumberWithCommas(item.total) }}</p>

            <div class="flex gap-2 justify-center align-bottom">
                <div class="lg:hidden">
                    <CounterInput v-model="item.qty" @increase-by="(n) => item.qty += n" @decreased-by="(n) => item.qty > 1 ? item.qty -= n : 0"/>

                </div>
                <DeleteIcon @click="$emit('deleteItem')"/>
            </div>
        </div>
    </div>
</template>
