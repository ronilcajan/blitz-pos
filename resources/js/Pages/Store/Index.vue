<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router } from '@inertiajs/vue3'
import debounce from "lodash/debounce";
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    stores: Object,
	filters: Object
});

let search = ref(props.filters.search);
const createModal = ref(false);
const editModal = ref(false);
const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);
const url = '/stores';

let storeIds = ref([]);
let selectAllCheckbox = ref(false);

const createForm = useForm({
	name: '',
	email: '',
	contact: '',
	address: '',
	avatar: '',
});

const editForm = useForm({
    id: '',
	name: '',
	email: '',
	contact: '',
	address: '',
	initialLogo: '',
    avatar: '',
});

const deleteForm = useForm({
	id: '',
});

const editModalForm = (store_id, store) => {
	editForm.clearErrors()
	editForm.id = store_id;
	editForm.name = store.name;
	editForm.email = store.email;
	editForm.contact = store.contact;
	editForm.address = store.address;
    editForm.initialLogo = store.initialLogo;
	editModal.value = true;
};


const deleteStoreForm = (store_id) => {
	deleteModal.value = true;
	deleteForm.id = store_id
}

const closeModal = () => {
    createForm.clearErrors()
	deleteForm.clearErrors()
	editForm.clearErrors()
    createModal.value = false;
    deleteModal.value = false;
    editModal.value = false;
    deleteAllSelectedModal.value = false;
    createForm.reset();
    deleteForm.reset();
    editForm.reset();
};

