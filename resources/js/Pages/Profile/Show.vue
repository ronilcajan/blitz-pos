<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
defineOptions({ layout: AuthenticatedLayout })

defineProps({
    profile: Object,
    user_details: Object
});
</script>

<template>
    <Head title="Profile" />
    <div class="grid grid-rows-2 gap-4 md:grid-cols-3">
        <div class="col-span-2 md:col-span-1">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <div class="avatar placeholder" v-show="!profile.profile_photo_url">
                        <div class="bg-neutral text-neutral-content rounded w-24">
                            <span class="text-3xl">{{ profile.name[0] }}</span>
                        </div>
                    </div> 
                    <div class="avatar" v-show="profile.profile_photo_url">
                        <div class="w-24 rounded">
                            <img :src="profile.profile_photo_url" />
                        </div>
                    </div>
                    <h2 class="card-title">{{ profile.name }}</h2>
                    <span class="flex gap-1 items-center">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M10 2a3 3 0 0 0-3 3v1H5a3 3 0 0 0-3 3v2.382l1.447.723.005.003.027.013.12.056c.108.05.272.123.486.212.429.177 1.056.416 1.834.655C7.481 13.524 9.63 14 12 14c2.372 0 4.52-.475 6.08-.956.78-.24 1.406-.478 1.835-.655a14.028 14.028 0 0 0 .606-.268l.027-.013.005-.002L22 11.381V9a3 3 0 0 0-3-3h-2V5a3 3 0 0 0-3-3h-4Zm5 4V5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v1h6Zm6.447 7.894.553-.276V19a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3v-5.382l.553.276.002.002.004.002.013.006.041.02.151.07c.13.06.318.144.557.242.478.198 1.163.46 2.01.72C7.019 15.476 9.37 16 12 16c2.628 0 4.98-.525 6.67-1.044a22.95 22.95 0 0 0 2.01-.72 15.994 15.994 0 0 0 .707-.312l.041-.02.013-.006.004-.002.001-.001-.431-.866.432.865ZM12 10a1 1 0 1 0 0 2h.01a1 1 0 1 0 0-2H12Z" clip-rule="evenodd"/>
                        </svg>
                        {{ profile.roles[0].name }}
                    </span>
                    
                    <div class="mt-2">
                        <small class="text-content font-bold">Email Address</small>
                        <p>{{ profile.email }}</p>
                    </div>
                    <div class="mt-2">
                        <small class="text-content font-bold">Contact Number</small>
                        <p>{{ profile.phone }}</p>
                    </div>
                    <div class="mt-2">
                        <small class="primary-content font-bold">Address</small>
                        <p>{{ profile.address }}</p>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl mt-5">
                <div class="card-body">
                    <h2 class="card-title font-bold">Skills</h2>
                    <div class="mt-2">
                        <div v-for="(skill, index) in user_details.skills.split(',')" :key="index" class="badge badge-neutral mr-2">{{ skill }}</div>
                        
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow-xl mt-5">
                <div class="card-body">
                    <h2 class="card-title font-bold">Hobbies</h2>
                    <div class="mt-2">
                        <div v-for="(hobby, index) in user_details.hobbies.split(',')" :key="index" class="badge badge-primary mr-2">{{ hobby }}</div>
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-2">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title font-bold">General information</h2>
                    <h4 class="font-bold">About me</h4>
                    <p>{{ user_details.bio }}</p>
                    <div class="mt-2">
                        <small class="primary-content font-bold">Education</small>
                        <p>{{ user_details.education }}</p>
                    </div>
                    <div class="mt-2">
                        <small class="primary-content font-bold">Position</small>
                        <p>{{ user_details.position }}</p>
                    </div>
                    <div class="mt-2">
                        <small class="primary-content font-bold">Join Date</small>
                        <p>{{ user_details.join_date }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
