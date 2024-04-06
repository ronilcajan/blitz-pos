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
    <div class="flex justify-center">
        <div class="w-full gap-8">
            <section class="card col-span-12 bg-base-100">
                <form class="card-body" action="">
                    <div class="alert alert-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Your payment was successful</span>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Card Number</span>
                        </label>
                        <input type="text" class="input input-bordered font-mono" pattern="\d{16}" title="16 digits card number" minlength="16" maxlength="16" required="">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">CVV</span>
                            </label>
                            <input type="text" placeholder="XXX" pattern="\d{3,4}" title="3 or 4 digits CVV number" minlength="3" maxlength="4" required="" class="input input-bordered text-center font-mono">
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Expiration date</span>
                            </label>
                            <div class="input input-bordered grid grid-cols-2 gap-2">
                                <input placeholder="MM" type="text" pattern="\d{2}" title="2 digits month number" minlength="2" maxlength="2" class="text-center font-mono" required="">
                                <input placeholder="YY" type="text" pattern="\d{2}" title="2 digits year number" minlength="2" maxlength="2" class="text-center font-mono" required="">
                            </div>
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input type="text" class="input input-bordered">
                    </div>
                    <div class="form-control mt-4 gap-4">
                        <label class="flex cursor-pointer gap-4">
                            <input type="checkbox" class="checkbox checkbox-sm" checked="">
                            <span class="label-text">Save my card information for future payments</span>
                        </label>
                        <label class="flex cursor-pointer gap-4">
                            <input required="" type="checkbox" class="checkbox checkbox-sm" checked="">
                            <span class="label-text">Accept terms of use and privac policy</span>
                        </label>
                    </div>
                    <div class="form-control">
                        <div class="flex items-end py-4">
                            <button class="btn btn-primary grow">Confirm Payment</button>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </div>
        <!-- <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div> -->
</template>
