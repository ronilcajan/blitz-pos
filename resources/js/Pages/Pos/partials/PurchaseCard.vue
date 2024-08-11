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
    <div class="flex justify-between items-center border border-gray-100 p-2 rounded">
        <div class="flex-1">
            <div class="flex gap-3 justify-start items-center">
                <div class="avatar">
                    <div class="w-12 rounded-full" v-if="item.image">
                        <img :src="item.image" />
                    </div>
                </div>
                <div class="">
                    <div class="font-bold text-sm">
                    {{ item.product }}
                    </div>
                    <div class="text-xs text-gray-500">
                    {{ item.details }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{  $page.props.auth.user.currency + " " +formatNumberWithCommas(item.price) }}/{{item.unit}}
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-1 hidden lg:block">
            <div class="font-bold flex justify-center items-center gap-2">
                <CounterInput v-model="item.qty" @increase-by="(n) => item.qty += n" @decreased-by="(n) => item.qty > 1 ? item.qty -= n : 0"/>
            </div>
        </div>

        <div class="flex-1 flex flex-col items-end ">
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
