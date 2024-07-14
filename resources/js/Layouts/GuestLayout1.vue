<script setup>
import { onMounted } from 'vue'
import Header from '@/Pages/Landing_page/partials/Header.vue';
import Footer from '@/Pages/Landing_page/partials/Footer.vue';
import BackToTopButton from '@/Pages/Landing_page/partials/BackToTopButton.vue';


onMounted(() => {

    window.addEventListener('scroll', function () {
        const ud_header = document.querySelector(".ud-header");
        const logo = document.querySelector(".header-logo");

        var scrollPosition = window.scrollY;

        if (scrollPosition > 0) {
            ud_header.classList.add("sticky");; // Change the background color to red when scrolled
        } else {
            ud_header.classList.remove("sticky");; // Reset the background color to transparent when scrolled to the top
        }
        // === logo change
        if (ud_header.classList.contains("sticky")) {
            logo.src = "assets/images/logo.png";
        } else {
            logo.src = "assets/images/logo-white.png";
        }

        const backToTop = document.querySelector(".back-to-top");
        if (
            document.body.scrollTop > 50 ||
            document.documentElement.scrollTop > 50
        ) {
            backToTop.style.display = "flex";
        } else {
            backToTop.style.display = "none";
        }
    });

    const faqs = document.querySelectorAll(".single-faq");
    faqs.forEach((el) => {
        el.querySelector(".faq-btn").addEventListener("click", () => {
            el.querySelector(".icon").classList.toggle("rotate-180");
            el.querySelector(".faq-content").classList.toggle("hidden");
        });
    });

    // ===== wow js
    new WOW().init();

    // ====== scroll top js
    function scrollTo(element, to = 0, duration = 500) {
        const start = element.scrollTop;
        const change = to - start;
        const increment = 20;
        let currentTime = 0;

        const animateScroll = () => {
            currentTime += increment;

            const val = Math.easeInOutQuad(currentTime, start, change, duration);

            element.scrollTop = val;

            if (currentTime < duration) {
                setTimeout(animateScroll, increment);
            }
        };

        animateScroll();
    }

    Math.easeInOutQuad = function (t, b, c, d) {
        t /= d / 2;
        if (t < 1) return (c / 2) * t * t + b;
        t--;
        return (-c / 2) * (t * (t - 2) - 1) + b;
    };

    document.querySelector(".back-to-top").onclick = () => {
        scrollTo(document.documentElement);
    };

    const pageLink = document.querySelectorAll(".ud-menu-scroll");

    pageLink.forEach((elem) => {
        elem.addEventListener("click", (e) => {
            e.preventDefault();
            document.querySelector(elem.getAttribute("href")).scrollIntoView({
                behavior: "smooth",
                offsetTop: 1 - 60,
            });
        });
    });

    // section menu active
    function onScroll(event) {
        const sections = document.querySelectorAll(".ud-menu-scroll");
        const scrollPos =
            window.scrollY ||
            document.documentElement.scrollTop ||
            document.body.scrollTop;

        for (let i = 0; i < sections.length; i++) {
            const currLink = sections[i];
            const val = currLink.getAttribute("href");
            const refElement = document.querySelector(val);
            const scrollTopMinus = scrollPos + 73;
            if (
                refElement.offsetTop <= scrollTopMinus &&
                refElement.offsetTop + refElement.offsetHeight > scrollTopMinus
            ) {
                document
                    .querySelector(".ud-menu-scroll")
                    .classList.remove("active");
                currLink.classList.add("active");
            } else {
                currLink.classList.remove("active");
            }
        }
    }

    window.document.addEventListener("scroll", onScroll);
})

</script>

<template>
    <div data-theme="light">

        <Header />


        <slot />


        <Footer />
        <BackToTopButton />
    </div>
</template>
