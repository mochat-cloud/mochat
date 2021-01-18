<template>
  <div class="header-wrapper">
    <span class="icon-wrapper" @click="trigger">
      <img class="icon" :src="iconSrc" alt="">
    </span>
    <top-menu class="top-menu"></top-menu>
    <right-content class="right-content" :is-mobile="isMobile" :theme="theme" />
  </div>
</template>
<script>
import RightContent from './RightContent'
import TopMenu from './Topmenu'
import { mapState, mapMutations } from 'vuex'

export default {
  components: {
    RightContent,
    TopMenu
  },
  props: {
    isMobile: {
      type: Boolean,
      default: false
    },
    theme: {
      type: String,
      default: 'dark'
    }
  },
  data () {
    return {
      iconSrc: require('@/assets/push.png')
    }
  },
  computed: {
    ...mapState({
      sideCollapsed: state => state.app.sideCollapsed
    })
  },
  created () {

  },
  methods: {
    ...mapMutations({
      sidebarType: 'SIDEBAR_TYPE'
    }),
    trigger () {
      this.sidebarType(!this.sideCollapsed)
    }
  }
}
</script>
<style lang='less' scoped>
.header-wrapper{
  flex: 1;
  display: flex;
  align-items: center;
  .top-menu{
    flex: 1
  }
  .right-content{
    display: flex;
    justify-content: flex-end;
    align-items: center;
    flex: 0 0 250px;
  }
  .icon-wrapper {
    width: 50px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    .icon {
      color: #fff;
      display: inline-block;
      width: 20px;
      height: 15px;
    }
  }

}
</style>
