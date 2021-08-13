<template>
  <div>
    <qrcode :value="text" :background="colorLight" :foreground="colorDark" :level="correctLevel" :size="width"></qrcode>
  </div>
</template>

<script>
import Vue from 'vue'
import qrcode from 'vue-qrcode2'

Vue.use(qrcode)

export default {
  data () {
    return {
      width: 120,
      colorDark: '#000000',
      colorLight: '#ffffff',
      text: '',
      correctLevel: 'H'
    }
  }
}
</script>
