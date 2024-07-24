<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })
const page = usePage()

const props = defineProps({
    title: String,
    stores: Object,
});

const form = useForm({
	name: '',
	email: '',
	phone : '',
	address : '',
	logo : '',
    store_id : page?.props?.auth?.user.store_id,
});
const image_preview = ref('');

const submitCreateForm = () => {
	form.post('/customers',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			form.reset()
			useToast().success(`Customer has been created successfully!`, {
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
</script>

<template>
    <Head :title="title" />

    <form class="flex-grow" @submit.prevent="submitCreateForm">

        <TitleContainer :title="title">
            <CancelButton href="/customers" >Cancel</CancelButton>
            <CreateSubmitBtn v-model="form">Create Customer</CreateSubmitBtn>
        </TitleContainer>

        <div class="flex gap-5 flex-col md:flex-row">
            <div class="w-full sm:w-2/3">
                <div class="card bg-base-100 shadow">
                    <div class="card-body grow-0">
                        <div class="flex justify-between gap-2 flex-col lg:flex-row">
                            <h2 class="card-title grow text-sm mb-5">
                                <span class="uppercase">Customer Information</span>
                            </h2>
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
                            <span class="uppercase">Customer Photo</span>
                        </h2>
                        <div class="flex relative mb-5.5 w-full cursor-pointer appearance-none rounded border border-dashed border-primary bg-gray px-4 py-4 dark:bg-meta-4 sm:py-7.5 justify-center">
                            <input type="file" @input="form.logo = $event.target.files[0]" accept="image/*" class="absolute inset-0 z-50 m-0 h-full w-full cursor-pointer p-0 opacity-0 outline-none" @change="onFileChange">

                            <ImagePreview v-model="image_preview" />
                        </div>
                        <progress v-if="form.progress" :value="form.progress.percentage" class="progress" max="100">
                                {{ form.progress.percentage }}%
                            </progress>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
