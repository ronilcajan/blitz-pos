<template>
    <section id="pricing"
    class="
     bg-white
     pt-20
     lg:pt-[120px]
     pb-12
     lg:pb-[90px]
     relative
     z-20
     overflow-hidden
     ">
    <div class="container">
        <div class="flex flex-wrap -mx-4">
            <div class="w-full px-4">
                <div class="text-center mx-auto mb-[60px] lg:mb-20 max-w-[620px]">
                    <span class="block mb-2 text-lg font-semibold text-primary">
                        Pricing Table
                    </span>
                    <h2
                        class="
                    font-bold
                    text-3xl
                    sm:text-4xl
                    md:text-[40px]
                    text-dark
                    mb-4
                    ">
                        Choose the Plan That’s Right for You
                    </h2>
                    <p class="text-lg leading-relaxed sm:text-xl sm:leading-relaxed text-body-color">
                        Select from our flexible pricing plans designed to meet the needs of businesses of all sizes
                        and stages.
                    </p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-center">
            <div class="w-full md:w-1/2 lg:w-1/3">
                <div class="relative z-10 px-8 py-10 mb-10 overflow-hidden text-center bg-white border rounded-xl border-primary border-opacity-20 shadow-pricing sm:p-12 lg:py-10 lg:px-6 xl:p-12 wow fadeInUp"
                    data-wow-delay=".15s
                 ">
                    <span class="block mb-2 text-base font-medium uppercase text-dark">
                        Free Plan
                    </span>
                    <h2 class="font-semibold text-primary mb-9 text-[28px]">
                        0.00/mo
                    </h2>
                    <div class="mb-10">
                        <p class="mb-1 text-base font-medium leading-loose text-body-color">
                            1 User
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-body-color">
                            Limited Features (Products, Expenses, Sales)
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-body-color">
                            Lifetime access
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-body-color">
                            Free updates
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-body-color">
                            Use on 1 (one) store
                        </p>
                    </div>
                    <div class="w-full">
                        <a :href="route('register')"
                            class="
                       inline-block
                       text-base
                       font-medium
                       text-primary
                       bg-transparent
                       border border-[#D4DEFF]
                       rounded-full
                       text-center
                       py-4
                       px-11
                       hover:text-white hover:bg-primary hover:border-primary
                       transition
                       duration-300
                       ease-in-out
                       ">
                            Get Started for Free
                        </a>
                    </div>
                    <span
                        class="
                    absolute
                    left-0
                    bottom-0
                    z-[-1]
                    w-14
                    h-14
                    rounded-tr-full
                    block
                    bg-primary
                    ">
                    </span>
                </div>
            </div>
            <div class="w-full md:w-1/2 lg:w-1/3">
                <div class="
                 bg-primary bg-gradient-to-b
                 from-primary
                 to-[#179BEE]
                 rounded-xl
                 relative
                 z-10
                 overflow-hidden
                 shadow-pricing
                 py-10
                 px-8
                 sm:p-12
                 lg:py-10 lg:px-6
                 xl:p-12
                 mb-10
                 text-center
                 wow
                 fadeInUp
                 "
                    data-wow-delay=".1s
                 ">
                    <span
                        class="inline-block px-6 py-2 mb-5 text-base font-semibold uppercase bg-white border border-white rounded-full text-primary">
                        Popular
                    </span>
                    <span class="block mb-2 text-base font-medium text-white uppercase">
                        Basic Plan
                    </span>
                    <h2 class="font-semibold text-white mb-9 text-[28px]">
                        $ 4.99/mo
                    </h2>
                    <div class="mb-10">
                        <p class="mb-1 text-base font-medium leading-loose text-white">
                            1 User
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-white">
                            Access features (Products, Inventory, Expenses, Sales, Supplier, Customers)
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-white">
                            Lifetime access
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-white">
                            Free updates
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-white">
                            Use on 1 (one) store
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-white">
                            Lifetime support
                        </p>
                    </div>
                    <div class="w-full">
                        <a href="javascript:void(0)"
                            class="inline-block py-4 text-base font-medium text-center transition duration-300 ease-in-out bg-white border border-white rounded-full text-dark px-11 hover:text-white hover:bg-dark hover:border-dark">
                            Upgrade to Basic
                        </a>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 lg:w-1/3">
                <div class="relative z-10 px-8 py-10 mb-10 overflow-hidden text-center bg-white border rounded-xl border-primary border-opacity-20 shadow-pricing sm:p-12 lg:py-10 lg:px-6 xl:p-12 wow fadeInUp"
                    data-wow-delay=".15s
                 ">
                    <span class="block mb-2 text-base font-medium uppercase text-dark">
                        Standard Plan
                    </span>
                    <h2 class="font-semibold text-primary mb-9 text-[28px]">
                        $ 14.99/mo
                    </h2>
                    <div class="mb-10">
                        <p class="mb-1 text-base font-medium leading-loose text-body-color">
                            Unlimited User
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-body-color">
                            Access all features
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-body-color">
                            Lifetime access
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-body-color">
                            Free updates
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-body-color">
                            Use on 1 (one) store
                        </p>
                        <p class="mb-1 text-base font-medium leading-loose text-body-color">
                            Lifetime support
                        </p>
                    </div>
                    <div class="w-full">
                        <a href="javascript:void(0)"
                            class="
                       inline-block
                       text-base
                       font-medium
                       text-primary
                       bg-transparent
                       border border-[#D4DEFF]
                       rounded-full
                       text-center
                       py-4
                       px-11
                       hover:text-white hover:bg-primary hover:border-primary
                       transition
                       duration-300
                       ease-in-out
                       ">
                            Purchase Now
                        </a>
                    </div>
                    <span
                        class="
                    absolute
                    right-0
                    top-0
                    z-[-1]
                    w-14
                    h-14
                    rounded-bl-full
                    block
                    bg-secondary
                    ">
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>
</template>
