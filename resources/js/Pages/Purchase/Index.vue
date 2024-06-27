<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router } from '@inertiajs/vue3'
import debounce from "lodash/debounce";
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	orders: Object,
    stores: Object,
	filter: Object,
    suppliers: Object
});

let search = ref(props.filter.search);
let store = ref('');
const url = '/purchase';
let supplier = ref('');

const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);

let orderIds = ref([]);
let selectAllCheckbox = ref(false);

const deleteForm = useForm({id: ''});

const deletePurchaseForm = (purchase_id) => {
	deleteModal.value = true;
	deleteForm.id = purchase_id
}

const closeModal = () => {
	deleteForm.clearErrors()
    deleteModal.value = false;
    deleteAllSelectedModal.value = false;
    deleteForm.reset();
};

const submitDeleteForm = () => {
	deleteForm.delete(`/purchase/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().success('Purchase order has been deleted successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    router.post(route('purchase.bulkDelete'),
    {
        orders_id: orderIds.value
    },
    {
        forceFormData: true,
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            orderIds.value = [];
            closeModal();
            useToast().success('Multiple orders has been deleted successfully!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            selectAllCheckbox.value = false
        },
    })
}

const selectAll = () => {
	if (selectAllCheckbox.value) {
        // If "Select All" checkbox is checked, select all users
        orderIds.value = props.orders.data.map(order => order.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        orderIds.value = [];
      }
}


watch(search, debounce(function (value) {
	router.get('/purchase',
	{ search: value },
	{ preserveState: true, replace:true })
}, 500)) ;
watch(supplier, value => {
	router.get('/purchase',
	{ supplier: value },
	{ preserveState: true, replace:true })
})
watch(store, value => {
	router.get('/purchase',
	{ store: value },
	{ preserveState: true, replace:true })
})
</script>

<template>
    <Head :title="title" />
    <div class="flex justify-end items-center mb-5 gap-3 flex-wrap">
        <CreateButtonLink href="/purchase/create">New pruchase</CreateButtonLink>
        <StatusFilter v-model="status" />
    </div>
    <section class="col-span-12 overflow-hidden bg-base-100 shadow rounded-xl">
        <div class="card-body grow-0">
            <div class="flex justify-between gap-5 flex-col-reverse sm:flex-row">
                <div class="flex gap-2 flex-col sm:flex-row">
                    <FilterByStoreDropdown v-model="store" :stores="stores" :url="url"/>
                    <div class="w-full">
                        <select v-model="supplier" class="select select-bordered select-sm w-full">
                            <option selected value="">Filter by suppliers</option>
                            <option v-for="supplier in suppliers" :value="supplier.name" :key="supplier.id">
                                {{ supplier.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-2 flex-col sm:flex-row">
                    <div class="w-full">
                        <SearchInput v-model="search" @clear-search="search = ''" :url="url"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead class="uppercase">
                    <tr>
                        <th v-if="$page.props.auth.user.canDelete">
                            <input @change="selectAll" v-model="selectAllCheckbox" type="checkbox" class="checkbox checkbox-sm">
                        </th>
                        <th>
                            <div class="font-bold">TRANSACTION</div>
                        </th>
                        <th class="sm:table-cell">
                            <div class="font-bold">Items</div>
                        </th>
                        <th class="sm:table-cell">
                            <div class="font-bold uppercase">Discount</div>
                        </th>
                        <th class="sm:table-cell">
                            <div class="font-bold ">Amount</div>
                        </th>
                        <th class="sm:table-cell">
                            <div class="font-bold">Supplier</div>
                        </th>
                        <th class="sm:table-cell">
                            <div class="font-bold">Status</div>
                        </th>
                        <th class="sm:table-cell" v-show="$page.props.auth.user.isSuperAdmin">
                            <div class="font-bold">Store</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in orders.data" :key="order.id">
                        <td class="w-0" v-if="$page.props.auth.user.canDelete">
                            <input :value="order.id" v-model="orderIds" type="checkbox" class="checkbox checkbox-sm">
                        </td>
                        <td>
                            <div class="text-sm font-bold">
                                <Link :href="`/purchase/${order.id}`" class="hover:text-primary">
                                {{ order.order_no }}
                            </Link>
                            </div>
                            <div>
                                <div class="text-xs opacity-50">
                                    {{ order.created_at }}
                                </div>
                            </div>
                        </td>
                        <!-- These columns will be hidden on small screens -->
                        <td class="sm:table-cell">{{ order.quantity }}</td>
                        <td class="sm:table-cell">{{ order.discount }}</td>
                        <td class="sm:table-cell">{{ order.amount }}</td>
                        <td class="sm:table-cell">{{ order.supplier }}</td>
                        <td class="sm:table-cell">
                            <div class="badge gap-2 badge-warning" v-if="order.status === 'pending'">
                            {{ order.status }}
                            </div>
                            <div class="badge gap-2 badge-success" v-if="order.status === 'completed'">
                            {{ order.status }}
                            </div>
                            <div class="badge gap-2 badge-error" v-if="order.status === 'cancelled'">
                            {{ order.status }}
                            </div>
                        </td>
                        <td class="hidden sm:table-cell" v-show="$page.props.auth.user.isSuperAdmin">{{ order.store }}</td>
                        <td>
                            <div class="flex items-center space-x-2 justify-center">
                                <Link v-if="order.status !== 'completed'" :href="`/purchase/${order.id}/edit`"
                                class="hover:text-green-500">
                                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/>
                                    </svg>
                                </Link>
                                <a :href="route('purchase.downloadPDF', order.id)"
                                class="hover:text-primary" target=_blank>
                                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 13V4M7 14H5a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1h-2m-1-5-4 5-4-5m9 8h.01"/>
                                    </svg>
                                </a>
                                <Link :href="`/purchase/${order.id}`" class="hover:text-primary tooltip tooltip-top" data-tip="Purchase details">
                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M10 3v4a1 1 0 0 1-1 1H5m8-2h3m-3 3h3m-4 3v6m4-3H8M19 4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1ZM8 12v6h8v-6H8Z"/>
                                    </svg>
                                </Link>
                                <DeleteIcon @modal-show="deletePurchaseForm(order.id)"/>

                            </div>
                        </td>

                    </tr>
                    <tr v-if="orders.data.length  <= 0">
                        <td colspan="9" class="text-center">
                            No data found
                        </td>

                    </tr>
                </tbody>
            </table>

        </div>
    </section>
    <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
        <PaginationResultRange :data="orders" />
        <PaginationControlList :url="url" />
        <Pagination :links="orders.links" />
    </div>
    <!-- delete modal -->
    <Modal :show="deleteModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete purchase order
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
                Delete {{ orderIds.length }} purchases
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
