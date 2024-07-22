<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	transactions: Object,
	filter: Object
});

const url = '/in_house';
let search = ref(props.filter.search);
const transactionsDataLength = props.transactions.data.length;

const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);
const importModal = ref(false);

let transactionIds = ref([]);
let selectAllCheckbox = ref(false);

const deleteForm = useForm({id: ''});
const deleteSelectedForm = useForm({transaction_id: []});

const deleteSupplierForm = (transaction_id) => {
	deleteModal.value = true;
	deleteForm.id = transaction_id
}

const submitDeleteForm = () => {
	deleteForm.delete(`/in_house/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			deleteForm.clearErrors()
            deleteModal.value = false;
            deleteForm.reset();
			useToast().error('Supplier has been deleted!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    deleteSelectedForm.suppliers_id = supplierIds.value
    deleteSelectedForm.post(route('suppliers.bulkDelete'),
    {
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            transactionIds.value = [];
            deleteAllSelectedModal.value = false;
            useToast().error('Selected suppliers has been deleted!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            selectAllCheckbox.value = false;
            deleteSelectedForm.reset();
        },
    })
}

const selectAll = () => {
	if (selectAllCheckbox.value) {
        // If "Select All" checkbox is checked, select all users
        transactionIds.value = props.transactions.data.map(transaction => transaction.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        transactionIds.value = [];
      }
}

</script>

<template>
    <Head :title="title" />

    <TitleContainer :title="title">
        <div v-if="transactionsDataLength !== 0" class="flex gap-2">
            <CreateBtnLink href="in_house/create" >New transactions</CreateBtnLink>
            <ActionDropdown
                :dataIds="transactionIds"
                :exportPDFRoute="false"
                :exportExcelRoute="false"
                :withImportBtn="false"
                @open-import-modal="importModal = false"
                @delete-all-selected="deleteAllSelectedModal = true"/>
        </div>
    </TitleContainer>

    <EmptyContainer :title="title" v-if="transactionsDataLength === 0">
        <CreateBtnLink href="in_house/create">New transactions</CreateBtnLink>
    </EmptyContainer> 

    <div class="flex-grow" v-if="transactionsDataLength > 0">
        <section class="col-span-12 overflow-hidden  bg-base-100 shadow-sm rounded">
            <div class="card-body grow-0">
                <div class="flex justify-end  gap-2 flex-col-reverse sm:flex-row">
                    <div class="flex gap-2 flex-col sm:flex-row">
                        <div class="w-full">
                            <SearchInput v-model="search"  @clear-search="search = ''" :url="url"/>
                        </div>
                    </div>
                </div>
            </div>

            <Table>
                <template #table-header>
                    <TableHeader>
                        <TableRow>
                            <TableHead v-if="$page.props.auth.user.canDelete">
                                <input @change="selectAll" v-model="selectAllCheckbox" type="checkbox" class="checkbox checkbox-sm">
                            </TableHead>
                            <TableHead>Tx No</TableHead>
                            <TableHead>Quantity</TableHead>
                            <TableHead>Amount</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Created By</TableHead>
                            <TableHead>Date</TableHead>
                        </TableRow>
                    </TableHeader>
                </template>
                <template #table-body>
                    <TableBody>
                        <TableRow v-for="transaction in transactions.data" :key="transaction.id">
                            <TableCell v-if="$page.props.auth.user.canDelete">
                                <input :value="transaction.id" v-model="transactionIds" type="checkbox" class="checkbox checkbox-sm">
                            </TableCell>
                            <TableCell>{{ transaction.tx_no }}</TableCell>
                            <TableCell>{{ transaction.quantity }}</TableCell>
                            <TableCell>{{ transaction.amount }}</TableCell>
                            <TableCell>
                               <span class="badge badge-success" v-if="transaction.status=='completed'">
                                    {{ transaction.status }}
                                </span>
                                <span class="badge badge-warning" v-else>
                                    {{ transaction.status }}
                                </span>
                            </TableCell>
                            <TableCell>{{ transaction.created_by }}</TableCell>
                            <TableCell>{{ transaction.created_at }}</TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <EditIconBtn v-if="transaction.status=='completed'" :href="`/inventory/in_house/${transaction.id}/edit`"/>
                                    <DeleteIcon @modal-show="deleteTransactionForm(transaction.id)"/>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="transactions.data == 0">
                            <TableCell :colspan="7" class="text-center">
                                No {{ title.toLocaleLowerCase() }} found!
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </template>
            </Table>
           
        </section>
        <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
            <PaginationResultRange :data="transactions" />
            <PaginationControlList :url="url" />
            <Pagination :links="transactions.links" />
        </div>
    </div>

    <!-- <Modal :show="deleteModal" @close="deleteModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete supplier
            </h1>
            <p>Are you sure you want to delete this data? This action cannot be undone.</p>
            <form method="dialog" class="w-full" @submit.prevent="submitDeleteForm">

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="deleteModal = false">Cancel</SecondaryButton>
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
    <Modal :show="deleteAllSelectedModal" @close="deleteAllSelectedModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete {{ supplierIds.length }} suppliers
            </h1>
            <p>Are you sure you want to delete this data? This action cannot be undone.</p>
            <form method="dialog" class="w-full" @submit.prevent="submitBulkDeleteForm">

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="deleteAllSelectedModal = false">Cancel</SecondaryButton>
                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': deleteSelectedForm.processing }"
                        :disabled="deleteSelectedForm.processing"
                    >
                        <span v-if="deleteSelectedForm.processing" class="loading loading-spinner"></span>
                        Delete
                    </DangerButton>
                </div>
            </form>
        </div>
    </Modal> -->
</template>
