// with polyfills
import 'core-js/stable'
import 'regenerator-runtime/runtime'

import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store/'
import { VueAxios } from './utils/request'
import ProLayout, { PageHeaderWrapper } from '@ant-design-vue/pro-layout'
import themePluginConfig from '../config/themePluginConfig'
import upload from '@/components/Upload/index'
import preview from '@/components/Preview/index'
import EnterText from '@/components/EnterText/EnterText'
import VueDragResize from 'vue-drag-resize'
import vcolorpicker from 'vcolorpicker'

import './core/lazy_use'
import './router/navigationGuards' // permission control
import './utils/filter' // global filter
import './global.less'

Vue.config.productionTip = false

// mount axios to `Vue.$http` and `this.$http`
Vue.use(VueAxios)
Vue.component('vue-drag-resize', VueDragResize)
Vue.use(vcolorpicker)
Vue.component('pro-layout', ProLayout)
Vue.component('page-header-wrapper', PageHeaderWrapper)
Vue.component('m-upload', upload)
Vue.component('m-preview', preview)
Vue.component('m-enter-text', EnterText)
Vue.use(vcolorpicker)

window.umi_plugin_ant_themeVar = themePluginConfig.theme

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')
