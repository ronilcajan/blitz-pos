<script setup>
import GuestLayout from '@/Layouts/GuestLayout1.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { reactive, onMounted, ref } from 'vue';

defineOptions({ layout: GuestLayout });

const form = useForm({
    name: '',
    email: '',
    order: 'monthly',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const countries = reactive({});
const country = ref('');

const timezone = ref('');
const currency = ref('');

onMounted(() => {
    fetch('/json/country.json')
        .then(res => res.json())
        .then(data => {
            countries.data = data;
        });
});

const countryChange = () => {
    timezone.value = country.value.timezones[0].name || '';
    currency.value = country.value.currency || '';
}

</script>

<template>

    <Head title="Order  Registration" />

    <div class="
        relative
        z-10
        pt-[100px]
        md:pt-[50px]
        lg:pt-[90px]
        pb-[60px]
        bg-primary
        overflow-hidden
      ">
        <div class="container">
            <div class="flex flex-wrap items-center -mx-4">
                <div class="w-full px-4">
                    <div class="text-center">
                        <h1 class="font-semibold text-white text-4xl">You’re almost there! Complete your order
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <span class="absolute top-0 left-0 z-[-1]">
                <svg width="495" height="470" viewBox="0 0 495 470" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="55" cy="442" r="138" stroke="white" stroke-opacity="0.04" stroke-width="50"></circle>
                    <circle cx="446" r="39" stroke="white" stroke-opacity="0.04" stroke-width="20"></circle>
                    <path d="M245.406 137.609L233.985 94.9852L276.609 106.406L245.406 137.609Z" stroke="white"
                        stroke-opacity="0.08" stroke-width="12"></path>
                </svg>
            </span>
            <span class="absolute top-0 right-0 z-[-1]">
                <svg width="493" height="470" viewBox="0 0 493 470" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="462" cy="5" r="138" stroke="white" stroke-opacity="0.04" stroke-width="50"></circle>
                    <circle cx="49" cy="470" r="39" stroke="white" stroke-opacity="0.04" stroke-width="20"></circle>
                    <path d="M222.393 226.701L272.808 213.192L259.299 263.607L222.393 226.701Z" stroke="white"
                        stroke-opacity="0.06" stroke-width="13"></path>
                </svg>
            </span>
        </div>
    </div>


    <div class="container mx-auto px-4 my-10 max-w-[780px]">
        <div class="flex gap-3">
            <p>Selected plan:</p>
            <span class="font-semibold">Basic Plan</span>
        </div>

        <div class="flex gap-3 mt-20 flex-col">
            <h1 class="text-4xl font-bold ">1. Check your order </h1>

            <div class="mt-4 gap-4 flex mt-6 flex-col md:flex-row">
                <div class="p-5 px-10 bg-gray-100 shadow-sm rounded flex flex-col items-center cursor-pointer"
                    @click="form.order = 'monthly'">
                    <div class="flex gap-3 justify-center">
                        <input type="radio" name="variant" class="radio radio-primary radio-sm" id="monthly"
                            value="monthly" checked v-model="form.order" />
                        <p class="font-semibold" for="monthly">Monthly</p>
                    </div>

                    <p class="text-5xl font-semibold mt-6">₱ 399.00</p>
                    <p class="text-sm mt-3 font-bold">PHP / month</p>

                    <p class="text-sm mt-2"> 1 month free trial</p>

                </div>
                <div class="p-5 bg-gray-100 shadow-sm rounded flex flex-col items-center cursor-pointer"
                    @click="form.order = 'yearly'">
                    <div class="flex gap-3 justify-center">
                        <input type="radio" name="variant" class="radio radio-primary radio-sm" value="yearly"
                            id="yearly" v-model="form.order" />
                        <p class="font-semibold" for="yearly">Yearly</p>
                    </div>
                    <p class="text-sm mt-6 text-muted line-through ">₱ 798.00</p>
                    <p class="text-5xl font-semibold ">₱ 3,999.00</p>
                    <p class="text-sm mt-3 font-bold">PHP / year</p>

                    <p class="text-sm mt-2"> 1 month free trial</p>
                    <p class="text-sm mt-2 badge badge-primary"> Saves up to two months</p>
                </div>
            </div>
        </div>

        <div class="flex gap-3 mt-24 flex-col">
            <h1 class="text-4xl font-bold ">2. Create your account </h1>
            <div class="flex flex-col sm:justify-center sm:pt-0">
                <form @submit.prevent="submit">
                    <div>
                        <InputLabel for="name" value="Name" />

                        <TextInput id="name" type="text" class="block w-full" v-model="form.name" required autofocus
                            autocomplete="name" placeholder="Enter name" />

                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="mt-4">
                        <InputLabel for="email" value="Email" />

                        <TextInput id="email" type="email" class="block w-full" v-model="form.email" required
                            autocomplete="username" placeholder="Enter email" />

                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="mt-4">
                        <InputLabel for="password" value="Password" />

                        <TextInput id="password" type="password" class="block w-full" v-model="form.password" required
                            autocomplete="new-password" placeholder="Enter password" />

                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="mt-4">
                        <InputLabel for="password_confirmation" value="Confirm Password" />

                        <TextInput id="password_confirmation" type="password" class="block w-full"
                            v-model="form.password_confirmation" required autocomplete="new-password"
                            placeholder="Confirm password" />

                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>
                </form>
            </div>

        </div>
        <div class="flex gap-3 mt-24 flex-col">
            <h1 class="text-4xl font-bold ">3. Create your store </h1>
            <div class="flex flex-col sm:justify-center sm:pt-0">
                <form @submit.prevent="submit">
                    <div>
                        <InputLabel for="name" value="Store" />

                        <TextInput type="text" class="block w-full" v-model="form.name" required autofocus
                            autocomplete="name" placeholder="Enter store name" />

                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="mt-4">
                        <InputLabel for="email" value="Email" />

                        <TextInput type="email" class="block w-full" v-model="form.email" required
                            autocomplete="username" placeholder="Enter email" />

                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                    <div class="mt-4">
                        <InputLabel value="Select Country" />
                        <select class="select select-bordered w-full " v-model="country" @change="countryChange">
                            <option disabled selected value="">Select your country</option>
                            <option v-for="country in countries.data" :value="country" :key="country.code">
                                {{ country.name }}
                            </option>
                        </select>
                    </div>
                    <div class="mt-4">
                        <InputLabel value="Select Timezone" />
                        <select class="select select-bordered w-full " v-model="timezone">
                            <option disabled selected value="">Choose timezone</option>
                            <option v-for="country in countries.data" :value="country.timezones[0].name"
                                :key="country.code">
                                {{ country.timezones[0].name }}
                            </option>
                        </select>
                    </div>
                    <div class="mt-4">
                        <InputLabel value="Select Currency" />
                        <select class="select select-bordered w-full " v-model="currency">
                            <option disabled selected value="">Choose currency</option>
                            <option v-for="country in countries.data" :value="country.currency" :key="country.code">
                                {{ country.currency }}
                            </option>
                        </select>
                    </div>
                </form>
            </div>

        </div>

        <div class="flex gap-3 mt-24 flex-col">
            <h1 class="text-4xl font-bold ">4. Proceed to payment </h1>

            <div class="flex gap-3 justify-between text-2xl bg-gray-100 p-3 shadow-sm font-bold mt-4">
                <p>Total amount</p>
                <span class="font-bold">₱ 10,000.00</span>
            </div>

            <button class="bg-primary text-white px-6 py-2 rounded hover:text-primary-600 mt-5 btn-lg"
                @click="submit">Pay Now</button>

            <p class="mt-4">
                By checking out you agree with our
                <a href="#" class="text-primary">Terms of Service</a>
                and confirm that
                you have read our
                <a href="#" class="text-primary">Privacy Policy</a>.
                You can cancel recurring payments at any time.
            </p>
        </div>

    </div>

</template>
