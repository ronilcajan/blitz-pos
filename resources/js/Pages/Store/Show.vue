<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3'
import { ref,computed } from 'vue';

defineOptions({ layout: AuthenticatedLayout })
const props = defineProps({
    title: String,
    store: Object,
    countries: Object,
	filters: Object
});

const currency = ref(null);
const searchTerm = ref('');

const form = useForm({
    name: props.store?.name,
    founder: props.store?.founder,
    tagline: props.store?.tagline,
    email: props.store?.email,
    contact: props.store?.contact,
    website: props.store?.website,
    industry: props.store?.industry ?? '',
    country: props.store?.country ?? '',
    timezone : props.store?.timezone ?? '',
    currency : props.store?.currency ?? '',
    currency_name : props.store?.currency ?? '',
    currency_symbol : props.store?.currency_symbol ?? '',
    tax : props.store?.tax,
    description : props.store?.description,
    logo : '',
});

const submitUpdateForm = () => {
    form.post(`/stores/${props.store.id}`,{
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            useToast().success(`Store has been updated successfully!`, {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
        },
    })
}

const sortedCountries = computed(() => {
    return props.countries.sort((a, b) => a.name.localeCompare(b.name));
});

const countryChange = (e) => {
    const selectedCountry = sortedCountries.value.find(country => country.name === e.name);

    if (selectedCountry) {
        form.country = e.name;
        form.timezone = selectedCountry.alpha3Code;
        form.currency = selectedCountry.currency_code;
        form.currency_name = selectedCountry.currency_name;
        form.currency_symbol = selectedCountry.currency_symbol;
        currency.value.focus()
    }
}

const filterCountries = (searchTerm) => {
    return sortedCountries.value.filter(country =>
        country.name.toLowerCase().includes(searchTerm.toLowerCase())
    );
}

</script>


<template>
    <Head :title="title" />

    <form @submit.prevent="submitCreateForm">
        <div class="flex gap-5 flex-col md:flex-row">
            <div class="w-full sm:w-2/3">
                <div class="card bg-base-100 shadow">
                    <div class="card-body grow-0">
                        <div class="flex justify-between gap-2 flex-col lg:flex-row">
                            <h2 class="card-title grow text-sm mb-5">
                                <span class="uppercase">Store Information</span>
                            </h2>
                            <div class="flex justify-end gap-3 flex-col md:flex-row">

                                <SuccessButton type="submit"
                                    class="btn btn-sm"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing" class="loading loading-spinner"></span>
                                    Save changes
                                </SuccessButton>
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
                                    required
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
                                    v-model="form.phone"

                                    placeholder="Enter phone number"
                                />
                                <InputError class="mt-2" :message="form.errors.phone" />
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
                                    type="url"
                                    class="block w-full"
                                    v-model="form.country"
                                    @input="filterCountries(form.country)"
                                    placeholder="Select a country"
                                />
                                    <ul tabindex="0" class="dropdown-content z-[1] bg-base-100 shadow-lg w-full overflow-y-auto h-64">
                                        <li class="p-2 px-5 border-b border-gray-50 cursor-pointer hover:bg-base-300 flex gap-2" v-for="(country,index) in sortedCountries" :key="index" @click="countryChange(country)">
                                        <img :src="country.flag" alt="">
                                        {{ country.name }}
                                        </li>
                                    </ul>
                                </div>
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>
                            <div class="form-control">
                                <InputLabel for="phone" value="Timezone" />
                                <select v-model="form.timezone" class="select select-bordered w-full">
                                    <option value="">Select a timezone...</option>
                                    <option v-for="(country,index) in sortedCountries" :key="index" :value="country.alpha3Code">{{ country.alpha3Code+' - '+country.timezone }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.timezone" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                            <div class="form-control">
                                <InputLabel for="name" value="Currency" />
                                <select v-model="form.currency" class="select select-bordered w-full" ref="currency">
                                    <option value="">Select a currency...</option>
                                    <option v-for="(country,index) in sortedCountries" :key="index" :value="country.currency_code">{{ country.currency_code + " - "+ country.currency_symbol  }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>
                            <div class="form-control">
                                <InputLabel for="phone" value="Currency Symbol" />
                                <select v-model="form.currency_symbol" class="select select-bordered w-full">
                                    <option value="">Select a currency...</option>
                                    <option v-for="(country,index) in countries" :key="index" :value="country.currency_code">{{ country.currency_symbol }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.currency" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <InputLabel value="Timezone" />
                            <textarea v-model="form.address" class="textarea w-full textarea-bordered" placeholder="Address" ref=""></textarea>
                            <InputError class="mt-2" :message="form.errors.address" />
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

                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="200"  height="200"  viewBox="0 0 24 24"  fill="none"  stroke="#9b9797"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-package"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /><path d="M16 5.25l-8 4.5" /></svg>
                                </div>
                            </label>
                        </div>
                        <div class="mt-3">
                            <input accept="image/*" @input="form.logo = $event.target.files[0]" type="file" class="file-input file-input-bordered file-input-sm w-full " />
                            <progress v-if="form.progress" :value="form.progress.percentage" class="progress" max="100">
                                {{ form.progress.percentage }}%
                            </progress>
                            <InputError class="mt-2" :message="form.errors.logo" />
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
