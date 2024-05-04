<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3'
import { ref,computed } from 'vue';
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    store: Object,
    countries: Object,
	filters: Object
});

const tin = ref(null);
const country = ref('');

const form = useForm({
    id: props.store.id,
    name: props.store?.name,
    founder: props.store?.founder,
    tagline: props.store?.tagline,
    email: props.store?.email,
    contact: props.store?.contact,
    website: props.store?.website,
    industry: props.store?.industry ?? '',
    country: props.store?.country ?? '',
    country_code: props.store?.country_code ?? '',
    // timezone : props.store?.timezone ?? '',
    // timezone_ : '',
    // currency : props.store?.currency ?? '',
    // currency_name : props.store?.currency ?? '',
    // currency_symbol : props.store?.currency_symbol ?? '',
    tax : props.store?.tax,
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

const countryChange = (e) => {
    const selectedCountry = props.countries.find(country => country.name === e.name);

    if (selectedCountry) {
        form.country = e.name;
        form.country_code = selectedCountry.alpha3Code;
        // form.timezone_ = selectedCountry.alpha3Code;
        // form.timezone = selectedCountry.timezone;
        // form.currency = selectedCountry.currency_code;
        // form.currency_name = selectedCountry.currency_name;
        // form.currency_symbol = selectedCountry.currency_symbol;
        tin.value.focus();
        country.value = '';
    }
}

const filterCountries = (searchTerm) => {
    return props.countries.filter(country =>
        country.name.toLowerCase().includes(searchTerm.toLowerCase())
    );
}

</script>


<template>
    <Head :title="title" />

    <form @submit.prevent="submitUpdateForm">
        <div class="flex gap-5 flex-col md:flex-row">
            <div class="w-full sm:w-2/3">
                <div class="card bg-base-100 shadow">
                    <div class="card-body grow-0">
                        <div class="flex justify-between gap-2 flex-col lg:flex-row">
                            <h2 class="card-title grow text-sm mb-5">
                                <span class="uppercase">Store Information</span>
                            </h2>
                            <div class="flex justify-end gap-3 flex-col md:flex-row">
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

                        <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
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

                        <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
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

                        <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
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
                                <select v-model="form.industry" class="select select-bordered w-full">
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

                        <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">

                            <div class="form-control">
                                <InputLabel for="name" value="Country" />
                                <div class="dropdown">
                                    <TextInput
                                    type="text"
                                    class="block w-full"
                                    v-model="form.country"
                                    placeholder="Select a country"
                                    required
                                />
                                    <ul tabindex="0" class="dropdown-content z-[1] bg-base-100 shadow-lg w-full overflow-y-auto max-h-40">
                                        <li class="p-2 pt-3">
                                            <TextInput
                                            type="text"
                                            v-model="country"
                                            class="block w-full input-sm mb-0"
                                            @input="filterCountries(country)"
                                            placeholder="search country..."
                                        />
                                        </li>
                                        <li class="p-2 px-5 border-b border-gray-50 cursor-pointer hover:bg-base-300 flex gap-2" v-for="(country,index) in filterCountries(country)" :key="index" @click="countryChange(country)">
                                        <img :src="country.flag" class="w-8" alt="">
                                        {{ country.name }}
                                        </li>
                                    </ul>
                                </div>
                                <InputError class="mt-2" :message="form.errors.country" />
                            </div>
                            <div class="form-control">
                                <InputLabel for="phone" value="TIN" />
                                <TextInput
                                    type="text"
                                    class="block w-full"
                                    v-model="form.tax"
                                    placeholder="Enter TIN"
                                    ref="tin"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                            <div class="form-control">
                                <InputError class="mt-2" :message="form.errors.currency" />
                                <TextInput
                                    type="hidden"
                                    v-model="form.timezone"
                                />
                                <TextInput
                                    type="hidden"
                                    v-model="form.currency"
                                />
                                <TextInput
                                    type="hidden"
                                    v-model="form.country_code"
                                />
                                <TextInput
                                    type="hidden"
                                    v-model="form.currency_symbol"
                                />
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
                <div class="card bg-base-100 shadow">
                    <div class="card-body grow-0 ">
                        <h2 class="card-title grow text-sm mb-5">
                            <span class="uppercase">Customer Profile</span>
                        </h2>
                        <ImageUpload v-model="form" :avatar="store.avatar" />
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
