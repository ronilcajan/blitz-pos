<script setup>
import { ref, watch, computed, reactive } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router, usePage } from '@inertiajs/vue3'
import debounce from "lodash/debounce";
import { useToast } from 'vue-toast-notification';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Line } from 'vue-chartjs'
import * as chartConfig from './chartConfig.js'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	expenses: Object,
    categories: Object,
    stores: Object,
	filter: Object
});

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
)

const data = {
  labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
  datasets: [
    {
      label: 'Data One',
      backgroundColor: '#2B49FF',
      data: [40, 39, 10, 40, 39, 80, 40]
    }
  ]
}

const options = {
  responsive: true,
  maintainAspectRatio: false
}

const page = usePage();
let per_page = ref(10);
let search = ref(props.filter.search);
let store = ref('');
let category = ref('');
let status = ref('');

const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);

let expenseIds = ref([]);
let selectAllCheckbox = ref(false);

const deleteForm = useForm({id: ''});

const deleteSupplierForm = (user_id) => {
	deleteModal.value = true;
	deleteForm.id = user_id
}

const closeModal = () => {
	deleteForm.clearErrors()
    deleteModal.value = false;
    deleteAllSelectedModal.value = false;
    deleteForm.reset();
};

const submitDeleteForm = () => {
	deleteForm.delete(`/expenses/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().success('Expenses has been deleted successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    router.post(route('expenses.bulkDelete'),
    {
        expenses_id: expenseIds.value
    },
    {
        forceFormData: true,
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            expenseIds.value = [];
            closeModal();
            useToast().success('Multiple expenses has been deleted successfully!', {
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
        expenseIds.value = props.expenses.data.map(expenses => expenses.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        expenseIds.value = [];
      }
}

const statusChange = (id,status) => {
	router.post(`/expenses/change-status/${id}`, {
            status: status,
        },
	    { preserveState: true, replace:true,
            onSuccess: () => {
            useToast().success('Expenses has been updated successfully!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
        },  only: ['expenses'], })
}

watch(per_page, value => {
	router.get('/expenses',
	{ per_page: value },
	{ preserveState: true, replace:true })
})

watch(search, debounce(function (value) {
	router.get('/expenses',
	{ search: value },
	{ preserveState: true, replace:true })
}, 500)) ;

watch(store, value => {
	router.get('/expenses',
	{ store: value },
	{ preserveState: true, replace:true })
})

watch(category, value => {
	router.get('/expenses',
	{ category: value },
	{ preserveState: true, replace:true })
})

watch(status, value => {
	router.get('/expenses',
	{ status: value },
	{ preserveState: true, replace:true })
})

const showRefresh = computed(() => {
    return store.value !== '' || category.value !== '' || status.value !== ''
})
</script>

<template>
    <Head :title="title" />

<div class="flex gap-5 mb-5 flex-col-reverse md:flex-row">
    <div class="w-full md:w-1/2">
        <div class="w-full stats shadow mb-5">
            <div class="stat">
                <div class="flex justify-between items-center mb-3">
                    <div class="stat-value">P 89,400</div>
                    <small>15 up vs last month</small>
                </div>
                <div class="stat-desc">Total spend this month</div>
            </div>
        </div>
        <div class="w-full stats shadow">
            <div class="stat">
                <div class="flex justify-between items-center mb-3">
                    <div class="stat-value">P 89,400</div>
                    <small>1 pending expenses</small>
                </div>

                <div class="stat-desc">Payment due this month</div>
            </div>
        </div>
    </div>
    <div class="w-ful md:w-1/2">
        <div class="card">
            <div class="card-body">
                <div class="card-title">Ex</div>
              
            </div>
            <Line :data="data" :options="options" />
        </div>
    </div>
</div>
    <section class="col-span-12 overflow-hidden bg-base-100 shadow rounded-xl">
        <div class="p-4 grow-0">
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
                    <NavLink href="/expenses" class="mt-1 mr-2 tooltip" v-show="showRefresh" data-tip="refresh">
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4"/>
                        </svg>
                    </NavLink>
                    <div class="w-full" v-show="$page.props.auth.user.isSuperAdmin">
                        <select v-model="store" class="select select-bordered select-sm w-full max-w-xs">
                            <option selected value="">Filter by store</option>
                            <option v-for="store in stores" :value="store.name" :key="store.id">
                                {{ store.name }}
                            </option>
                        </select>
                    </div>
                    <div class="w-full">
                        <select v-model="category" class="select select-bordered select-sm w-full max-w-xs">
                            <option selected value="">Filter by categories</option>
                            <option v-for="category in categories" :value="category.name" :key="category.id">
                                {{ category.name }}
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
                    <NavLink href="/expenses/create" class="btn btn-sm btn-primary">New expenses</NavLink>
                    <DangerButton v-if="$page.props.auth.user.canDelete" v-show="expenseIds.length > 0" @click="deleteAllSelectedModal = true" class="btn btn-sm">Delete</DangerButton>
                </div>
            </div>
        </div>
        <div class="w-auto md:w-1/5">
            <div role="tablist" class="tabs tabs-bordered">
                <input type="radio" v-model="status" role="tab" class="tab" aria-label="All" value="all"  :checked="status === 'all' || status === ''"/>
                <input type="radio" v-model="status" role="tab" class="tab" aria-label="Pending" value="pending" :checked="status === 'pending'" />
                <input type="radio" v-model="status" role="tab" class="tab" aria-label="Approved" value="approved" :checked="status === 'approved'" />
                <input type="radio" v-model="status" role="tab" class="tab" aria-label="Rejected" value="rejected" :checked="status === 'rejected'" />
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead class="uppercase">
                    <tr>
                        <th v-if="$page.props.auth.user.canDelete">
                            <input @change="selectAll" v-model="selectAllCheckbox" type="checkbox" class="checkbox checkbox-sm">
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Date</div>
                        </th>
                        <th>
                            <div class="font-bold">Description</div>
                        </th>

                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Documents</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Category</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Amount</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Status</div>
                        </th>
                        <th class="hidden sm:table-cell"  v-show="$page.props.auth.user.isSuperAdmin">
                            <div class="font-bold">Store</div>
                        </th>

                    </tr>
                </thead>
                <tbody>
                    <tr v-for="expense in expenses.data" :key="expense.id">
                        <td class="w-0" v-if="$page.props.auth.user.canDelete">
                            <input :value="expense.id" v-model="expenseIds" type="checkbox" class="checkbox checkbox-sm">
                        </td>
                        <td class="table-cell">{{ expense.expenses_date }}</td>

                        <td class="table-cell">
                            <div class="flex items-center gap-2">
                                <div>
                                    <div class="flex text-sm font-bold gap-2">
                                        {{ expense.description }}
                                    </div>

                                    <div class="flex flex-col gap-2 sm:hidden ">
                                        <div class="text-xs opacity-50">P {{ expense.amount }}</div>
                                        <div class="text-xs opacity-50">
                                            <a v-if="expense.attachments" :href="expense.attachments" target="_blank" download>
                                                <svg class="w-6 h-6 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Z"/>
                                                <path fill-rule="evenodd" d="M11 7V2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Zm4.707 5.707a1 1 0 0 0-1.414-1.414L11 14.586l-1.293-1.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l4-4Z" clip-rule="evenodd"/>
                                                </svg>

                                            </a>
                                        </div>
                                        <div class="text-xs opacity-50">
                                            {{ expense.notes }}
                                        </div>
                                        <div class="text-xs opacity-50">
                                            {{ expense.notes }}
                                        </div>
                                        <div class="text-xs opacity-50 ">
                                            <select @change="statusChange(expense.id, $event.target.value)" class="select select-xs" :class="expense.statusColor">
                                                <option :selected="expense.status === 'pending'">pending</option>
                                                <option :selected="expense.status === 'approved'">approved</option>
                                                <option :selected="expense.status === 'rejected'">rejected</option>
                                            </select>
                                        </div>
                                        <div class="text-xs opacity-50">{{ expense.expenses_date }}</div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <!-- These columns will be hidden on small screens -->
                        <td class="hidden sm:table-cell">
                            <a v-if="expense.attachments" :href="expense.attachments" target="_blank" download class="tooltip" data-tip="view documents">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-files"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 3v4a1 1 0 0 0 1 1h4" /><path d="M18 17h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h4l5 5v7a2 2 0 0 1 -2 2z" /><path d="M16 17v2a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h2" /></svg>
                            </a>
                        </td>
                        <td class="hidden sm:table-cell">
                                {{ expense.category }}
                        </td>
                        <td class="hidden sm:table-cell">{{ expense.amount }}</td>

                        <td class="hidden sm:table-cell">
                            <select @change="statusChange(expense.id, $event.target.value)" class="select select-xs" :class="expense.statusColor">
                                <option :selected="expense.status === 'pending'">pending</option>
                                <option :selected="expense.status === 'approved'">approved</option>
                                <option :selected="expense.status === 'rejected'">rejected</option>
                            </select>
                        </td>

                        <td class="hidden sm:table-cell" v-show="$page.props.auth.user.isSuperAdmin">{{ expense.store }}
                        </td>
                        <td>
                            <div class="flex items-center space-x-2 justify-center">
                                <Link :href="`/expenses/${expense.id}/edit`" class=" hover:text-green-500">
                                    <svg class="w-6 h-6 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/>
                                    </svg>
                                </Link>
                                <button v-if="$page.props.auth.user.canDelete" @click="deleteSupplierForm(expense.id)" class="text-orange-900 hover:text-orange-600">
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
                    <tr v-if="expenses.data.length <= 0">
                        <td colspan="9" class="text-center">
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
                Showing {{ expenses.from }} to  {{ expenses.to }} of  {{ expenses.total }} results
            </small>
        </div>
        <Paginator :links="expenses.links" />
    </div>
    <!-- delete modal -->
    <Modal :show="deleteModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete expenses
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
                Delete {{ expenseIds.length }} expenses
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