const submitCreateForm = () => {
	createForm.post('/stores',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
            closeModal();
			useToast().success(`${createForm.name} store has been created successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitUpdateForm = () => {
	editForm.post(route('store.update'),
	{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
            closeModal();
			useToast().success(`Store has been updated successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitDeleteForm = () => {
	deleteForm.delete(`/stores/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().success('Store has been deleted successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    router.post(route('store.bulkDelete'),
    {
        store_id: storeIds.value
    },
    {
        forceFormData: true,
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            storeIds.value = [];
            closeModal();
            useToast().success('Multiple stores has been deleted successfully!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            selectAllCheckbox.value = false;
        },
    })
}

const selectAll = () => {
	if (selectAllCheckbox.value) {
        // If "Select All" checkbox is checked, select all users
        storeIds.value = props.stores.data.map(store => store.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        storeIds.value = [];
      }
}

</script>

<template>
    <Head :title="title" />
    <div class="flex justify-end mb-4">
        <PrimaryButton class="btn btn-sm"  @click="createModal = true">New store</PrimaryButton>
    </div>
    <section class="col-span-12 overflow-hidden bg-base-100 shadow rounded-xl">

        <div class="card-body grow-0">
            <div class="flex justify-between gap-2 flex-col-reverse sm:flex-row">
                <div>
                    <DeleteButton v-show="storeIds.length > 0" @click="deleteAllSelectedModal = true">
                        Delete
                    </DeleteButton>
                </div>
                <div class="flex gap-2 flex-col sm:flex-row">
                    <SearchInput v-model="search" @clear-search="search = ''" :url="url"/>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead class="uppercase">
                    <tr>
                        <th>
                            <input @change="selectAll" v-model="selectAllCheckbox" type="checkbox" class="checkbox checkbox-sm">
                        </th>
                        <th >
                            <div class="font-bold">Name</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Phone</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Address</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Created on</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="store in stores.data" :key="store.id">
                        <td class="w-0">
                            <input :value="store.id" v-model="storeIds" type="checkbox" class="checkbox checkbox-sm">
                        </td>
                        <td class="w-5">
                            <div class="flex items-center gap-2">
                                <div class="avatar" v-show="store.avatar">
                                    <div class="mask mask-squircle h-10 w-10">
                                        <img :src="store.avatar" alt="Store logo">
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm font-bold">{{ store.name }}</div>
                                    <div class="text-xs opacity-50">{{ store.email }}</div>
                                    <div class="sm:hidden">
                                        <div class="text-xs opacity-50">{{ store.contact }}</div>
                                        <div class="text-xs opacity-50">{{ store.address }}</div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <!-- These columns will be hidden on small screens -->
                        <td class="hidden sm:table-cell">{{ store.contact }}</td>
                        <td class="hidden sm:table-cell">{{ store.address }}</td>
                        <td class="hidden sm:table-cell">{{ store.created_at }}</td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <button class=" hover:text-green-500"
                                    @click="editModalForm(store.id,
                                        { name: store.name, email: store.email,contact: store.contact, address: store.address,initialLogo: store.file})">
                                    <svg class="w-6 h-6 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/>
                                    </svg>
                                </button>
                                <DeleteIcon @modal-show="deleteStoreForm(store.id)"/>
                            </div>
                        </td>

                    </tr>
                    <tr v-if="stores.data.length  <= 0">
                        <td colspan="5" class="text-center">
                            No data found
                        </td>

                    </tr>
                </tbody>
            </table>

        </div>
    </section>
    <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
        <PaginationResultRange :data="stores" />
        <PaginationControlList :url="url" />
        <Pagination :links="stores.links" />
    </div>

    <!-- create modal -->
    <Modal :show="createModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Add new store
            </h1>

            <form method="dialog" class="w-full" @submit.prevent="submitCreateForm">
                <div class="mb-3">
                    <InputLabel for="name" value="Store name" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="createForm.name"
                        required
                        placeholder="store name"
                    />
                    <InputError class="mt-2" :message="createForm.errors.name" />
                </div>
                <div class="mb-3">
                    <InputLabel value="Email Address" />
                    <TextInput
                        type="email"
                        class="block w-full"
                        v-model="createForm.email"
                        required
                        placeholder="email address"
                    />
                    <InputError class="mt-2" :message="createForm.errors.email" />
                </div>
                <div class="mb-3">
                    <InputLabel value="Contact No" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="createForm.contact"
                        placeholder="phone number"
                    />
                    <InputError class="mt-2" :message="createForm.errors.contact" />
                </div>
                <div class="mb-3">
                    <InputLabel value="Address" />
                    <textarea v-model="createForm.address" class="textarea w-full textarea-bordered" placeholder="Address"></textarea>
                    <InputError class="mt-2" :message="createForm.errors.address" />
                </div>
                <div class="mb-3">
                    <InputLabel value="Store logo" />
                    <input accept="image/*" @input="createForm.avatar = $event.target.files[0]" type="file" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                    <progress v-if="createForm.progress" :value="createForm.progress.percentage" class="progress" max="100">
                        {{ createForm.progress.percentage }}%
                    </progress>
                    <InputError class="mt-2" :message="createForm.errors.avatar" />
                </div>


                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': createForm.processing }"
                        :disabled="createForm.processing"
                    >
                        <span v-if="deleteForm.processing" class="loading loading-spinner"></span>
                        Create
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
    <!-- edit modal -->
    <Modal :show="editModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Edit store
            </h1>

            <form method="dialog" class="w-full" @submit.prevent="submitUpdateForm">
                <div class="mb-3">
                    <InputLabel for="name" value="Store name" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="editForm.name"

                        placeholder="store name"
                    />
                    <InputError class="mt-2" :message="editForm.errors.name" />
                </div>
                <div class="mb-3">
                    <InputLabel value="Email Address" />
                    <TextInput
                        type="email"
                        class="block w-full"
                        v-model="editForm.email"
                        required
                        placeholder="email address"
                    />
                    <InputError class="mt-2" :message="editForm.errors.email" />
                </div>
                <div class="mb-3">
                    <InputLabel value="Contact No" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="editForm.contact"
                        placeholder="phone number"
                    />
                    <InputError class="mt-2" :message="editForm.errors.contact" />
                </div>
                <div class="mb-3">
                    <InputLabel value="Address" />
                    <textarea v-model="editForm.address" class="textarea w-full textarea-bordered" placeholder="Address"></textarea>
                    <InputError class="mt-2" :message="editForm.errors.address" />
                </div>
                <div class="mb-3">
                    <div class="relative rounded-full" v-if="editForm.initialLogo">
                        <img width="60" class="rounded-md" :src="editForm.initialLogo" alt="Product">
                    </div>
                    <InputLabel value="Store logo" />
                    <input accept="image/*" @input="editForm.avatar = $event.target.avatar[0]" type="file" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                    <progress v-if="editForm.progress" :value="editForm.progress.percentage" class="progress" max="100">
                        {{ editForm.progress.percentage }}%
                    </progress>
                    <InputError class="mt-2" :message="editForm.errors.avatar" />
                </div>


                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <SuccessButton
                        class="ms-3"
                        :class="{ 'opacity-25': editForm.processing }"
                        :disabled="editForm.processing"
                    >
                        <span v-if="editForm.processing" class="loading loading-spinner"></span>
                        Update
                    </SuccessButton>
                </div>
            </form>
        </div>
    </Modal>
    <!-- delete modal -->
    <Modal :show="deleteModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete store
            </h1>
            <p>Are you sure you want to delete this data? This action cannot be undone.</p>
            <form method="dialog" class="w-full" @submit.prevent="submitDeleteForm">

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': deleteForm.processing }"
                        :disabled="deleteForm.processing"
                    >
                        <span v-if="deleteForm.processing" class="loading loading-spinner"></span>
                        Delete
                    </DangerButton>
                </div>
            </form>
        </div>
    </Modal>
    <!-- delete all selected modal -->
    <Modal :show="deleteAllSelectedModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete {{ storeIds.length }} store
            </h1>
            <p>Are you sure you want to delete this data? This action cannot be undone.</p>
            <form method="dialog" class="w-full" @submit.prevent="submitBulkDeleteForm">

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': submitBulkDeleteForm.processing }"
                        :disabled="submitBulkDeleteForm.processing"
                    >
                        <span v-if="submitBulkDeleteForm.processing" class="loading loading-spinner"></span>
                        Delete
                    </DangerButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
