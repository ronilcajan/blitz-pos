<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue';
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

let createModal = ref(false);

const props = defineProps({  
    title: String,
    stores: Object,
    categories: Object,
    expense: Object,
});

const form = useForm({
	id: props.expense.id,
	expenses_date: props.expense.expenses_date,
	description: props.expense.description,
	amount: props.expense.amount,
	notes : props.expense.notes,
	attachments : '',
	expenses_category_id : props.expense.expenses_category_id,
    store_id : props.expense.store_id,
});
console.log(form.expenses_date);

const category = useForm({
	name: '',
});

const closeModal = () => {
    createModal.value = false;
    category.reset();
};


const submitCreateForm = () => {
    form.post('/expenses/update',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			useToast().success(`Expenses has been updated successfully!`, {
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
			closeModal();
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
                        <div class="flex items-end gap-2">
                            <div class="w-full">
                                <InputLabel for="phone" value="Category" />
                                <select v-model="form.expenses_category_id" class="select select-bordered w-full">
                                    <option disabled selected value="">Select a category</option>
                                    <option v-for="category in categories" :value="category.id" :key="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <button @click="createModal = true" type="button" class="btn btn-primary btn-square">
                                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7.757v8.486M7.757 12h8.486M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </button>
                            </div>
                          
                        </div>
                     
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
                    <div class="relative rounded-full" v-if="props.expense.attachments">
                        <a :href="props.expense.attachments" target="_blank" download="">
                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M9 2.221V7H4.221a2 2 0 0 1 .365-.5L8.5 2.586A2 2 0 0 1 9 2.22ZM11 2v5a2 2 0 0 1-2 2H4v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2h-7Z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    </div>
                    <InputLabel value="Attachments" />
                    <input @input="form.attachments = $event.target.files[0]" type="file" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                    <progress v-if="form.progress" :value="form.progress.percentage" class="progress" max="100">
                        {{ form.progress.percentage }}%
                    </progress>
                    <InputError class="mt-2" :message="form.errors.attachments" />
                </div>

                <div class="mt-6 flex justify-end">
                    <NavLink href="/expenses" class="btn">Cancel</NavLink>
                    <SuccessButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="loading loading-spinner"></span>
                        Save changes
                    </SuccessButton>
                </div>

            </form>
        </div>
    </section>
    <!-- create modal -->
    <Modal :show="createModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Create new category
            </h1>

            <form method="dialog" class="w-full" @submit.prevent="submitCategoryForm">
                <div class="mb-3">
                    <InputLabel for="name" value="Category name" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="category.name"
                        required
                        placeholder="category name"
                    />
                    <InputError class="mt-2" :message="category.errors.name" />
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': category.processing }"
                        :disabled="category.processing"
                    >
                        <span v-if="category.processing" class="loading loading-spinner"></span>
                        Create
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
