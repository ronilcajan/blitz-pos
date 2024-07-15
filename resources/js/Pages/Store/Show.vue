<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3'
import { ref,onMounted, reactive } from 'vue';
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    store: Object,
    country: Object,
	filters: Object
});

const countries = reactive({});
// const country = ref('');
const image_preview = ref(props.store?.avatar ?? '');

const form = useForm({
    id: props.store.id,
    name: props.store?.name,
    founder: props.store?.founder,
    tagline: props.store?.tagline,
    email: props.store?.email,
    contact: props.store?.contact,
    website: props.store?.website,
    industry: props.store?.industry ?? '',
    country: props.store?.country ?? props.country?.name,
    country_code: props.store?.country_code ?? props.country?.code,
    timezone : props.store?.timezone ?? props.country?.time_zone,
    currency : props.store?.currency ?? props.country?.currency,
    currency_symbol : props.store?.currency_symbol ?? props.country?.currency_symbol,
    address : props.store?.address,
    description : props.store?.description,
    avatar : '',
});

const submitUpdateForm = () => {
    form.post(`/stores/update`,{
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            useToast().success(`Store details has been updated successfully!`, {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
        },
    })
}

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (!file.type.startsWith('image/')) {
        alert('Please select an image file');
        return;
    }
    image_preview.value = URL.createObjectURL(file);
}

const countryChange = () => {
    countries.data.forEach((country) => {
        if (country.name === form.country ) {   
            form.timezone = country.timezones[0].name;
            form.currency = country.currency;
            form.country_code = country.code;
        }
    })
}


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
    <form @submit.prevent="submitUpdateForm">
        <div class="flex flex-col gap-5 md:flex-row">
            <div class="w-full sm:w-2/3">
                <div class="shadow card bg-base-100">
                    <div class="card-body grow-0">
                        <div class="flex flex-col justify-between gap-2 lg:flex-row">
                            <h2 class="mb-5 text-sm card-title grow">
                                <span class="uppercase">Store Information</span>
                            </h2>
                            <div class="flex flex-col justify-end gap-3 md:flex-row">
                                <SaveButton v-model="form" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="name" value="Store name" />
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="form.name"
                                required
                                placeholder="Enter name"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <div class="form-control">
                                <InputLabel for="phone" value="Tagline" />
                                <TextInput
                                    type="text"
                                    class="block w-full"
                                    v-model="form.tagline"
                                    placeholder="Enter store tagline"
                                />
                                <InputError class="mt-2" :message="form.errors.tagline" />
                            </div>
                            <div class="form-control">
                                <InputLabel for="name" value="Founder" />
                                <TextInput
                                    type="text"
                                    class="block w-full"
                                    v-model="form.founder"
                                    placeholder="Enter store founder"
                                />
                                <InputError class="mt-2" :message="form.errors.founder" />
                            </div>

                        </div>

                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <div class="form-control">
                                <InputLabel for="name" value="Email address" />
                                <TextInput
                                    type="email"
                                    class="block w-full"
                                    v-model="form.email"
                                    required
                                    placeholder="Enter email address"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>
                            <div class="form-control">
                                <InputLabel for="phone" value="Phone number" />
                                <TextInput
                                    type="text"
                                    class="block w-full"
                                    v-model="form.contact"
                                    placeholder="Enter phone number"
                                />
                                <InputError class="mt-2" :message="form.errors.contact" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <div class="form-control">
                                <InputLabel for="name" value="Website" />
                                <TextInput
                                    type="url"
                                    class="block w-full"
                                    v-model="form.website"
                                    placeholder="Enter store website"
                                />
                                <InputError class="mt-2" :message="form.errors.website" />
                            </div>
                            <div class="form-control">
                                <InputLabel for="phone" value="Industry" />
                                <select v-model="form.industry" class="w-full select select-bordered">
                                    <option value="">Select an industry...</option>
                                    <option value="Agriculture">Agriculture</option>
                                    <option value="Automotive">Automotive</option>
                                    <option value="Banking and Finance">Banking and Finance</option>
                                    <option value="Biotechnology">Biotechnology</option>
                                    <option value="Chemicals">Chemicals</option>
                                    <option value="Construction">Construction</option>
                                    <option value="Consumer Goods">Consumer Goods</option>
                                    <option value="Defense">Defense</option>
                                    <option value="Education">Education</option>
                                    <option value="Energy">Energy (Oil & Gas, Renewable Energy)</option>
                                    <option value="Entertainment">Entertainment</option>
                                    <option value="Food and Beverage">Food and Beverage</option>
                                    <option value="Healthcare">Healthcare</option>
                                    <option value="Hospitality and Tourism">Hospitality and Tourism</option>
                                    <option value="Information Technology">Information Technology (IT)</option>
                                    <option value="Insurance">Insurance</option>
                                    <option value="Manufacturing">Manufacturing</option>
                                    <option value="Media and Communications">Media and Communications</option>
                                    <option value="Mining and Metals">Mining and Metals</option>
                                    <option value="Pharmaceuticals">Pharmaceuticals</option>
                                    <option value="Real Estate">Real Estate</option>
                                    <option value="Retail">Retail</option>
                                    <option value="Telecommunications">Telecommunications</option>
                                    <option value="Transportation and Logistics">Transportation and Logistics</option>
                                    <option value="Utilities">Utilities</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.industry" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

                            <div class="form-control">
                                <InputLabel for="name" value="Country" />
                                <select class="select select-bordered w-full " v-model="form.country" @change="countryChange">
                                    <option disabled selected value="">Select your country</option>
                                    <option v-for="country in countries.data" :value="country.name" :key="country.code">
                                        {{ country.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-control">
                                <InputLabel value="Select Timezone" />
                                <select class="select select-bordered w-full " v-model="form.timezone">
                                    <option disabled selected value="">Choose timezone</option>
                                    <option v-for="country in countries.data" :value="country.timezones[0].name"
                                        :key="country.code" :selected="country.code === 'PH'">
                                        {{ country.timezones[0].name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

                                <div class="form-control">
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
                        <div>
                            <InputLabel value="Full address" />
                            <TextArea placeholder="Address" v-model="form.address" />
                            <InputError class="mt-2" :message="form.errors.address" />
                        </div>
                        <div class="mb-3">
                            <InputLabel value="Description" />
                            <TextArea placeholder="Shop description" v-model="form.description" />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full sm:w-1/3">
                <div class="shadow card bg-base-100">

                    <div class="shadow card bg-base-100">
                        <div class="card-body grow-0 ">
                            <h2 class="mb-5 text-sm card-title grow">
                                <span class="uppercase">Store Logo</span>
                            </h2>
                            <div class="flex relative mb-5.5 w-full cursor-pointer appearance-none rounded border border-dashed border-primary bg-gray px-4 py-4 dark:bg-meta-4 sm:py-7.5 justify-center">
                                <input type="file" @input="form.avatar = $event.target.files[0]" accept="image/*" class="absolute inset-0 z-50 w-full h-full p-0 m-0 outline-none opacity-0 cursor-pointer" @change="onFileChange">

                                <ImagePreview v-model="image_preview" />
                            </div>
                            <progress v-if="form.progress" :value="form.progress.percentage" class="progress" max="100">
                                    {{ form.progress.percentage }}%
                            </progress>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
