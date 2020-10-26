import Vue from 'vue'
import store from '~/store'
import router from '~/router'
import i18n from '~/plugins/i18n'
import App from '~/components/App'
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'

import '~/plugins'
import '~/components'

import moment from 'moment'

Vue.config.productionTip = false
// Install BootstrapVue
Vue.use(BootstrapVue)
// Optionally install the BootstrapVue icon components plugin
Vue.use(IconsPlugin)

Vue.prototype.moment = moment

/* eslint-disable no-new */
new Vue({
  i18n,
  store,
  router,
  ...App
})
