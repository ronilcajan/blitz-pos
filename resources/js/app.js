import './bootstrap';
import '../css/app.css';
import 'vue-toast-notification/dist/theme-default.css';
import.meta.glob('../images/**');

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
import TextArea from '@/Components/TextArea.vue';
import NumberInput from '@/Components/NumberInput.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import CreateButtonLink from '@/Components/CreateButtonLink.vue';
import DefaultButtonLink from '@/Components/DefaultButtonLink.vue';
import SaveButton from '@/Components/SaveButton.vue';
import DeleteButton from '@/Components/DeleteButton.vue';
import DeleteIcon from '@/Components/DeleteIcon.vue';
import EditIconBtn from '@/Components/EditIconBtn.vue';
import SearchInput from '@/Components/SearchInput.vue';
import PrintButton from '@/Components/PrintButton.vue';
import CancelButton from '@/Components/CancelButton.vue';
import DownloadButton from '@/Components/DownloadButton.vue';
import ImagePreview from '@/Components/ImagePreview.vue';
import Pagination from '@/Components/Pagination.vue';
import ImageUpload from '@/Components/ImageUpload.vue';
import PaginationControlList from '@/Components/PaginationControlList.vue';
import PaginationResultRange from '@/Components/PaginationResultRange.vue';
import FilterByStoreDropdown from '@/Components/FilterByStoreDropdown.vue';
import Modal from '@/Components/Modal.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ActionGroupDropdown from '@/Components/ActionGroupDropdown.vue';
import FilterDate from '@/Components/FilterDate.vue';
import TitleContainer from '@/Components/TitleContainer.vue';
import Table from '@/Components/Table.vue';
import TableHeader from '@/Components/TableHeader.vue';
import TableBody from '@/Components/TableBody.vue';
import TableHead from '@/Components/TableHead.vue';
import TableRow from '@/Components/TableRow.vue';
import TableCell from '@/Components/TableCell.vue';
import Avatar from '@/Components/Avatar.vue';
import EmptyContainer from '@/Components/EmptyContainer.vue';
import SaveAlert from '@/Components/SaveAlert.vue';
import CreateBtn from '@/Components/CreateBtn.vue';
import CreateSubmitBtn from '@/Components/CreateSubmitBtn.vue';
import SaveSubmitBtn from '@/Components/SaveSubmitBtn.vue';
import ActionDropdown from '@/Components/ActionDropdown.vue';
import DateRangeFilter from '@/Components/DateRangeFilter.vue';
import ClearFilters from '@/Components/ClearFilters.vue';
import SelectDropdownFilter from '@/Components/SelectDropdownFilter.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {

        const app = createApp({ render: () => h(App, props) });

        app.config.warnHandler = function (msg, vm, trace) {
            // Ignore specific warnings
            if (msg.includes('Extraneous non-props attributes')) {
                return;
            }
            // Log other warnings to the console
            console.warn(`[Vue warn]: ${msg}${trace}`);
        };

        return app.use(plugin)
            .use(ZiggyVue)
            .use(ToastPlugin)
            .component('Head', Head)
            .component('Link', Link)
            .component('ApplicationLogo', ApplicationLogo)
            .component('Checkbox', Checkbox)
            .component('GuestLayout', GuestLayout)
            .component('InputError', InputError)
            .component('PrimaryButton', PrimaryButton)
            .component('SecondaryButton', SecondaryButton)
            .component('SuccessButton', SuccessButton)
            .component('DangerButton', DangerButton)
            .component('PrintButton', PrintButton)
            .component('CancelButton', CancelButton)
            .component('DownloadButton', DownloadButton)
            .component('ImagePreview', ImagePreview)
            .component('TextInput', TextInput)
            .component('TextArea', TextArea)
            .component('NumberInput', NumberInput)
            .component('Dropdown', Dropdown)
            .component('DropdownLink', DropdownLink)
            .component('InputLabel', InputLabel)
            .component('FilterDate', FilterDate)
            .component('ImageUpload', ImageUpload)
            .component('NavLink', NavLink)
            .component('CreateButtonLink', CreateButtonLink)
            .component('DefaultButtonLink', DefaultButtonLink)
            .component('SaveButton', SaveButton)
            .component('DeleteButton', DeleteButton)
            .component('EditIconBtn', EditIconBtn)
            .component('DeleteIcon', DeleteIcon)
            .component('SearchInput', SearchInput)
            .component('Pagination', Pagination)
            .component('PaginationControlList', PaginationControlList)
            .component('PaginationResultRange', PaginationResultRange)
            .component('FilterByStoreDropdown', FilterByStoreDropdown)
            .component('ActionGroupDropdown', ActionGroupDropdown)
            .component('Modal', Modal)
            .component('TitleContainer', TitleContainer)
            .component('Table', Table)
            .component('TableHeader', TableHeader)
            .component('TableBody', TableBody)
            .component('TableHead', TableHead)
            .component('TableRow', TableRow)
            .component('TableCell', TableCell)
            .component('Avatar', Avatar)
            .component('EmptyContainer', EmptyContainer)
            .component('SaveAlert', SaveAlert)
            .component('CreateBtn', CreateBtn)
            .component('CreateSubmitBtn', CreateSubmitBtn)
            .component('SaveSubmitBtn', SaveSubmitBtn)
            .component('ActionDropdown', ActionDropdown)
            .component('DateRangeFilter', DateRangeFilter)
            .component('ClearFilters', ClearFilters)
            .component('SelectDropdownFilter', SelectDropdownFilter)

            .mount(el);
    },
    progress: {
        delay: 100,
        color: '#29d',
        includeCSS: true,
        showSpinner: true,
    },
});
