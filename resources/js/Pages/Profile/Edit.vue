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
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />
    <div class="w-full flex gap-3 justify-center">
        <section class="col-span-12 overflow-hidden bg-base-100 shadow-sm">
            <div class="card-body grow-0">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>
        </section>
        <section class="col-span-12 overflow-hidden bg-base-100 shadow-sm">
            <div class="card-body grow-0">
                <UpdatePasswordForm class="max-w-xl" />
            </div>
            <div class="card-body grow-0">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </section>
    </div>
</template>
