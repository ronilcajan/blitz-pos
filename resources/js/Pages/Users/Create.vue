<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue';
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })
const page = usePage()

const props = defineProps({
    title: String,
    stores: Object,
    roles: Object,
});

const form = useForm({
	name: '',
	email: '',
	phone: '',
	store_id : page?.props?.auth?.user.store_id,
	role_id : '',
	profile_photo_url : '',
});

const roles = computed(() => { // remove super-admin roles when creating a user if not super-admin
	return page?.props?.auth?.user.isSuperAdmin ? props.roles : props?.roles.filter(role => role.id !== 1);
})
const image_preview = ref('');

const submitCreateForm = () => {
	form.post('/users',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			form.reset()
			useToast().success('User has been created successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
            image_preview.value = '';
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
    <form @submit.prevent="submitCreateForm">
        <div class="flex gap-5 flex-col md:flex-row">
            <div class="w-full sm:w-2/3">
                <div class="card bg-base-100 shadow">
                    <div class="card-body grow-0">
                        <div class="flex justify-between gap-2 flex-col lg:flex-row">
                            <h2 class="card-title grow text-sm mb-5">
                                <span class="uppercase">Personal Information</span>
                            </h2>
                            <div class="flex justify-end gap-3 flex-col md:flex-row">
                                <NavLink href="/users" class="btn btn-sm">
                                    <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4"/>
                                    </svg>
                                    Cancel</NavLink>
                                <PrimaryButton type="submit"
                                    class="btn btn-sm"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing" class="loading loading-spinner"></span>
                                    Create User
                                </PrimaryButton>
                            </div>
                        </div>
                        <div>
                            <InputLabel for="name" value="Full name" />
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="form.name"
                                required
                                placeholder="Enter full name"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="grid grid-cols-1 gap-2 lg:grid-cols-2 mt-2">
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

                        <div class="grid gap-2 mb-3" :class="$page.props.auth.user.isSuperAdmin ? 'lg:grid-cols-2 grid-cols-1' : 'grid-cols-1'">
                            <div class="form-control mt-2">
                                <InputLabel for="name" value="Roles" />
                                <select v-model="form.role_id" required class="select select-bordered w-full max-w-3xl mt-2">
                                    <option disabled selected value="">Assign role</option>
                                    <option v-for="role in roles" :value="role.id" :key="role.id">
                                            {{ role.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.role_id" />
                            </div>
                            <div class="form-control" v-show="$page.props.auth.user.isSuperAdmin">
                                <InputLabel for="phone" value="Store" />
                                <select v-model="form.store_id" class="select select-bordered w-full max-w-3xl">
                                    <option disabled selected value="">Select a store</option>
                                    <option v-for="store in stores" :value="store.id" :key="store.id">
                                        {{ store.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.store_id" />
                            </div>
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
                            <span class="uppercase">Profile Photo</span>
                        </h2>
                        <div class="flex relative mb-5.5 w-full cursor-pointer appearance-none rounded border border-dashed border-primary bg-gray px-4 py-4 dark:bg-meta-4 sm:py-7.5 justify-center">
                            <input type="file" @input="form.profile_photo_url = $event.target.files[0]" accept="image/*" class="absolute inset-0 z-50 m-0 h-full w-full cursor-pointer p-0 opacity-0 outline-none" @change="onFileChange">

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
