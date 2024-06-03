<script setup>
import { computed, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	deliveries: Object,
    stores: Object,
    suppliers: Object,
	filter: Object
});

const page = usePage();
let search = ref(props.filter.search);
let store = ref('');
let supplier = ref('');
let type = ref('');
const url = '/deliveries';

const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);

let deliveryIds = ref([]);
let selectAllCheckbox = ref(false);

const deleteForm = useForm({id: ''});

const deleteDeliveryForm = (delivery_id) => {
	deleteModal.value = true;
	deleteForm.id = delivery_id
}

const closeModal = () => {
	deleteForm.clearErrors()
    deleteModal.value = false;
    deleteAllSelectedModal.value = false;
    deleteForm.reset();
};

const submitDeleteForm = () => {
	deleteForm.delete(`/deliveries/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().success('Product has been deleted successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
        only: ['deliveries'],
	})
}

const submitBulkDeleteForm = () => {
    router.post(route('delivery.bulkDelete'),
    {
        delivery_id: deliveryIds.value
    },
    {
        forceFormData: true,
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            deliveryIds.value = [];
            closeModal();
            useToast().success('Multiple deliveries has been deleted successfully!', {
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
        deliveryIds.value = props.deliveries.data.map(delivery => delivery.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        deliveryIds.value = [];
      }
}

watch(supplier, value => {
	router.get('/deliveries',
	{ supplier: value },
	{ preserveState: true, replace:true })
})
watch(type, value => {
	router.get('/products',
	{ type: value },
	{ preserveState: true, replace:true })
})

const isSuperAdmin = page.props.auth.user.isSuperAdmin
const canDelete = page.props.auth.user.canDelete

</script>

<template>
    <Head :title="title" />
    <div class="flex justify-end items-center mb-5 gap-3 flex-wrap">
        <CreateButtonLink href="/deliveries/create">New delivery</CreateButtonLink>
        <!-- <DownloadButton :href="route('user.export')">Export</DownloadButton> -->
        <!-- <StatusFilter v-model="type" /> -->
    </div>
    <section class="col-span-12 overflow-hidden bg-base-100 shadow rounded-xl">
        <div class="card-body grow-0">
            <div class="flex justify-between gap-2 flex-col-reverse sm:flex-row">
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
            <div>
                <DeleteButton v-if="canDelete" v-show="deliveryIds.length > 0" @click="deleteAllSelectedModal = true">
                    Delete
                </DeleteButton>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead class="uppercase">
                    <tr>
                        <th v-if="canDelete">
                            <input @change="selectAll" v-model="selectAllCheckbox" type="checkbox" class="checkbox checkbox-sm">
                        </th>
                        <th>
                            <div class="font-bold">TX No</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Purchase</div>
                        </th>
                        <th>
                            <div class="font-bold">Date</div>
                        </th>

                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Quantity</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Amount</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Status</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Supplier</div>
                        </th>

                        <th class="hidden sm:table-cell" v-show="isSuperAdmin">
                            <div class="font-bold">Store</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="delivery in deliveries.data" :key="delivery.id">
                        <td class="w-0" v-if="canDelete">
                            <input :value="delivery.id" v-model="deliveryIds" type="checkbox" class="checkbox checkbox-sm">
                        </td>
                        <td class="sm:table-cell font-bold">
                            {{ delivery.tx_no }}</td>
                        <td class="sm:table-cell">
                                {{ delivery.purchase }}
                        </td>
                            <td class="sm:table-cell">
                            {{ delivery.created_at }}</td>
                        <td class="sm:table-cell">
                            {{ delivery.quantity }} Items</td>
                        <td class="sm:table-cell">
                            {{ delivery.amount }}</td>
                        <td class="hidden sm:table-cell">
                            <div class="badge gap-2 badge-warning"
                            v-if="delivery.status === 'pending' || delivery.status === 'partial'">
                            {{ delivery.status }}
                            </div>
                            <div class="badge gap-2 badge-success" v-if="delivery.status === 'completed' ||
                            delivery.status === 'full'">
                            {{ delivery.status }}
                            </div>
                            <div class="badge gap-2 badge-error" v-if="delivery.status === 'cancelled'">
                            {{ delivery.status }}
                            </div>
                        </td>
                        <td class="sm:table-cell">
                                {{ delivery.supplier }}
                        </td>

                        <td class="sm:table-cell" v-show="isSuperAdmin">
                            {{ delivery.store }}</td>
                        <td>

                            <div class="flex items-center space-x-2 justify-center">
                                <Link v-if="delivery.status !== 'completed'" :href="`/deliveries/${delivery.id}/edit`"
                                class="hover:text-green-500">
                                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/>
                                    </svg>
                                </Link>
                                <a :href="route('delivery.downloadPDF', delivery.id)"
                                class="hover:text-primary">
                                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 13V4M7 14H5a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1h-2m-1-5-4 5-4-5m9 8h.01"/>
                                    </svg>
                                </a>
                                <Link :href="`/deliveries/${delivery.id}`" class="hover:text-primary tooltip tooltip-top" data-tip="Delivery details">
                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M10 3v4a1 1 0 0 1-1 1H5m8-2h3m-3 3h3m-4 3v6m4-3H8M19 4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1ZM8 12v6h8v-6H8Z"/>
                                    </svg>
                                </Link>
                                <DeleteIcon @modal-show="deleteDeliveryForm(delivery.id)"/>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="deliveries.data.length  <= 0">
                        <td colspan="12" class="text-center">
                            No data found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
    <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
        <PaginationResultRange :data="deliveries" />
        <PaginationControlList :url="url" />
        <Pagination :links="deliveries.links" />
    </div>
    <!-- delete modal -->
    <Modal :show="deleteModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete delivery
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
                Delete {{ deliveryIds.length }} products
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
