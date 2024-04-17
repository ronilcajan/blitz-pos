<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineOptions({ layout: AuthenticatedLayout })

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    user_details: Object,
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <div class="grid gap-4 md:grid-cols-2">
        <div class="col-span-1">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body grow-0">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail" :user_details="user_details"
                        :status="status"
                        class="w-full"
                    />
                </div>
            </div>
        </div>
        <div class="col-span-1">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body grow-0">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm mt-4">
                <div class="card-body grow-0">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </div>
</template>
