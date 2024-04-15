<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toast-notification';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone,
    address: user.address,
});

const avatarForm = useForm({
    profile_photo_url: '',
});

const fileChanged = () => {
    avatarForm.post(route('profile.update_avatar'),{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			useToast().success('User profile has been updated successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitProfileChanges = () => {
    form.patch(route('profile.update'),{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			useToast().success('User profile has been updated successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

</script>

<template>
        <div class="gap-2 mb-8">
            <h2 class="card-title grow text-lg font-medium">
                Profile Information
            </h2>
            <p class="mt-1 text-sm ">
            Update your account's profile information and email address.
            </p>
        </div>


        <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
            <div class="form-control">
            
                <div class="avatar placeholder" v-show="!$page.props.auth.user.avatar">
                    <div class="w-36 bg-neutral text-neutral-content rounded-full">
                        <span class="text-3xl">{{ user.name[0] }}</span>
                    </div>
                </div>

                <div class="avatar" v-show="$page.props.auth.user.avatar">
                    <div class="w-36 rounded-full">
                        <img :src="$page.props.auth.user.avatar" />
                    </div>
                </div>

                <div>
                    <InputLabel value="Profile picture" />
                    <input @change="fileChanged" accept="image/*" @input="avatarForm.profile_photo_url = $event.target.files[0]" type="file" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                    <progress v-if="avatarForm.progress" :value="avatarForm.progress.percentage" class="progress" max="100">
                        {{ avatarForm.progress.percentage }}%
                    </progress>
                    <InputError class="mt-2" :message="avatarForm.errors.profile_photo_url" />
                </div>

            </div>
        </div>
    <section class="max-w-xl">
        <form @submit.prevent="submitProfileChanges" class="mt-6 space-y-6">    
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
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

                <div class="mb-3">
                    <InputLabel value="Address" />
                    <textarea v-model="form.address" class="textarea w-full textarea-bordered" placeholder="Address"></textarea>
                    <InputError class="mt-2" :message="form.errors.address" />
                </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">
                    <span v-if="form.processing" class="loading loading-spinner"></span>

                    Save changes</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                </Transition>
            </div>
        </form>
    </section>
       
</template>
