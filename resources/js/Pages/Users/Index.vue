<script setup>
import { ref, watch } from 'vue';
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

const per_page = ref(10);
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
</script>

<template>
    <Head :title="title" />

    <div class="flex justify-end items-center mb-5 gap-3 flex-wrap">
        <CreateButtonLink href="/users/create">New user</CreateButtonLink>
        <DownloadButton :href="route('user.export')">Export</DownloadButton>
        <StatusFilter v-model="status" />
    </div>
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
        <div class="p-4 grow-0 ">
            <div class="flex justify-between gap-5 flex-col-reverse sm:flex-row">
                <div class="flex w-full gap-3">
                    <FilterByStoreDropdown v-model="store" :stores="stores" :url="url"/>
                    <DeleteButton v-show="userIds.length > 0" @click="deleteAllSelectedModal = true">
                        Delete
                    </DeleteButton>
                </div>
                <SearchInput v-model="search" @clear-search="search = ''" :url="url"/>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead class="uppercase">
                    <tr>
                        <th>
                            <input @change="selectAll" v-model="selectAllCheckbox" type="checkbox" class="checkbox checkbox-sm">
                        </th>
                        <th class="w-1/6">
                            <div class="font-bold">Name</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Phone</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Address</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Role</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Status
                            </div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Registered Date</div>
                        </th>
                        <th class="hidden sm:table-cell" v-show="$page.props.auth.user.isSuperAdmin">
                            <div class="font-bold">Store</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users.data" :key="user.id">
                        <td class="w-0">
                            <input :value="user.id" v-model="userIds" type="checkbox" class="checkbox checkbox-sm">
                        </td>
                        <td class="w-10 table-cell">
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder " v-show="!user.avatar">
                                    <div class="w-10 bg-neutral text-neutral-content rounded-full">
                                        <span class="text-xl">{{ user.name[0] }}</span>
                                    </div>
                                </div>

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
                        </td>
                        <!-- These columns will be hidden on small screens -->
                        <td class="hidden sm:table-cell">{{ user.phone }}</td>
                        <td class="hidden sm:table-cell">{{ user.address }}</td>
                        <td class="hidden sm:table-cell">
                            <div class="badge badge-primary gap-2">
                            {{ user.role }}
                            </div>
                        </td>
                        <td class="hidden sm:table-cell">
                            <select @change="statusChange(user.id, $event.target.value)" class="select select-xs"  :class="`text-${user.statusColor}`">
                                <option :selected="user.status === 'active'">active</option>
                                <option :selected="user.status === 'inactive'">inactive</option>
                                <option :selected="user.status === 'blocked'">blocked</option>
                            </select>
                        </td>
                        <td class="hidden sm:table-cell">{{ user.created_at }}</td>
                        <td class="hidden sm:table-cell" v-show="$page.props.auth.user.isSuperAdmin">{{ user.store }}</td>
                        <td>
                            <div class="flex items-center space-x-2 justify-center">
                                <Link :href="`/users/${user.id}/edit`" class=" hover:text-green-500">
                                    <svg class="w-6 h-6 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/>
                                    </svg>
                                </Link>
                                <DeleteIcon @modal-show="deleteUserForm(user.id)"/>
                            </div>
                        </td>

                    </tr>
                    <tr v-if="users.data.length <= 0">
                        <td colspan="7" class="text-center">
                            No data found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
    <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
        <PaginationResultRange :data="users" />
        <PaginationControlList v-model="per_page" :url="url" />
        <Pagination :links="users.links" />
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
