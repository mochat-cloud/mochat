import view from './index.vue'
import Vue from 'vue'

const Constructor = Vue.extend(view)

export default class {
  constructor (el, options) {
    const instance = new Constructor({
      data: options
    })
    instance.id = '_qrcode_' + Math.random()
    instance.$mount()
    el.appendChild(instance.$el)
  }
}
