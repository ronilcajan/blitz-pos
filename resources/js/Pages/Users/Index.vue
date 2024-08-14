<script setup>
import { ref,computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusFilter from './partials/StatusFilter.vue';
import StatsCard from './partials/StatsCard.vue';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	users: Object,
    stores: Object,
	filter: Object,
    userSummary: Object,
});

const search = ref(props.filter.search);
const store = ref('');
const status = ref('');
const url = ref('/users');

const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);

let userIds = ref([]);
let selectAllCheckbox = ref(false);

const deleteForm = useForm({id: ''});

const deleteUserForm = (user_id) => {
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
	deleteForm.delete(`/users/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().success('Users has been deleted successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    router.post(route('user.bulkDelete'),
    {
        users_id: userIds.value
    },
    {
        forceFormData: true,
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            userIds.value = [];
            closeModal();
            useToast().success('Multiple users has been deleted successfully!', {
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
        userIds.value = props.users.data.map(user => user.id);
    } else {
        // If "Select All" checkbox is unchecked, deselect all users
        userIds.value = [];
    }
}

const statusChange = (userId, selectedStatus) => {
    router.post(route('user.update.status'), { id: userId, status: selectedStatus },
	{
        preserveState: true,
        replace:true,
        preserveScroll: true,
        onSuccess: () => {
			closeModal();
			useToast().success('User status been updated successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
        only: ['users','userSummary'] })
}

const appliedFilters = [
    { title: 'search', value: search },
]

const clearFilters = (filter) => {
if (filter.title == 'search') {
        search.value = '';
    }
}

const product_types = [
    { id: '1', name: 'sellable', },
    // { id: '2', name: 'internal_use', },

]

const usersDataLength = computed(() => {
    if (Object.keys(route().params).length > 0) {
        return props.users.data.length + 1;
    }
    
    return props.users.data.length;
})

</script>

<template>
    <Head :title="title" />

    <TitleContainer :title="title">
        <div class="flex items-center gap-2" v-if="usersDataLength > 0">
            <CreateBtnLink href="/users/create">New user</CreateBtnLink>
            <ActionDropdown :dataIds="productIds" :exportPDFRoute="route('user.export')"
                :exportExcelRoute="false" :withImportBtn="true"
                @open-import-modal="importModal = false" @delete-all-selected="deleteAllSelectedModal = true" />
            <StatusFilter v-model="status" />
        </div>

    </TitleContainer>

    <EmptyContainer :title="title" v-if="usersDataLength == 0">
        <CreateBtnLink href="/users/create">New product</CreateBtnLink>
    </EmptyContainer>

    <div class="flex-grow" v-if="usersDataLength > 0">
        <section class="stats stats-vertical col-span-12 mb-5 w-full shadow-sm xl:stats-horizontal">
            <StatsCard title="Users" :users="userSummary" filter="all">
            Total users who uses the system
            </StatsCard>
            <StatsCard title="Active User" :users="userSummary" filter="active">
                Total users that are active in the system
            </StatsCard>
            <StatsCard title="Inactive User" :users="userSummary" filter="inactive" >
                Total users that are inactive in the system
            </StatsCard>
            <StatsCard title="Blocked User" :users="userSummary" filter="blocked" >
                Total users that are blocked in the system
            </StatsCard>
        </section>
        <section class="col-span-12 overflow-hidden bg-base-100 shadow rounded-xl">
            <div class="card-body grow-0">
                <div class="flex justify-between gap-2 flex-col-reverse sm:flex-row">
                    <div class="flex gap-2 flex-col sm:flex-row">

                        <!-- <SelectDropdownFilter v-if="product_categories.length" v-model="category" :url="url" :title="`category`"
                            :options="product_categories" />
                        <SelectDropdownFilter v-model="type" :url="url" :title="`type`" :options="product_types" /> -->

                    </div>
                    <div class="flex gap-2 flex-col sm:flex-row">
                        <div class="w-full">
                            <SearchInput v-model="search" :url="url" />
                        </div>
                    </div>
                </div>
                <ClearFilters :filters="appliedFilters" @clear-filters="clearFilters" />
            </div>
            <Table>
                <template #table-header>
                    <TableHeader>
                        <TableRow>
                            <TableHead v-if="$page.props.auth.user.canDelete">
                                <input @change="selectAll" v-model="selectAllCheckbox" type="checkbox"
                                    class="checkbox checkbox-sm">
                            </TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Phone</TableHead>
                            <TableHead>Address</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Registered Date</TableHead>
                        </TableRow>
                    </TableHeader>
                </template>
                <template #table-body>
                    <TableBody>
                        <TableRow v-for="user in users.data" :key="user.id">
                            <TableCell v-if="$page.props.auth.user.canDelete">
                                <input :value="user.id" v-model="userIds" type="checkbox"
                                    class="checkbox checkbox-sm">
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <div class="avatar btn btn-circle btn-ghost online" v-show="user.avatar">
                                        <div class="w-10 rounded-full">
                                            <img :src="user.avatar" />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex text-sm font-bold gap-2">{{ user.name }}
                                            <Link :href="route('user.impersonate', user.id)" class="text-primary text-xs" v-show="$page.props.auth.user.isSuperAdmin">Impersonate</Link>
                                        </div>

                                        <div class="text-xs opacity-50">{{ user.email }}</div>
                                        <div class="sm:hidden">
                                            <div class="text-xs opacity-50">{{ user.phone }}</div>
                                            <div class="text-xs opacity-50">{{ user.address }}</div>
                                            <div class="text-xs opacity-50">{{ user.role }}</div>
                                        </div>
                                    </div>
                                </div>
                               
                            </TableCell>
                            <TableCell>{{ user.phone }}</TableCell>
                            <TableCell>{{ user.address }}</TableCell>
                            <TableCell>
                                <div class="badge badge-primary gap-2">
                                    {{ user.role }}
                                </div>
                            </TableCell>

                            <TableCell>
                                <select @change="statusChange(user.id, $event.target.value)" class="select select-xs"  :class="`text-${user.statusColor}`">
                                    <option :selected="user.status === 'active'">active</option>
                                    <option :selected="user.status === 'inactive'">inactive</option>
                                    <option :selected="user.status === 'blocked'">blocked</option>
                                </select>
                            </TableCell>
                            <TableCell>
                                {{user.created_at}}
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <EditIconBtnLink :href="`/users/${user.id}/edit`" />
                                    <DeleteIcon @modal-show="deleteProductForm(user.id)" />
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="users.data == 0">
                            <TableCell :colspan="10" class="text-center">
                                No {{ title.toLocaleLowerCase() }} found!
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </template>
            </Table>

        </section>
        <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
            <PaginationResultRange :data="users" />
            <PaginationControlList :url="url" />
            <Pagination :links="users.links" />
        </div>
    </div>
    <!-- delete modal -->
    <Modal :show="deleteModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete user
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
                Delete {{ userIds.length }} users
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
