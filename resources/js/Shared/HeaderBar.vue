
<script setup>
import { ref, watch } from "vue";
import { usePage } from '@inertiajs/vue3'

const darkMode = ref(localStorage.getItem("theme"));
const page = usePage();

watch(darkMode, () => {
	localStorage.setItem('theme', darkMode.value ? 'dark' : 'light');
	document.documentElement.setAttribute("data-theme", localStorage.getItem("theme"))
})
</script>

<template>

    <!-- header -->
    <header class="col-span-12 flex items-center gap-2 lg:gap-1">
		<label for="my-drawer" class="btn btn-square btn-ghost drawer-button lg:hidden">
			<svg data-src="https://unpkg.com/heroicons/20/solid/bars-3.svg" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon" data-id="svg-loader_1">
			<path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"></path>
			</svg>
		</label>
		<div class="grow">
			<h1 class="lg:text-2xl lg:font-bold">
				{{ page.props.title }}
			</h1>
		</div>
        <label class="swap swap-rotate p-1">
            <!-- this hidden checkbox controls the state -->
            <input type="checkbox" v-model="darkMode" :checked="darkMode === 'dark' ? true : false" class="theme-controller" value="dark" />
            <!-- sun icon -->
            <svg class="swap-off fill-current w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z"/></svg>
            <!-- moon icon -->
            <svg class="swap-on fill-current w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z"/></svg>

        </label>
        <div class="btn btn-circle btn-ghost">
            <Link :href="route('pos')" title="POS">
                <svg  xmlns="http://www.w3.org/2000/svg"  width="22"  height="22"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-cash-register"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M21 15h-2.5c-.398 0 -.779 .158 -1.061 .439c-.281 .281 -.439 .663 -.439 1.061c0 .398 .158 .779 .439 1.061c.281 .281 .663 .439 1.061 .439h1c.398 0 .779 .158 1.061 .439c.281 .281 .439 .663 .439 1.061c0 .398 -.158 .779 -.439 1.061c-.281 .281 -.663 .439 -1.061 .439h-2.5" /><path d="M19 21v1m0 -8v1" /><path d="M13 21h-7c-.53 0 -1.039 -.211 -1.414 -.586c-.375 -.375 -.586 -.884 -.586 -1.414v-10c0 -.53 .211 -1.039 .586 -1.414c.375 -.375 .884 -.586 1.414 -.586h2m12 3.12v-1.12c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586h-2" /><path d="M16 10v-6c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586h-4c-.53 0 -1.039 .211 -1.414 .586c-.375 .375 -.586 .884 -.586 1.414v6m8 0h-8m8 0h1m-9 0h-1" /><path d="M8 14v.01" /><path d="M8 17v.01" /><path d="M12 13.99v.01" /><path d="M12 17v.01" /></svg>
            </Link>
        </div>
		<!-- <div>
			<input
				type="text"
				placeholder="Search"
				class="input input-sm rounded-full max-sm:w-24" />
		</div> -->
					<!-- dropdown -->
		<div class="dropdown dropdown-end z-10">
			<div tabindex="0" class="btn btn-circle btn-ghost">
				<div class="indicator">
					<span class="badge indicator-item badge-error badge-xs"></span>
					<svg data-src="https://unpkg.com/heroicons/20/solid/bell.svg" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon" data-id="svg-loader_2">
					<path fill-rule="evenodd" d="M10 2a6 6 0 0 0-6 6c0 1.887-.454 3.665-1.257 5.234a.75.75 0 0 0 .515 1.076 32.91 32.91 0 0 0 3.256.508 3.5 3.5 0 0 0 6.972 0 32.903 32.903 0 0 0 3.256-.508.75.75 0 0 0 .515-1.076A11.448 11.448 0 0 1 16 8a6 6 0 0 0-6-6ZM8.05 14.943a33.54 33.54 0 0 0 3.9 0 2 2 0 0 1-3.9 0Z" clip-rule="evenodd"></path>
					</svg>
				</div>
			</div>
			<ul
				tabindex="0"
				class="menu dropdown-content mt-3 w-80 rounded-box bg-base-100 p-2 shadow-2xl">
				<li>
					<a class="gap-4">
						<div class="avatar">
							<div class="w-8 rounded-full">
								<img src="https://picsum.photos/80/80?1" />
							</div>
						</div>
						<span>
							<b>New message</b>
							<br />
							Alice: Hi, did you get my files?
						</span>
					</a>
				</li>
				<li>
					<a class="gap-4">
						<div class="avatar">
							<div class="w-8 rounded-full">
								<img src="https://picsum.photos/80/80?2" />
							</div>
						</div>
						<span>
							<b>Reminder</b>
							<br />
							Your meeting is at 10am
						</span>
					</a>
				</li>
				<li>
					<a class="gap-4">
						<div class="avatar">
							<div class="w-8 rounded-full">
								<img src="https://picsum.photos/80/80?3" />
							</div>
						</div>
						<span>
							<b>New payment</b>
							<br />
							Received $2500 from John Doe
						</span>
					</a>
				</li>
				<li>
					<a class="gap-4">
						<div class="avatar">
							<div class="w-8 rounded-full">
								<img src="https://picsum.photos/80/80?4" />
							</div>
						</div>
						<span>
							<b>New payment</b>
							<br />
							Received $1900 from Alice
						</span>
					</a>
				</li>
			</ul>
		</div>
					<!-- /dropdown -->
					<!-- dropdown -->
		<div class="dropdown-end dropdown z-10">

			<div tabindex="0" class="avatar placeholder btn btn-circle btn-ghost online" v-show="!$page.props.auth.user.avatar">
				<div class="w-10 bg-neutral text-neutral-content rounded-full">
					<span class="text-xl">{{ $page.props.auth.user.name[0] }}</span>
				</div>
			</div>

            <div tabindex="0" class="avatar btn btn-circle btn-ghost online" v-show="$page.props.auth.user.avatar">
				<div class="w-10 rounded-full">
					<img :src="$page.props.auth.user.avatar" />
				</div>
			</div>

            <ul tabindex="0" class="menu dropdown-content mt-3 w-60 rounded-box bg-base-100 p-2 shadow-2xl ">
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
		<!-- /dropdown -->

	</header>

	<header class="col-span-12 flex gap-2 lg:gap-4 mt-5">
		<div v-show="$page.props.flash.message" role="alert" class="alert alert-success">
			<svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
			<span>{{ $page.props.flash.message }}</span>
			</div>
	</header>

				<!-- /header -->
</template>
