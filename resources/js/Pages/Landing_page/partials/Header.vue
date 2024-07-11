<template>
    <div class="absolute top-0 left-0 z-40 w-full bg-transparent ud-header text-dark">
        <div class="container relative navbar">
            <div class="navbar-start">
                <div class="dropdown">
                    <div tabindex="0" role="button" class=" btn btn-ghost lg:hidden">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h8m-8 6h16" />
                        </svg>
                    </div>
                    <ul
                    tabindex="0"
                    class="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-52 p-2 shadow">
                    <li><a href="#home" class="ud-menu-scroll ">Home</a></li>
                    <li><a href="#about" class="ud-menu-scroll">About</a></li>
                    <li><a href="#feature" class="ud-menu-scroll">Features</a></li>
                    <li><a href="#pricing" class="ud-menu-scroll">Pricing</a></li>
                    <li><a href="#faq" class="ud-menu-scroll">FAQs</a></li>
                    <li><a href="#contact" class="ud-menu-scroll">Contacts</a></li>
                    <li><Link :href="route('login')"
                        class="px-4 py-2 font-medium text-white transition duration-300 ease-in-out border rounded-lg bg-primary hover:bg-opacity-100 hover:text-dark">
                        Sign In
                    </Link></li>
                    </ul>
                </div>
                <div class="w-40 max-w-full">
                    <a href="/" class="block w-full navbar-logo">
                        <img src="assets/images/logo-white.png" alt="logo"
                            class="w-full header-logo" />
                    </a>
                </div>
            </div>
            <div class="hidden navbar-center lg:flex">
                <ul class="space-x-5 text-white menu menu-horizontal">
                    <li><a href="#home" class="ud-menu-scroll ">Home</a></li>
                    <li><a href="#feature" class="ud-menu-scroll">Features</a></li>
                    <li><a href="#about" class="ud-menu-scroll">About</a></li>
                    <li><a href="#pricing" class="ud-menu-scroll">Pricing</a></li>
                    <li><a href="#faq" class="ud-menu-scroll">FAQs</a></li>
                    <li><a href="#contact" class="ud-menu-scroll">Contacts</a></li>
                </ul>
            </div>
            <div class="navbar-end">
                <a href="#pricing"
                    class="px-6 py-3 text-base font-medium text-white duration-300 ease-in-out bg-white rounded-lg bg-opacity-20 hover:bg-opacity-100 hover:text-dark signUpBtn ud-menu-scroll">
                    Get Started
                </a>
            </div>
        </div>
    </div>
</template>
<style scoped>
    .sticky{
        position: fixed;
        z-index: 9999;
        background-color: rgb(255 255 255 / var(--tw-bg-opacity));
        --tw-bg-opacity: 0.8;
        transition-property: color, background-color, border-color, fill, stroke, opacity, box-shadow, transform, filter, -webkit-text-decoration-color, -webkit-backdrop-filter;
        transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
        transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter, -webkit-text-decoration-color, -webkit-backdrop-filter;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 150ms;
        -webkit-backdrop-filter: blur(5px);
        backdrop-filter: blur(5px);
        box-shadow: inset 0 -1px 0 0 rgba(0, 0, 0, 0.1);
    }
    .sticky ul li a{
        color: black;
    }
</style>
