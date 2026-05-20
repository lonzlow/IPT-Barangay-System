import './bootstrap';

import Alpine from 'alpinejs';
import jQuery from 'jquery';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import axios from 'axios';

// Expose to global scope for use in Blade templates
window.$ = jQuery;
window.jQuery = jQuery;
window.DataTable = DataTable;
window.$.fn.DataTable = DataTable;
window.axios = axios;

await import('select2/dist/js/select2.full.min.js');

window.Alpine = Alpine;

Alpine.start();
