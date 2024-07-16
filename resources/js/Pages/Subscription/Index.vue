<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    subscription: Object,
});
</script>

<template>
    <Head :title="title" />
    
    <TitleContainer :title="title">
        <CreateButtonLink v-if="subscription.length > 0" href="/suppliers/create">Create subscription</CreateButtonLink>
    </TitleContainer>
   
    <div class="flex-grow mt-5 p-2 lg:gap-x-12 lg:p-4 lg:pt-2 flex flex-col justify-center items-center">

        <div class="flex flex-col gap-3 justify-center items-center" v-if="subscription.length == 0">
            <p>No {{ title }} found!</p>

            <CreateButtonLink href="/suppliers/create">Create subscription</CreateButtonLink>

        </div>

        <div v-if="subscription.length > 0">
            
            <section class="col-span-12 overflow-hidden bg-base-100 shadow-sm rounded-xl">
            <div class="card-body grow-0">
                <div class="flex justify-between gap-2 flex-col-reverse sm:flex-row">
                    <div class="flex gap-2">
                        <FilterByStoreDropdown v-model="store" :stores="stores" :url="url"/>
                        <DeleteButton v-if="$page.props.auth.user.canDelete" v-show="supplierIds.length > 0" @click="deleteAllSelectedModal = true">
                            Delete
                        </DeleteButton>
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
                                <div class="font-bold">Name</div>
                            </th>
                            <th class="hidden sm:table-cell">
                                <div class="font-bold">Contact Person</div>
                            </th>
                            <th class="hidden sm:table-cell">
                                <div class="font-bold">Phone</div>
                            </th>
                            <th class="hidden sm:table-cell">
                                <div class="font-bold">Address</div>
                            </th>
                            <th class="hidden sm:table-cell" v-show="$page.props.auth.user.isSuperAdmin">
                                <div class="font-bold">Store</div>
                            </th>
                            <th class="hidden sm:table-cell">
                                <div class="font-bold">Created on</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="supplier in suppliers.data" :key="supplier.id">
                            <td class="w-0" v-if="$page.props.auth.user.canDelete">
                                <input :value="supplier.id" v-model="supplierIds" type="checkbox" class="checkbox checkbox-sm">
                            </td>
                            <td class="w-5 table-cell">
                                <div class="flex items-center gap-2">
                                    <div class="avatar" v-show="supplier.logo">
                                        <div class="mask mask-squircle h-10 w-10">
                                            <img :src="supplier.logo" alt="logo">
                                        </div>
                                    </div>
                                    <div>

                                        <div class="flex text-sm font-bold gap-2">{{ supplier.name }}
                                        </div>

                                        <div class="text-xs opacity-50">{{ supplier.email }}</div>
                                        <div class="sm:hidden">
                                            <div class="text-xs opacity-50">{{ supplier.contact_person }}</div>
                                            <div class="text-xs opacity-50">{{ supplier.phone }}</div>
                                            <div class="text-xs opacity-50">{{ supplier.address }}</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <!-- These columns will be hidden on small screens -->
                            <td class="hidden sm:table-cell">{{ supplier.contact_person }}</td>
                            <td class="hidden sm:table-cell">{{ supplier.phone }}</td>
                            <td class="hidden sm:table-cell">{{ supplier.address }}</td>
                            <td class="hidden sm:table-cell" v-show="$page.props.auth.user.isSuperAdmin">{{ supplier.store }}</td>
                            <td class="hidden sm:table-cell">{{ supplier.created_at }}</td>
                            <td>
                                <div class="flex items-center space-x-2 justify-center">
                                    <Link :href="`/suppliers/${supplier.id}/edit`" class=" hover:text-green-500">
                                        <svg class="w-6 h-6 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/>
                                        </svg>
                                    </Link>
                                    <DeleteIcon @modal-show="deleteSupplierForm(supplier.id)"/>
                                </div>
                            </td>

                        </tr>
                        <tr v-if="suppliers.data.length  <= 0">
                            <td colspan="7" class="text-center">
                                No data found
                            </td>

                        </tr>
                    </tbody>
                </table>

            </div>
            </section>
            <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
                <PaginationResultRange :data="subscription" />
                <PaginationControlList :url="url" />
                <Pagination :links="subscription.links" />
            </div>
        </div>
        
    </div>

    
    
    
</template>
