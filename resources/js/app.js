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
import NumberInput from '@/Components/NumberInput.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import CreateButtonLink from '@/Components/CreateButtonLink.vue';
import DefaultButtonLink from '@/Components/DefaultButtonLink.vue';
import DeleteButton from '@/Components/DeleteButton.vue';
import Paginator from '@/Components/Paginator.vue';
import SearchInput from '@/Components/SearchInput.vue';
import Pagination from '@/Components/Pagination.vue';
import PaginationControlList from '@/Components/PaginationControlList.vue';
import PaginationResultRange from '@/Components/PaginationResultRange.vue';
import FilterByStoreDropdown from '@/Components/FilterByStoreDropdown.vue';
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
            .component('NumberInput', NumberInput)
            .component('Dropdown', Dropdown)
            .component('DropdownLink', DropdownLink)
            .component('InputLabel', InputLabel)
            .component('NavLink', NavLink)
            .component('CreateButtonLink', CreateButtonLink)
            .component('DefaultButtonLink', DefaultButtonLink)
            .component('DeleteButton', DeleteButton)
            .component('Paginator', Paginator)
            .component('SearchInput', SearchInput)
            .component('Pagination', Pagination)
            .component('PaginationControlList', PaginationControlList)
            .component('PaginationResultRange', PaginationResultRange)
            .component('FilterByStoreDropdown', FilterByStoreDropdown)
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
