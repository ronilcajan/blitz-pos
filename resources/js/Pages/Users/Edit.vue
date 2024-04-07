<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue';
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })
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
	form.post('/users/update',{
		replace: true,
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
</script>

<template>
    <Head :title="title" />

    <section class="col-span-12 overflow-hidden bg-base-100 shadow-sm">
        <div class="card-body grow-0">
            <div class="flex justify-between gap-2">
                <h2 class="card-title grow text-sm">
                    <a class="link-hover link">User Information</a>
                </h2>
            </div>
            <form @submit.prevent="submitUpdateForm">
                <div class="mb-3">
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

                <div class="grid gap-2 mb-3" :class="$page.props.auth.user.isSuperAdmin ? 'grid-cols-1 lg:grid-cols-2' : 'grid-cols-1'">
                    <div class="form-control">
                        <InputLabel for="name" value="Roles" />
                        <select v-model="form.role_id" required class="select select-bordered w-full max-w-3xl">
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
                    <div class="relative rounded-full" v-if="props.employee.profile_photo_url">
                        <img width="60" class="rounded-md" :src="props.employee.profile_photo_url" alt="User profile">
                    </div>
                    <InputLabel value="Profile pciture" />
                    <input accept="image/*" @input="form.profile_photo_url = $event.target.files[0]" type="file" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                    <progress v-if="form.progress" :value="form.progress.percentage" class="progress" max="100">
                        {{ form.progress.percentage }}%
                    </progress>
                    <InputError class="mt-2" :message="form.errors.profile_photo_url" />
                </div>

                <div class="mb-3">
                    <InputLabel value="Address" />
                    <textarea v-model="form.address" class="textarea w-full textarea-bordered" placeholder="Address"></textarea>
                    <InputError class="mt-2" :message="form.errors.address" />
                </div>
                

                <div class="mt-6 flex justify-end">
                    <NavLink href="/users" class="btn">Cancel</NavLink>
                    <SuccessButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="loading loading-spinner"></span>
                        Update
                    </SuccessButton>
                </div>

            </form>
        </div>
    </section>
</template>
