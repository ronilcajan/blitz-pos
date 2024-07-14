<script setup>
import { reactive } from 'vue'

import GuestLayout from '@/Layouts/GuestLayout1.vue';
import Hero from './partials/Hero.vue';
import Feature from './partials/Feature.vue';
import About from './partials/About.vue';
import FAQs from './partials/FAQs.vue';
import Contact from './partials/Contact.vue';
import Pricing from './partials/Pricing.vue';

defineOptions({ layout: GuestLayout });

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    }
});


const inquiryForm = reactive({
    name: '',
    email: '',
    contact: '',
    subject: '',
    message: '',
})


</script>

<template>

    <Head title="Home" />
    <Hero />
    <Feature />
    <About />
    <Pricing />
    <FAQs />
    <Contact :inquiryForm="inquiryForm" />
</template>
