<script setup>
import { computed,ref, onMounted  } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();

const impersonating = computed(() =>
    page.props.auth.user.impersonate !== null ? true : false
)

const latestActivity = ref(null);
const notificationCount = ref(0);

const fetchUnreadtActivity = async () => {
	try {
        const response = await axios.get('/api/unread-activity'); // Adjust the URL as needed
        latestActivity.value = response.data;
        notificationCount.value = (response.data).length ; // Set the count based on your logic
		console.log(notificationCount.value );
    } catch (error) {
        console.error('Error fetching unread activity:', error);
	}
}

const markAsRead = async () => {
	try {
		const response = await axios.post(`/api/mark-as-read`);
		console.log(response.data.message);
		notificationCount.value = 0;
	} catch (error) {
		console.error('Error marking as read:', error);
	}
}

onMounted(() => {
	fetchUnreadtActivity()
})

</script>
<template>
    <div class="flex flex-col min-h-screen bg-base-200">

		<main class="flex-1">
            <div v-if="impersonating" class="fixed top-0 z-50 flex justify-between w-full p-2 bg-primary start-0">
                <div class="flex items-center mx-auto text-white">
                    <p class="flex items-center text-sm font-normal ">
                        <span class="inline-flex items-center justify-center flex-shrink-0 w-6 h-6 p-1 rounded-full me-3 dark:bg-gray-600">
                            <svg class="w-3 h-3 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 19">
                                <path d="M15 1.943v12.114a1 1 0 0 1-1.581.814L8 11V5l5.419-3.871A1 1 0 0 1 15 1.943ZM7 4H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2v5a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2V4ZM4 17v-5h1v5H4ZM16 5.183v5.634a2.984 2.984 0 0 0 0-5.634Z"/>
                            </svg>
                            <span class="sr-only">Light bulb</span>
                        </span>
                        <span>You're impersonating {{ $page.props.auth.user.name }}.
                            <NavLink :href="route('user.leave')" class="inline font-medium underline underline-offset-2 decoration-600 decoration-solid hover:no-underline">
                                Leave Now</NavLink></span>
                    </p>
                </div>
            </div>

            <div class="navbar bg-base-100"  :class="{'mt-10': impersonating }">
                <div class="navbar-start">
                    <NavLink :href="route('dashboard')" class="btn btn-ghost btn-circle">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-home"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                    </NavLink>
                </div>
                <div class="navbar-center">
                    <NavLink :href="route('dashboard')">
                        <!-- <ApplicationLogo class="fill-current" /> -->
                        <div class="flex items-center">
                            <img :src="$page.props.logo" alt="Laravel" class="h-12" />
                        </div>
                    </NavLink>
                </div>
                <div class="gap-1 navbar-end">
                    <div class="z-10 dropdown dropdown-end">
                        <div tabindex="0" class="btn btn-circle btn-ghost" @click="markAsRead">
                            <div class="indicator">
                                <span v-if="notificationCount" class="badge indicator-item badge-error badge-xs">{{ notificationCount }}</span>
                                <svg data-src="https://unpkg.com/heroicons/20/solid/bell.svg" class="w-5 h-5"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true" data-slot="icon" data-id="svg-loader_2">
                                    <path fill-rule="evenodd"
                                        d="M10 2a6 6 0 0 0-6 6c0 1.887-.454 3.665-1.257 5.234a.75.75 0 0 0 .515 1.076 32.91 32.91 0 0 0 3.256.508 3.5 3.5 0 0 0 6.972 0 32.903 32.903 0 0 0 3.256-.508.75.75 0 0 0 .515-1.076A11.448 11.448 0 0 1 16 8a6 6 0 0 0-6-6ZM8.05 14.943a33.54 33.54 0 0 0 3.9 0 2 2 0 0 1-3.9 0Z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
				
                        <ul tabindex="0" class="p-2 mt-3 shadow-2xl menu dropdown-content w-80 rounded-box bg-base-100" v-if="latestActivity">
                            <template v-for="activity in latestActivity" :key="activity.id">
                                <li>
                                    <a class="gap-4">
                                        <div class="avatar">
                                            <div class="w-8 rounded-full">
                                                <img :src="activity.user_image" />
                                            </div>
                                        </div>
                                        <span class="text-xs">
                                            <b>{{ activity.user }}</b>
                                            <br />
                                            {{ activity.description }}: {{ activity.log_name }}
                                        </span>
                                    </a>
                                </li>
                            </template>
                                <li v-if="!latestActivity.length">
                                    <a class="gap-4">
                                        No unread notification
                                    </a>
                                </li>
                        </ul>
                    </div>
                    <div class="z-10 dropdown-end dropdown">
                        <div tabindex="0" class="avatar placeholder btn btn-circle btn-ghost online" v-show="!$page.props.auth.user.avatar">
                            <div class="w-10 rounded-full bg-neutral text-neutral-content">
                                <span class="text-xl">{{ $page.props.auth.user.name[0] }}</span>
                            </div>
                        </div>

                        <div tabindex="0" class="avatar btn btn-circle btn-ghost online" v-show="$page.props.auth.user.avatar">
                            <div class="w-10 rounded-full">
                                <img :src="$page.props.auth.user.avatar" />
                            </div>
                        </div>

                        <ul tabindex="0" class="p-2 mt-3 shadow-2xl menu dropdown-content w-60 rounded-box bg-base-100 ">
                            <li>
                                <span class="gap-4">
                                    <span>
                                        <b>{{ $page.props.auth.user.name }}</b>
                                        <br />
                                        {{ $page.props.auth.user.email }}
                                    </span>
                                </span>
                            </li>
                            <li>
                                <Link :href="route('profile.show')">
                                    Profile
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('profile.edit')">
                                    Settings
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('logout')" method="post" as="button">Logout</Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <slot/>
            </div>

		</main>

    </div>


</template>
