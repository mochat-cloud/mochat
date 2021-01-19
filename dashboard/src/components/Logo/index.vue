<template>
  <img v-if="!sideCollapsed" class="logo" :src="src"/>
</template>

<script>
import { mapState } from 'vuex'
export default {
  data () {
    return {
      src: require('@/assets/title.png')
    }
  },
  computed: {
    ...mapState({
      sideCollapsed: state => state.app.sideCollapsed
    })
  }
}
</script>

<style>

</style>
