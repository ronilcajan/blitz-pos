<script setup>
import GuestLayout from '@/Layouts/GuestLayout1.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { reactive, onMounted, ref, computed } from 'vue';
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: GuestLayout });

const props = defineProps({
    title: String,
    variants: Object,
    product: Object,
    user_id: Number
});

const page = usePage();
const countries = reactive({});
const lemonsqueezy_api = page.props.app_lemon_squeezy_api;

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    store: '',
    country: 'Philippines',
    country_code: '',
    timezone: 'Asia/Manila',
    currency: 'PHP',
    order: 'monthly',
    product_id: props.product?.data.id,
    variant_id: props.variants?.data[0].id,
    total: '',
});

const countryChange = () => {
    countries.data.forEach((country) => {
        if (country.name === form.country ) {   
            form.timezone = country.timezones[0].name;
            form.currency = country.currency;
            form.country_code = country.code;
        }
    })
}

const filteredVariants = computed(() => {
    return props.variants.data.slice(1);
});

const formatPrice = (price) => {
    return (price / 100).toFixed(2);
}

const formatNumberWithCommas = (number) => {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

const showPassword = ref(false);
const inputType = ref('password');

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
    inputType.value = inputType.value === 'password' ? 'text' : 'password';
}

const total = ref(
    (formatPrice(filteredVariants.value[0].attributes.price)));

const toggleVarient = (variant) => {
    form.order = variant.attributes.name.toLowerCase();
    form.variant_id = variant.id;
    total.value = formatPrice(variant.attributes.price);
    form.total = formatNumberWithCommas(total.value);
}

const submit = async () => {
    form.post(route('register.store'), {
        onSuccess: async (response) => {

            const url = 'https://api.lemonsqueezy.com/v1/checkouts';
            const redirect_url = 'http://127.0.0.1:8000/dashboard';

            if(form.product_id !== 'free_plan') { // if not free plan
                const headers = {
                    'Accept': 'application/vnd.api+json',
                    'Content-Type': 'application/vnd.api+json',
                    'Authorization': `Bearer ${lemonsqueezy_api}`,
                };
                const data = {
                    data: {
                        type: 'checkouts',
                        attributes: {
                            product_options: {
                                redirect_url : redirect_url,
                                enabled_variants: [form.variant_id],
                            },
                            checkout_options: {
                                embed : true,
                                subscription_preview : false,
                                button_color: '#2DD272'
                            },
                            checkout_data: {
                                name: `${response.props.auth.user.name}`,
                                email: `${response.props.auth.user.email}`,
                                billing_address: {
                                    country: `${form.country_code}`,
                                },
                                custom: {
                                    user_id: `${response.props.auth.user.id}`,
                                }
                            },
                            preview: true
                        },
                        relationships: {
                            store: {
                                data: {
                                    type: 'stores',
                                    id: `${props.product.data.attributes.store_id}`
                                }
                            },
                            variant: {
                                data: {
                                    type: 'variants',
                                    id: `${form.variant_id}`
                                }
                            }
                        }
                    }
                };
                const res = await fetch(url, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(data)
                });
                const details = await res.json();  
                console.log('API Response:', details); // Log the entire response for debugging
                LemonSqueezy.Url.Open(details.data.attributes.url);
            }

            form.reset();
            form.clearErrors();
        },

        onError: (response) => {
            useToast().error(`Error! Please check the required fields to complete the order.`, {
                position: 'bottom-right',
                timeout: 3000,  
                dismissible: true
            })
            console.log(response);
        },

        preserveScroll: true,
        
    });
};

onMounted(() => {
    fetch('/json/country.json')
        .then(res => res.json())
        .then(data => {
            countries.data = data;
        });
});
</script>

