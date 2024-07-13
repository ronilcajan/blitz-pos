<script setup>
import { useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
            
};
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" class="block w-full" v-model="form.email" required autofocus
                    autocomplete="username" placeholder="email" />
                <InputError class="mt-2" :message="$page.props.errors.email" />

            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput id="password" type="password" class="block w-full" v-model="form.password" required
                    autocomplete="current-password" placeholder="password" />

                <InputError class="mt-2" :message="form.errors.password" />

            </div>

            <div class="flex items-center justify-between mt-4">
                <div>
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="text-sm text-gray-600 ms-2 dark:text-gray-400">Remember me</span>
                    </label>
                </div>
                <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm link link-hover">
                Forgot your password?
                </Link>


            </div>

            <div class="flex items-end mt-12">
                <PrimaryButton class="grow" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    <span v-if="form.processing" class="loading loading-spinner"></span>
                    Log in
                </PrimaryButton>
            </div>
            <div class="text-sm divider">or connect to</div>
            <div class="flex justify-center gap-3">
                <a :href="route('google-auth')" class="btn grow">
                    <span>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_191_13499)">
                                <path
                                    d="M19.999 10.2217C20.0111 9.53428 19.9387 8.84788 19.7834 8.17737H10.2031V11.8884H15.8266C15.7201 12.5391 15.4804 13.162 15.1219 13.7195C14.7634 14.2771 14.2935 14.7578 13.7405 15.1328L13.7209 15.2571L16.7502 17.5568L16.96 17.5774C18.8873 15.8329 19.9986 13.2661 19.9986 10.2217"
                                    fill="#4285F4" />
                                <path
                                    d="M10.2055 19.9999C12.9605 19.9999 15.2734 19.111 16.9629 17.5777L13.7429 15.1331C12.8813 15.7221 11.7248 16.1333 10.2055 16.1333C8.91513 16.1259 7.65991 15.7205 6.61791 14.9745C5.57592 14.2286 4.80007 13.1801 4.40044 11.9777L4.28085 11.9877L1.13101 14.3765L1.08984 14.4887C1.93817 16.1456 3.24007 17.5386 4.84997 18.5118C6.45987 19.4851 8.31429 20.0004 10.2059 19.9999"
                                    fill="#34A853" />
                                <path
                                    d="M4.39899 11.9777C4.1758 11.3411 4.06063 10.673 4.05807 9.99996C4.06218 9.32799 4.1731 8.66075 4.38684 8.02225L4.38115 7.88968L1.19269 5.4624L1.0884 5.51101C0.372763 6.90343 0 8.4408 0 9.99987C0 11.5589 0.372763 13.0963 1.0884 14.4887L4.39899 11.9777Z"
                                    fill="#FBBC05" />
                                <path
                                    d="M10.2059 3.86663C11.668 3.84438 13.0822 4.37803 14.1515 5.35558L17.0313 2.59996C15.1843 0.901848 12.7383 -0.0298855 10.2059 -3.6784e-05C8.31431 -0.000477834 6.4599 0.514732 4.85001 1.48798C3.24011 2.46124 1.9382 3.85416 1.08984 5.51101L4.38946 8.02225C4.79303 6.82005 5.57145 5.77231 6.61498 5.02675C7.65851 4.28118 8.9145 3.87541 10.2059 3.86663Z"
                                    fill="#EB4335" />
                            </g>
                            <defs>
                                <clipPath id="clip0_191_13499">
                                    <rect width="20" height="20" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                    </span>

                </a>
                <a :href="route('google-auth')" class="btn grow">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="35" width="35"
                            viewBox="-204.79995 -341.33325 1774.9329 2047.9995">
                            <path
                                d="M1365.333 682.667C1365.333 305.64 1059.693 0 682.667 0 305.64 0 0 305.64 0 682.667c0 340.738 249.641 623.16 576 674.373V880H402.667V682.667H576v-150.4c0-171.094 101.917-265.6 257.853-265.6 74.69 0 152.814 13.333 152.814 13.333v168h-86.083c-84.804 0-111.25 52.623-111.25 106.61v128.057h189.333L948.4 880H789.333v477.04c326.359-51.213 576-333.635 576-674.373"
                                fill="#1877f2" />
                            <path
                                d="M948.4 880l30.267-197.333H789.333V554.609C789.333 500.623 815.78 448 900.584 448h86.083V280s-78.124-13.333-152.814-13.333c-155.936 0-257.853 94.506-257.853 265.6v150.4H402.667V880H576v477.04a687.805 687.805 0 00106.667 8.293c36.288 0 71.91-2.84 106.666-8.293V880H948.4"
                                fill="#fff" />
                        </svg> </span>
                </a>
                <a :href="route('google-auth')" class="btn grow">
                    <span>
                        <svg fill="none" height="30" width="30" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 512 512">
                            <path d="M0 0h512v512H0z" fill="#000" />
                            <path clip-rule="evenodd"
                                d="M192.034 98H83l129.275 170.757L91.27 412h55.908l91.521-108.34 81.267 107.343H429L295.968 235.284l.236.303L410.746 99.994h-55.908l-85.062 100.694zm-48.849 29.905h33.944l191.686 253.193h-33.944z"
                                fill="#fff" fill-rule="evenodd" />
                        </svg>
                    </span>
                </a>
            </div>
            <div class="mt-5 text-xs text-center">

                Don't have an account yet?
                <a href="/#pricing" class="link link-hover text-primary">Click here
                </a>
            </div>
        </form>
    </GuestLayout>
</template>
