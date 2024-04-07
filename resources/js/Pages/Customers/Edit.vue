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

    <section class="col-span-12 overflow-hidden bg-base-100 shadow-sm">
        <div class="card-body grow-0">
            <div class="flex justify-between gap-2">
                <h2 class="card-title grow text-sm">
                    <a class="link-hover link">Customer Information</a>
                </h2>
            </div>
            <form @submit.prevent="submitUpdateForm">
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

                <div class="grid grid-cols-1 gap-2 mb-3 lg:grid-cols-2">
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
                    <InputLabel for="name" value="Contact person" />
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
                    <div class="relative rounded-full" v-if="props.customer.logo">
                        <img width="60" class="rounded-md" :src="props.customer.logo" alt="logo">
                    </div>
                    <InputLabel value="Customer logo" />
                    <input accept="image/*" @input="form.logo = $event.target.files[0]" type="file" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                    <progress v-if="form.progress" :value="form.progress.percentage" class="progress" max="100">
                        {{ form.progress.percentage }}%
                    </progress>
                    <InputError class="mt-2" :message="form.errors.logo" />
                </div>

                <div class="mb-3">
                    <InputLabel value="Address" />
                    <textarea v-model="form.address" class="textarea w-full textarea-bordered" placeholder="Address"></textarea>
                    <InputError class="mt-2" :message="form.errors.address" />
                </div>
                

                <div class="mt-6 flex justify-end">
                    <NavLink href="/customers" class="btn">Cancel</NavLink>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="loading loading-spinner"></span>
                        Create
                    </PrimaryButton>
                </div>

            </form>
        </div>
    </section>
</template>
