require('../css/app.scss');

global.jQuery = require('jquery');
global.axios = require('axios');

require('bootstrap-sass');

import Vue from 'vue';
import motdepasse from './components/motdepasseComponent'

new Vue({
    el: '#app',
    components: {motdepasse}
});