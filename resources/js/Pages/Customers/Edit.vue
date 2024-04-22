<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue';
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    customer: Object,
    stores: Object,
});

const form = useForm({
    id: props.customer.id,
	name: props.customer.name,
	email: props.customer.email,
	phone : props.customer.phone,
	address : props.customer.address,
	logo : '',
    store_id : props.customer.store_id,
});

const submitUpdateForm  = () => {
	form.post('/customers/update',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			useToast().success(`Customer has been updated successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
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
                                <span class="uppercase">Customer Information</span>
                            </h2>
                            <div class="flex justify-end gap-3 flex-col md:flex-row">
                                <NavLink href="/customers" class="btn btn-sm">
                                    <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4"/>
                                    </svg>
                                    Cancel</NavLink>
                                <SuccessButton type="submit"
                                    class="btn btn-sm"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing" class="loading loading-spinner"></span>
                                    Save Changes
                                </SuccessButton>
                            </div>
                        </div>
                        <div class="mb-3">
                            <InputLabel for="name" value="Customer name" />
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

                        <div class="mb-3" v-show="$page.props.auth.user.isSuperAdmin">
                            <InputLabel for="phone" value="Store" />
                            <select v-model="form.store_id" class="select select-bordered w-full">
                                <option disabled selected value="">Select a store</option>
                                <option v-for="store in stores" :value="store.id" :key="store.id">
                                    {{ store.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.store_id" />
                        </div>
                        <div class="mb-3">
                            <InputLabel value="Address" />
                            <textarea v-model="form.address" class="textarea w-full textarea-bordered" placeholder="Address"></textarea>
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
                            <label v-if="!props.customer.logo" for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="200"  height="200"  viewBox="0 0 24 24"  fill="none"  stroke="#9b9797"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-package"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /><path d="M16 5.25l-8 4.5" /></svg>
                                </div>
                            </label>
                            <img :src="props.customer.logo" v-if="props.customer.logo" alt="product image">

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
