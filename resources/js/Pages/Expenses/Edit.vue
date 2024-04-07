<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue';
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const page = usePage()
let createModal = ref(false);

const props = defineProps({  
    title: String,
    stores: Object,
    categories: Object,
});

const form = useForm({
	expenses_date: new Date().toISOString().substring(0, 10),
	description: '',
	amount: '0',
	notes : '',
	attachments : '',
	expenses_category_id : '',
    store_id : page?.props?.auth?.user.store_id,
});

const submitCreateForm = () => {
	form.post('/expenses',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			form.reset()
			useToast().success(`Expenses has been created successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitCategoryForm = () => {
	category.post('/expenses_categories',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			category.reset();
            createModal.value = false;
			useToast().success(`Category expenses has been created successfully!`, {
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
                    <a class="link-hover link">Expenses Information</a>
                </h2>
            </div>
            <form @submit.prevent="submitCreateForm">

                <div class="grid grid-cols-1 gap-2 mb-3 lg:grid-cols-2">
                    <div class="form-control">
                        <InputLabel for="name" value="Date" />
                        <TextInput
                            type="date"
                            class="block w-full"
                            v-model="form.expenses_date"
                            required
                            placeholder="Enter date"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                    <div class="form-control">
                        <div>
                            <InputLabel for="phone" value="Category" />
                            <select v-model="form.expenses_category_id" class="select select-bordered w-full">
                                <option disabled selected value="">Select a category</option>
                                <option v-for="category in categories" :value="category.id" :key="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                    
                        </div>
                        <button class="btn btn-square btn-outline">
  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
</button>
                        <InputError class="mt-2" :message="form.errors.expenses_category_id" />

                    </div>
                </div>

                <div class="grid grid-cols-1 gap-2 mb-3" :class="$page.props.auth.user.isSuperAdmin ? 'lg:grid-cols-2 grid-cols-1' : 'grid-cols-1'">
                    <div class="form-control">
                        <InputLabel for="name" value="Amount" />
                        <input v-model="form.amount" type="number" placeholder="Enter amount" step="0.01" min="0" class="input input-bordered w-full" />
                        <InputError class="mt-2" :message="form.errors.amount" />
                    </div>
                    <div class="form-control" v-show="$page.props.auth.user.isSuperAdmin">
                        <InputLabel for="phone" value="Store" />
                        <select v-model="form.store_id" class="select select-bordered w-full">
                            <option disabled selected value="">Select a store</option>
                            <option v-for="store in stores" :value="store.id" :key="store.id">
                                {{ store.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.store_id" />
                    </div>
                </div>

                <div class="mb-3">
                    <InputLabel value="Description" />
                    <textarea v-model="form.description" class="textarea w-full textarea-bordered" placeholder="description"></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <div class="mb-3">
                    <InputLabel value="Notes" />
                    <textarea v-model="form.notes" class="textarea w-full textarea-bordered" placeholder="notes"></textarea>
                    <InputError class="mt-2" :message="form.errors.notes" />
                </div>

                <div class="mb-3">
                    <InputLabel value="Attachments" />
                    <input @input="form.attachments = $event.target.files[0]" type="file" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                    <progress v-if="form.progress" :value="form.progress.percentage" class="progress" max="100">
                        {{ form.progress.percentage }}%
                    </progress>
                    <InputError class="mt-2" :message="form.errors.attachments" />
                </div>

                <div class="mt-6 flex justify-end">
                    <NavLink href="/expenses" class="btn">Cancel</NavLink>
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
