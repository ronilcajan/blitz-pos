<script setup>
import { reactive } from 'vue'

import GuestLayout from '@/Layouts/GuestLayout.vue';
import Hero from './partials/Hero.vue';
import Feature from './partials/Feature.vue';
import About from './partials/About.vue';
import FAQs from './partials/FAQs.vue';
import Contact from './partials/Contact.vue';
import Pricing from './partials/Pricing.vue';

defineOptions({ layout: GuestLayout });

const props = defineProps({
    products: Object
});


const inquiryForm = reactive({
    name: '',
    email: '',
    contact: '',
    subject: '',
    message: '',
})

const plans = reactive({});

</script>

<template>

    <Head title="Home" />

    <Hero />
    <Feature />
    <About />
    <Pricing :plans="products" />
    <FAQs />
    <Contact :inquiryForm="inquiryForm" />
</template>