<template>

    <Head :title="title" />

    <div class="
        relative
        z-10
        pt-[130px]
        md:pt-[70px]
        lg:pt-[120px]
        pb-[80px]
        bg-primary
        overflow-hidden
      ">
        <div class="container">
            <div class="flex flex-wrap items-center -mx-4">
                <div class="w-full px-4">
                    <div class="text-center">
                        <h1 class="font-semibold text-white text-4xl">You’re almost there! Complete your order.
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
            <span class="font-semibold">{{ product.data.attributes.name }}</span>
        </div>
        <!-- {{ form.errors }} -->
        <form @submit.prevent="submit">
            <div class="flex gap-3 mt-20 flex-col fadeInUp">
                <h1 class="text-4xl font-bold ">1. Check your order </h1>

                <div class="gap-4 flex mt-6 flex-col md:flex-row">
                    <div v-for="(variant, index) in filteredVariants" :key="variant.id"
                        class="p-5 px-10 bg-gray-100 shadow-sm rounded cursor-pointer" @click="toggleVarient(variant);">

                        <div v-if="index === 0" class="flex flex-col items-center">
                            <div class="flex gap-3 justify-center">
                                <input type="radio" :id="'variant-' + index" name="variant"
                                    class="radio radio-primary radio-sm" :value="variant.attributes.name.toLowerCase()"
                                    v-model="form.order"  required />
                                <p class="font-semibold" :for="'variant-' + index" checked>{{ variant.attributes.name }}
                                </p>
                            </div>
                            <p class="text-5xl font-semibold mt-6">
                                ₱{{ formatPrice(variant.attributes.price) }}</p>
                            <p class="text-sm mt-3 font-bold">PHP / month</p>
                            <p class="text-sm mt-2" v-if="product.data.attributes.id !== 'free_plan'">14 days free trial</p>
                        </div>
                        <div v-else class="flex flex-col items-center">
                            <div class="flex gap-3 justify-center">
                                <input type="radio" :id="'variant-' + index" name="variant"
                                    class="radio radio-primary radio-sm" :value="variant.attributes.name.toLowerCase()"
                                    v-model="form.order" required />
                                <p class="font-semibold" :for="'variant-' + index">{{ variant.attributes.name }}</p>
                            </div>
                            <p class="text-5xl font-semibold mt-6">
                                ₱{{ formatNumberWithCommas(formatPrice(variant.attributes.price)) }}
                            </p>
                            <p class="text-sm mt-3 font-bold">PHP / year</p>
                            <p class="text-sm mt-2">14 days free trial</p>
                            <p v-if="variant.attributes.name.toLowerCase() === 'yearly'"
                                class="text-xs md:text-sm mt-2 badge badge-primary">Save two months when you pay
                                annually.
                            </p>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.variant" />
                </div>
            </div>

            <div class="flex gap-3 mt-24 flex-col fadeInUp">
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

                            <div class="relative">
                                <TextInput id="password" class="block w-full" v-model="form.password" required
                                    :type="inputType" autocomplete="new-password" placeholder="Enter password" />

                                <button type="button" class="absolute inset-y-0 end-0 flex items-center pe-3"
                                    @click="togglePasswordVisibility">
                                    <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-eye">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                        <path
                                            d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-eye-closed">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M21 9c-2.4 2.667 -5.4 4 -9 4c-3.6 0 -6.6 -1.333 -9 -4" />
                                        <path d="M3 15l2.5 -3.8" />
                                        <path d="M21 14.976l-2.492 -3.776" />
                                        <path d="M9 17l.5 -4" />
                                        <path d="M15 17l-.5 -4" />
                                    </svg>
                                </button>
                            </div>


                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password_confirmation" value="Confirm Password" />

                            <TextInput id="password_confirmation" :type="inputType" class="block w-full"
                                v-model="form.password_confirmation" required autocomplete="new-password"
                                placeholder="Confirm password" />

                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>
                    </form>
                </div>

            </div>
            <div class="flex gap-3 mt-24 flex-col fadeInUp">
                <h1 class="text-4xl font-bold ">3. Create your store </h1>
                <div class="flex flex-col sm:justify-center sm:pt-0">

                    <div>
                        <InputLabel for="name" value="Store" />

                        <TextInput type="text" class="block w-full" v-model="form.store" required autofocus
                            autocomplete="name" placeholder="Enter store name" />

                        <InputError :message="form.errors.store" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />

                        <TextInput type="email" class="block w-full" v-model="form.email" required
                            autocomplete="username" placeholder="Enter email" />

                        <InputError :message="form.errors.email" />
                    </div>
                    <div>
                        <InputLabel value="Select Country" />
                        <select class="select select-bordered w-full " v-model="form.country" @change="countryChange">
                            <option disabled selected value="">Select your country</option>
                            <option v-for="country in countries.data" :value="country.name" :key="country.code">
                                {{ country.name }}
                            </option>
                        </select>
                    </div>
                    <div class="mt-4">
                        <InputLabel value="Select Timezone" />
                        <select class="select select-bordered w-full " v-model="form.timezone">
                            <option disabled selected value="">Choose timezone</option>
                            <option v-for="country in countries.data" :value="country.timezones[0].name"
                                :key="country.code" :selected="country.code === 'PH'">
                                {{ country.timezones[0].name }}
                            </option>
                        </select>
                    </div>
                    <div class="mt-4">
                        <InputLabel value="Select Currency" />
                        <select class="select select-bordered w-full " v-model="form.currency">
                            <option disabled selected value="">Choose currency</option>
                            <option v-for="country in countries.data" :value="country.currency" :key="country.code"
                                :selected="country.code === 'PH'">
                                {{ country.currency }}
                            </option>
                        </select>
                    </div>
                </div>

            </div>

            <div class="flex gap-3 mt-24 flex-col fadeInUp">
                <h1 class="text-4xl font-bold ">4. Proceed to payment </h1>

                <div class="flex gap-3 justify-between text-2xl bg-gray-100 p-3 shadow-sm font-bold mt-4">
                    <p>Total amount</p>
                    <span class="font-bold">₱ {{ total }}</span>
                </div>

                <button class="btn btn-primary text-white px-6 py-2 rounded mt-5 btn-lg"
                    @click.prevent="submit" :disabled="form.processing">
                    
                    <span v-if="!form.processing">Pay Now</span>
                    <div class="flex gap-3 items-center" v-if="form.processing">
                        <span class="loading loading-spinner"></span>
                        <span> Processing...</span>
                    </div>
                  
                </button>

                <p class="mt-4">
                    By checking out you agree with our
                    <a href="#" class="text-primary">Terms of Service</a>
                    and confirm that
                    you have read our
                    <a href="#" class="text-primary">Privacy Policy</a>.
                    You can cancel recurring payments at any time.
                </p>
            </div>
        </form>
    </div>

</template>
