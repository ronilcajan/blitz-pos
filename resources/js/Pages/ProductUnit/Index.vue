<script setup>
import { computed, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router, usePage } from '@inertiajs/vue3'
import debounce from "lodash/debounce";
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({  
    title: String,
    units: Object,
    stores: Object,
	filters: Object
});

let per_page = ref(10);
let search = ref(props.filters.search);
let store = ref('');

const createModal = ref(false);
const editModal = ref(false);
const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);
const page = usePage();

let categoryIds = ref([]);
let selectAllCheckbox = ref(false);

const createForm = useForm({name: ''});

const editForm = useForm({id: '', name: ''});

const deleteForm = useForm({id: ''});

const editModalForm = (category_id, category) => {
	editForm.clearErrors()
	editForm.id = category_id;
    editForm.name = category.category;
	editModal.value = true;
};


const deleteCatForm = (category_id) => {
	deleteModal.value = true;
	deleteForm.id = category_id
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
	createForm.post('/product_units',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
            closeModal();
			useToast().success(`Produt unit has been created successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitUpdateForm = () => {
	editForm.post('product_units/update',
	{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
            closeModal();
			useToast().success(`Produt unit has been updated successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitDeleteForm = () => {
	deleteForm.delete(`/product_units/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().success('Produt unit has been deleted successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    router.post(route('product_unit.bulkDelete'), 
    {
        categories_id: categoryIds.value
    },
    {
        forceFormData: true,
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            categoryIds.value = [];
            closeModal();
            useToast().success('Multiple units has been deleted successfully!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            selectAllCheckbox.value = []
        },
    })
}

const selectAll = () => {
	if (selectAllCheckbox.value) {
        // If "Select All" checkbox is checked, select all users
        categoryIds.value = props.units.data.map(unit => unit.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        categoryIds.value = [];
      }
}

const isSuperAdmin = computed(() => 
    page.props.auth.user.isSuperAdmin ? true : false
)

watch(per_page, value => {
	router.get('/product_units', 
	{ per_page: value },
	{ preserveState: true, replace:true })
})

watch(search, debounce(function (value) {
	router.get('/product_units',
	{ search: value },
	{ preserveState: true, replace:true })
}, 500)) ;

watch(store, value => {
	router.get('/product_units', 
	{ store: value },
	{ preserveState: true, replace:true })
})

</script>

<template>
    <Head :title="title" />

    <section class="col-span-12 overflow-hidden bg-base-100 shadow-sm">
        <div class="card-body grow-0">
            <div class="flex justify-between gap-2 flex-col-reverse sm:flex-row">
                <div>
                    <select v-model="per_page" class="select select-sm max-w-xs">
                        <option class="text-body">10</option>
                        <option class="text-body">25</option>
                        <option class="text-body">50</option>
                        <option class="text-body">100</option>
                        <option class="text-body">All</option>
                    </select>
                </div>
                <div class="flex gap-2 flex-col sm:flex-row">
                    <div class="w-full" v-show="isSuperAdmin">
                        <select v-model="store" class="select select-bordered select-sm w-full max-w-xs">
                            <option selected value="">Filter by store</option>
                            <option v-for="store in stores" :value="store.name" :key="store.id">
                                {{ store.name }}
                            </option>
                        </select>
                    </div>
                    <div class="w-full">
                        <label for="simple-search" class="sr-only">Search</label>
                        <div class="relative sm:w-60 w-full">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                            <input placeholder="Type here" v-model="search" class="input pl-8 input-bordered input-sm w-full max-w-xs"/>
                            <button type="button" v-if="search" class="absolute inset-y-0 end-0 flex items-center pe-3" @click="search = ''">
                                <svg class="w-4 h-4 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                </svg>

                            </button>
                        </div>
                    </div>
                    <PrimaryButton class="btn btn-sm"  @click="createModal = true">Add new</PrimaryButton>
                    <DangerButton v-show="categoryIds.length > 0" @click="deleteAllSelectedModal = true" class="btn btn-sm">Delete</DangerButton>
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
                        <th>
                            <div class="font-bold">Name</div>
                        </th>
                        <th class="hidden sm:table-cell" v-if="isSuperAdmin">
                            <div class="font-bold">Store</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Created on</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="unit in units.data" :key="unit.id">
                        <td class="w-0">
                            <input :value="unit.id" v-model="categoryIds" type="checkbox" class="checkbox checkbox-sm">
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div>
                                    <div class="text-sm font-bold">
                                        {{ unit.name }}</div>
                                    <div class="sm:hidden">
                                        <div v-if="isSuperAdmin" class="text-xs opacity-50">
                                            {{ unit.store }}</div>
                                        <div class="text-xs opacity-50">
                                            {{ unit.created_at }}</div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="hidden sm:table-cell" v-if="isSuperAdmin">{{ unit.store }}</td>
                        <td class="hidden sm:table-cell">{{ unit.created_at }}</td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <button class=" hover:text-green-500" 
                                    @click="editModalForm(unit.id, 
                                        { unit: unit.name })">
                                    <svg class="w-6 h-6 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/>
                                    </svg>
                                </button>
                                <button @click="deleteCatForm(unit.id)" class="text-orange-900 hover:text-orange-600" type="button">
                                    <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M13.7535 2.47502H11.5879V1.9969C11.5879 1.15315 10.9129 0.478149 10.0691 0.478149H7.90352C7.05977 0.478149 6.38477 1.15315 6.38477 1.9969V2.47502H4.21914C3.40352 2.47502 2.72852 3.15002 2.72852 3.96565V4.8094C2.72852 5.42815 3.09414 5.9344 3.62852 6.1594L4.07852 15.4688C4.13477 16.6219 5.09102 17.5219 6.24414 17.5219H11.7004C12.8535 17.5219 13.8098 16.6219 13.866 15.4688L14.3441 6.13127C14.8785 5.90627 15.2441 5.3719 15.2441 4.78127V3.93752C15.2441 3.15002 14.5691 2.47502 13.7535 2.47502ZM7.67852 1.9969C7.67852 1.85627 7.79102 1.74377 7.93164 1.74377H10.0973C10.2379 1.74377 10.3504 1.85627 10.3504 1.9969V2.47502H7.70664V1.9969H7.67852ZM4.02227 3.96565C4.02227 3.85315 4.10664 3.74065 4.24727 3.74065H13.7535C13.866 3.74065 13.9785 3.82502 13.9785 3.96565V4.8094C13.9785 4.9219 13.8941 5.0344 13.7535 5.0344H4.24727C4.13477 5.0344 4.02227 4.95002 4.02227 4.8094V3.96565ZM11.7285 16.2563H6.27227C5.79414 16.2563 5.40039 15.8906 5.37227 15.3844L4.95039 6.2719H13.0785L12.6566 15.3844C12.6004 15.8625 12.2066 16.2563 11.7285 16.2563Z" fill=""></path>
                                        <path d="M9.00039 9.11255C8.66289 9.11255 8.35352 9.3938 8.35352 9.75942V13.3313C8.35352 13.6688 8.63477 13.9782 9.00039 13.9782C9.33789 13.9782 9.64727 13.6969 9.64727 13.3313V9.75942C9.64727 9.3938 9.33789 9.11255 9.00039 9.11255Z" fill=""></path>
                                        <path d="M11.2502 9.67504C10.8846 9.64692 10.6033 9.90004 10.5752 10.2657L10.4064 12.7407C10.3783 13.0782 10.6314 13.3875 10.9971 13.4157C11.0252 13.4157 11.0252 13.4157 11.0533 13.4157C11.3908 13.4157 11.6721 13.1625 11.6721 12.825L11.8408 10.35C11.8408 9.98442 11.5877 9.70317 11.2502 9.67504Z" fill=""></path>
                                        <path d="M6.72245 9.67504C6.38495 9.70317 6.1037 10.0125 6.13182 10.35L6.3287 12.825C6.35683 13.1625 6.63808 13.4157 6.94745 13.4157C6.97558 13.4157 6.97558 13.4157 7.0037 13.4157C7.3412 13.3875 7.62245 13.0782 7.59433 12.7407L7.39745 10.2657C7.39745 9.90004 7.08808 9.64692 6.72245 9.67504Z" fill=""></path>
                                    </svg>
                                </button>
                            </div>    
                        </td>

                    </tr>
                    <tr v-if="units.data.length  <= 0">
                        <td colspan="5" class="text-center">
                            No data found
                        </td>

                    </tr>
                </tbody>
            </table>
            
        </div>
    </section>
    <div class="col-span-12 items-center sm:flex sm:justify-between sm:mt-0 mt-2">
        <div class="text-center mb-4">
            <small>
                Showing {{ units.from }} to  {{ units.to }} of  {{ units.total }} results
            </small>
        </div>
        <Paginator :links="units.links" />
    </div>

    <!-- create modal -->
    <Modal :show="createModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Create new product unit
            </h1>

            <form method="dialog" class="w-full" @submit.prevent="submitCreateForm">
                <div class="mb-3">
                    <InputLabel for="name" value="Product unit" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="createForm.name"
                        required
                        placeholder="product unit"
                    />
                    <InputError class="mt-2" :message="createForm.errors.name" />
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': createForm.processing }"
                        :disabled="createForm.processing"
                    >
                        <span v-if="createForm.processing" class="loading loading-spinner"></span>
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
                Edit product units
            </h1>

            <form method="dialog" class="w-full" @submit.prevent="submitUpdateForm">
                <div class="mb-3">
                    <InputLabel for="name" value="Product unit" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="editForm.name"
                        required
                        placeholder="product unit"
                    />
                    <InputError class="mt-2" :message="editForm.errors.name" />
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
                Delete product units
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
                Delete {{ categoryIds.length }} product units
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
