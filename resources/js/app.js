import './bootstrap';
import '../css/app.css';
import 'vue-toast-notification/dist/theme-default.css';

import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import ToastPlugin from 'vue-toast-notification';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import TextInput from '@/Components/TextInput.vue';
import NavLink from '@/Components/NavLink.vue';
import Paginator from '@/Components/Paginator.vue';
import Modal from '@/Components/Modal.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(ToastPlugin)
            .component('Head', Head)
            .component('Link', Link)
            .component('Checkbox', Checkbox)
            .component('GuestLayout', GuestLayout)
            .component('InputError', InputError)
            .component('PrimaryButton', PrimaryButton)
            .component('SecondaryButton', SecondaryButton)
            .component('SuccessButton', SuccessButton)
            .component('DangerButton', DangerButton)
            .component('TextInput', TextInput)
            .component('InputLabel', InputLabel)
            .component('NavLink', NavLink)
            .component('Paginator', Paginator)
            .component('Modal', Modal)
            .mount(el);
    },
    progress: {
        delay: 100,
        color: '#29d',
        includeCSS: true,
        showSpinner: true,
    },
});
