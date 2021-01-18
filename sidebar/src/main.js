import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import lazy from 'utils/lazy'
import 'amfe-flexible'
import './global.less'
const ary = Object.keys(lazy)
const app = createApp(App).use(store).use(router)
ary.reduce((innit, current) => {
  return innit.use(lazy[current])
}, app)

app.mount('#app')
