<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue';
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AdminLayout })
const page = usePage()

const props = defineProps({
    title: String,
    stores: Object,
    roles: Object,
    employee: Object
});

const form = useForm({
    id: props.employee.id,
	name: props.employee.name,
	email: props.employee.email,
	phone: props.employee.phone,
    address: props.employee.address,
	store_id : props.employee.store_id,
	role_id : props.employee.role.id,
	profile_photo_url : '',
});

const roles = computed(() => { // remove super-admin roles when creating a user
	return page?.props?.auth?.user.isSuperAdmin ? props.roles : props?.roles.filter(role => role.id !== 1);
})


const submitUpdateForm = () => {
	form.post(route('user.update.profile'),{
		// replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			useToast().success('User has been updated successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const image_preview = ref(props.employee.profile_photo_url);

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

        <div class="flex flex-col gap-5 md:flex-row">
            <div class="w-full sm:w-2/3">
                <form @submit.prevent="submitUpdateForm">
                <div class="shadow card bg-base-100">
                    <div class="card-body grow-0">
                        <div class="flex flex-col justify-between gap-2 lg:flex-row">
                            <h2 class="mb-5 text-sm card-title grow">
                                <span class="uppercase">Personal Information</span>
                            </h2>
                            <div class="flex justify-end gap-3">
                                <NavLink href="/admin/users" class="btn btn-sm">
                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
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

                        <div class="grid gap-2 mb-3" :class="$page.props.auth.user.isSuperAdmin ? 'lg:grid-cols-2 grid-cols-1' : 'grid-cols-1'">
                            <div class="form-control">
                                <InputLabel for="name" value="Roles" />
                                <select v-model="form.role_id" required class="w-full max-w-3xl select select-bordered">
                                    <option disabled selected value="">Assign role</option>
                                    <option v-for="role in roles" :value="role.id" :key="role.id">
                                            {{ role.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.role_id" />
                            </div>
                            <div class="form-control" v-show="$page.props.auth.user.isSuperAdmin">
                                <InputLabel for="phone" value="Store" />
                                <select v-model="form.store_id" class="w-full max-w-3xl select select-bordered">
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
                            <textarea v-model="form.address" class="w-full textarea textarea-bordered" placeholder="Address"></textarea>
                            <InputError class="mt-2" :message="form.errors.address" />
                        </div>
                    </div>
                </div>
            </form>
            </div>

            <div class="w-full sm:w-1/3">
                <div class="shadow card bg-base-100">
                    <div class="card-body grow-0 ">
                        <h2 class="mb-5 text-sm card-title grow">
                            <span class="uppercase">Profile Photo</span>
                        </h2>
                        <div class="flex relative mb-5.5 w-full cursor-pointer appearance-none rounded border border-dashed border-primary bg-gray px-4 py-4 dark:bg-meta-4 sm:py-7.5 justify-center">
                            <input type="file" @input="form.profile_photo_url = $event.target.files[0]" accept="image/*" class="absolute inset-0 z-50 w-full h-full p-0 m-0 outline-none opacity-0 cursor-pointer" @change="onFileChange">

                            <ImagePreview v-model="image_preview" />
                        </div>
                        <progress v-if="form.progress" :value="form.progress.percentage" class="progress" max="100">
                                {{ form.progress.percentage }}%
                            </progress>
                    </div>
                </div>
            </div>
        </div>

</template>
