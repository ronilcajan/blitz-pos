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
	vendor: props.expense.vendor,
	amount: props.expense.amount,
	notes : props.expense.notes,
	status : props.expense.status,
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


const submitUpdateForm = () => {
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

    <form @submit.prevent="submitUpdateForm"  class="flex-grow">

        <TitleContainer :title="title">
            <CancelButton href="/expenses" >Cancel</CancelButton>
            <SaveSubmitBtn v-model="form">Save changes</SaveSubmitBtn>
        </TitleContainer>

        <div class="flex gap-4 md:flex-row flex-col">
            <div class="w-full md:w-2/3">
                <div class="card bg-base-100 shadow mt-5">
                    <div class="card-body">
                        <div class="flex justify-between gap-2 flex-col lg:flex-row">
                            <h2 class="card-title grow text-sm">
                                <span class="uppercase">General Information</span>
                            </h2>
                        </div>
                        <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
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
                                <div class="flex flex-row items-center gap-2">
                                    <span class="font-semibold text-sm">Category</span>
                                    <button as="button" class="btn btn-xs btn-primary btn-link" type="button" @click="createModal = true">Add new category</button>
                                </div>
                                <select v-model="form.expenses_category_id" required class="w-full select select-bordered">
                                    <option disabled selected value="">Select a product category</option>
                                    <option v-for="category in categories" :value="category.id" :key="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.product_category_id" />
                            </div>
                        </div>

                        <div>
                            <InputLabel value="Vendor" />
                            <TextInput
                                    type="text"
                                    class="block w-full"
                                    v-model="form.vendor"
                                    required
                                    placeholder="Enter vendor name"
                                />
                            <InputError class="mt-2" :message="form.errors.vendor" />
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
                            <InputLabel value="Notes" />
                            <textarea v-model="form.notes" class="textarea w-full textarea-bordered" placeholder="notes"></textarea>
                            <InputError class="mt-2" :message="form.errors.notes" />
                        </div>
                </div>
            </div>
            </div>
            <div class="w-full md:w-1/3">
                <div class="card bg-base-100 shadow mt-5">
                    <div class="card-body">
                        <h2 class="card-title grow text-sm mb-5">
                            <span class="uppercase">Upload Supporting Documents</span>
                        </h2>
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-56 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="120"  height="120"  viewBox="0 0 24 24"  fill="none"  stroke="#525252"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-import"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 13v-8a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5.5m-9.5 -2h7m-3 -3l3 3l-3 3" /></svg>
                                </div>
                            </label>
                        </div>
                        <input accept="image/*" @input="form.attachments = $event.target.files[0]" type="file" class="file-input file-input-bordered file-input-sm w-full" />
                        <progress v-if="form.progress" :value="form.progress.percentage" class="progress" max="100">
                            {{ form.progress.percentage }}%
                        </progress>
                        <InputError class="mt-2" :message="form.errors.attachments" />
                    </div>
                </div>

                <div class="card bg-base-100 shadow mt-5">
                    <div class="card-body">
                        <h2 class="card-title grow text-sm mb-5">
                            <span class="uppercase">Expenses Status</span>
                        </h2>
                        <div class="w-full justify-center mb-5">
                            <div class="join">
                                <input v-model="form.status" value="pending" :checked="form.status==='pending'" class="join-item btn btn-sm"
                            type="radio" name="options" aria-label="Pending" ref="hideDropdownRef" />
                                <input v-model="form.status" value="approved" :checked="form.status==='approved'" class="join-item btn btn-sm" type="radio" name="options" aria-label="Completed" />
                                <input v-model="form.status" value="rejected" :checked="form.status==='rejected'" class="join-item btn btn-sm" type="radio" name="options" aria-label="rejected" />
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.status" />
                    </div>
                </div>
            </div>
        </div>
    </form>

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
