<script setup>
import { ref, computed} from 'vue';
import { useToast } from 'vue-toast-notification';
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	logs: Object,
	filter: Object
});

let logIds = ref([]);
const url = '/logs';


const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);
const selectAllCheckbox = ref(false);

const deleteForm = useForm({id: ''});
const deleteSelectedForm = useForm({logs_id: []});

const deleteLogs = (log_id) => {
	deleteModal.value = true;
	deleteForm.id = log_id
}

const logsDataLength = computed(() => {
     if (Object.keys(route().params).length > 0) {
        return props.logs.data.length + 1;
    }
    
    return props.logs.data.length;
});

const selectAll = () => {
	if (selectAllCheckbox.value) {
        // If "Select All" checkbox is checked, select all users
        logIds.value = props.logs.data.map(log => log.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        logIds.value = [];
      }
}

const submitDeleteForm = () => {
	deleteForm.delete(`/logs/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			deleteForm.clearErrors()
            deleteModal.value = false;
            deleteForm.reset();
			useToast().error('Logs has been deleted!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    deleteSelectedForm.logs_id = logIds.value
    deleteSelectedForm.post(route('logs.bulkDelete'),
    {
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            logIds.value = [];
            deleteAllSelectedModal.value = false;
            useToast().error('Selected logs has been deleted!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            selectAllCheckbox.value = false;
            deleteSelectedForm.reset();
        },
    })
}

</script>
<template>
     <Head :title="title" />

<TitleContainer :title="title">
    <div v-if="logsDataLength !== 0" class="flex items-center gap-2">
        <ActionDropdown
            :dataIds="logIds"
            :exportPDFRoute="false"
            :exportExcelRoute="false"
            :withImportBtn="false"
            @delete-all-selected="deleteAllSelectedModal = true"/>
    </div>
</TitleContainer>

<EmptyContainer :title="title" v-if="logsDataLength === 0"/> 

<div class="flex-grow" v-if="logsDataLength > 0">
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
                        <TableHead>Log Name</TableHead>
                        <TableHead>Description</TableHead>
                        <TableHead>Event</TableHead>
                        <TableHead>Date</TableHead>
                        <TableHead>Cause by</TableHead>
                    </TableRow>
                </TableHeader>
            </template>
            <template #table-body>
                <TableBody>
                    <TableRow v-for="log in logs.data" :key="log.id">
                        <TableCell v-if="$page.props.auth.user.canDelete">
                            <input :value="log.id" v-model="logIds" type="checkbox" class="checkbox checkbox-sm">
                        </TableCell>
                        <TableCell>{{ log.log_name }}</TableCell>
                        <TableCell>  {{ log.description }}{{ log.data ? ': ' + log.data : '' }}
                        </TableCell>
                        <TableCell>
                            <div class="badge badge-primary badge-sm" v-if="log.event === 'created'">{{ log.event }}</div>
                            <div class="badge badge-success badge-sm" v-if="log.event === 'updated'">{{ log.event }}</div>
                            <div class="badge badge-error badge-sm" v-if="log.event === 'deleted'">{{ log.event }}</div>
                        </TableCell>
                        <TableCell>{{ log.created_at }}</TableCell>
                        <TableCell>{{ log.created_by }}</TableCell>
                        <TableCell class="flex gap-2">
                            <div class="flex items-center gap-2">
                                <DeleteIcon @modal-show="deleteLogs(log.id)"/>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="logs.data == 0">
                        <TableCell :colspan="7" class="text-center">
                            No {{ title.toLocaleLowerCase() }} found!
                        </TableCell>
                    </TableRow>
                </TableBody>
            </template>
        </Table>
       
    </section>
    <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
        <PaginationResultRange :data="logs" />
        <PaginationControlList :url="url" />
        <Pagination :links="logs.links" />
    </div>
</div>

 <!-- delete modal -->
 <Modal :show="deleteModal" @close="deleteModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete log
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
    <!-- delete all selected modal -->
    <Modal :show="deleteAllSelectedModal" @close="deleteAllSelectedModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete {{ logIds.length }} logs
            </h1>
            <p>Are you sure you want to delete the selected data? This action cannot be undone.</p>
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
    </Modal>

</template>