<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue';

const props = defineProps({
    plans: Object
});

const selectPlan = (plan) => {
    // console.log(plan.id);
    router.get(route('register', plan.id));
}

const plansIsMonthly = ref(true);
const changePricing = () => {
    plansIsMonthly.value = !plansIsMonthly.value;
}

</script>
<template>
    <section id="pricing" class="
     bg-white
     pt-20
     lg:pt-[120px]
     pb-12
     lg:pb-[90px]
     relative
     z-20
     overflow-hidden
     ">
        <div class="container">
            <div class="flex flex-wrap -mx-4">
                <div class="w-full px-4">
                    <div class="text-center mx-auto mb-[60px] lg:mb-10 max-w-[620px]">
                        <span class="block mb-2 text-lg font-semibold text-primary">
                            Pricing Table
                        </span>
                        <h2 class="font-bold text-3xl sm:text-4xl md:text-[40px] text-dark mb-4">
                            Choose the Plan That’s Right for You
                        </h2>
                        <p class="text-lg leading-relaxed sm:text-xl sm:leading-relaxed text-body-color">
                            Select from our flexible pricing plans designed to meet the needs of businesses of all sizes
                            and stages.
                        </p>
                        <div class="mt-10 flex justify-center ">
                            <label class="label cursor-pointer gap-3">
                                <input type="checkbox" class="toggle toggle-primary"  @change="changePricing" />
                                <span class="text-xl font-semibold">Pay annually and
                                    <span class="text-primary relative">save up to 17%

                                        <span class="absolute mt-6 right-4">
                                            <svg width="126" height="11" viewBox="0 0 126 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.39429 7.30524L1.95739 6.94095C23.114 3.08745 69.9537 -3.24652 118.067 4.18479L125.206 7.15758C118.647 6.14449 111.958 5.38936 105.251 4.85001C63.9115 1.89605 23.1258 6.61143 5.3761 10.3878L0.000390253 10.333L3.33004 9.06123L2.83185 7.42404C3.01764 7.38451 3.20513 7.34491 3.39429 7.30524Z" fill="#335DFF"/>
                                            </svg>

                                        </span>

                                    </span>
                                </span>
                            </label>

                        </div>

                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-center">
                <div class="w-full md:w-1/2 lg:w-1/3">
                    <div class="relative z-10 px-8 py-10 mb-10 overflow-hidden text-center bg-white border rounded-xl border-primary border-opacity-20 shadow-pricing sm:p-12 lg:py-10 lg:px-6 xl:p-12 wow fadeInUp"
                        data-wow-delay=".15s">
                        <span class="block mb-2 text-base font-medium uppercase text-dark">
                            Free Plan
                        </span>
                        <h2 class="font-semibold text-primary mb-9 text-[28px]">
                            0.00/mo
                        </h2>
                        <div class="mb-10 text-base font-medium leading-loose text-body-color">
                            <p class="mb-1">
                                1 User
                            </p>
                            <p class="mb-1">
                                Limited Features (Products, Expenses, Sales)
                            </p>
                            <p class="mb-1">
                                Lifetime access
                            </p>
                            <p class="mb-1">
                                Free updates
                            </p>
                            <p class="mb-1">
                                Use on 1 (one) store
                            </p>
                        </div>
                        <div class="w-full">
                            <Link :href="route('register','free_plan')"
                                class="btn btn-lg btn-primary btn-outline inline-block text-base font-medium text-primary bg-transparent border border-[#D4DEFF] rounded-full text-center py-4 px-11 hover:text-white hover:bg-primary hover:border-primary transition duration-300 ease-in-out">
                                Get Started for Free
                            </Link>
                        </div>
                        <span class="absolute left-0 bottom-0 z-[-1] w-14 h-14 rounded-tr-full block bg-primary">
                        </span>
                    </div>
                </div>
                <div class="w-full md:w-1/2 lg:w-1/3" v-for="(plan, index) in plans.data" :key="index">
                    <div class="bg-primary bg-gradient-to-b from-primary to-[#179BEE] rounded-xl relative z-10 overflow-hidden shadow-pricing py-10 px-8 sm:p-12 lg:py-10 lg:px-6 xl:p-12 mb-10 text-center wow fadeInUp"
                        data-wow-delay=".1s" v-if="index === 0">
                        <span
                            class="inline-block px-6 py-2 mb-5 text-base font-semibold uppercase bg-white border border-white rounded-full text-primary">
                            Popular
                        </span>
                        <span class="block mb-2 text-base font-medium text-white uppercase">
                            {{ plan.attributes.name }}
                        </span>
                        <h2 class="font-semibold text-white mb-9 text-[28px]" v-if="plansIsMonthly">
                            {{ plan.attributes.from_price_formatted }}/mo
                        </h2>

                        <div v-if="!plansIsMonthly">
                            <h2 class="font-semibold text-white text-[28px]" >
                                {{ plan.attributes.to_price_formatted }}/yr
                            </h2>
                            <span class="inline-block px-5 py-1 mb-5 text-xs font-medium text-dark bg-white rounded">
                                save up to {{ ((plan.attributes.price/100) * 2).toFixed(2) }}
                            </span>
                        </div>

                        <div class="mb-10">
                            <p class="text-white mb-1 text-base font-medium leading-loose text-body-color"
                                v-html="plan.attributes.description"></p>
                        </div>
                        <div class="w-full">
                            <Link type="button" :href="route('register', plan.id)"
                                class="btn btn-lg btn-primary btn-outline inline-block py-5 text-base font-medium text-center transition duration-300 ease-in-out bg-white border border-white rounded-full text-dark px-11 hover:text-white hover:bg-dark hover:border-dark">
                                Upgrade to Basic
                            </Link>
                        </div>
                    </div>

                    <div class="relative z-10 px-8 py-10 mb-10 overflow-hidden text-center bg-white border rounded-xl border-primary border-opacity-20 shadow-pricing sm:p-12 lg:py-10 lg:px-6 xl:p-12 wow fadeInUp"
                        data-wow-delay=".15s" v-else>
                        <span class="block mb-2 text-base font-medium uppercase text-dark">
                            {{ plan.attributes.name }}
                        </span>
                        <h2 class="font-semibold text-primary mb-9 text-[28px]" v-if="plansIsMonthly">
                            {{ plan.attributes.from_price_formatted }}/mo
                        </h2>

                        <div v-if="!plansIsMonthly">
                            <h2 class="font-semibold text-primary text-[28px] " >
                                {{ plan.attributes.to_price_formatted }}/yr
                            </h2>
                            <span class="inline-block px-5 py-1 mb-5 text-xs font-medium text-white bg-primary rounded">
                                save up to
                                {{ ((plan.attributes.price/100) * 2).toFixed(2) }}
                            </span>
                        </div>

                        <div class="mb-10">
                            <p clas s="mb-10 mb-1 text-base font-medium leading-loose text-body-color" v-html="plan.attributes.description">
                            </p>
                        </div>

                        <div class="w-full">
                            <Link type="button" :href="route('register', plan.id)"
                                class="btn btn-lg btn-primary btn-outline inline-block text-base font-medium text-primary bg-transparent border border-[#D4DEFF] rounded-full text-center py-5 px-11 hover:text-white hover:bg-primary hover:border-primary transition duration-300 ease-in-out">
                                Upgrade to Standard
                            </Link>
                        </div>
                        <span class="
                    absolute
                    right-0
                    top-0
                    z-[-1]
                    w-14
                    h-14
                    rounded-bl-full
                    block
                    bg-secondary
                    ">
                        </span>
                    </div>
                </div>
                <div class="w-full md:w-1/2 lg:w-1/3">

                </div>
            </div>
        </div>
    </section>
</template>
