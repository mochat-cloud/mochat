<template>
  <div id="app">
    <Header v-if="navShow" class="header"></Header>
    <router-view/>
  </div>
</template>

<script>
import Header from '@/components/Header'
import { mapGetters } from 'vuex'
export default {
  name: 'App',
  components: {
    Header
  },
  computed: {
    ...mapGetters(['navShow'])
  }
}
</script>
<style lang="less" scoped>
#app {
  background: #EFEFF4;
  .header {
    margin-bottom: 13px
  }
}
</style>
