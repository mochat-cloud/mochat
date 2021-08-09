import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import Antd from 'ant-design-vue';
import LuckDraw from 'vue-luck-draw'
Vue.use(LuckDraw);
Vue.use(Antd);

import './plugins/ant'
import './plugins/axios'
import './plugins/utils'

Vue.config.productionTip = false

// router.beforeEach((to, from, next) => {
//   // alert(123)
// })

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')
