<template>
  <a-layout-header class="header">
    <a-menu
      theme="dark"
      mode="horizontal"
      :selected-keys="[topMenuKey.title]"
      :style="{ lineHeight: '60px' }"
    >
      <a-menu-item
        v-for="menuItem in permissionList"
        :key="menuItem.title"
        @click="setTopMenu(menuItem)">
        <a-icon v-if="menuItem.icon" :type="menuItem.icon" />
        <span class="title">{{ menuItem.title }}</span>
      </a-menu-item>
    </a-menu>
  </a-layout-header>
</template>
<script>
import { mapGetters, mapState, mapMutations } from 'vuex'

export default {
  name: 'TopMenu',
  data () {
    return {
    }
  },
  computed: {
    ...mapState({
      // 动态主路由
      mainMenu: state => state.permission.addRouters,
      topMenuKey: state => state.permission.topMenuKey
    }),
    ...mapGetters(['permissionList'])
  },
  created () {
    if (!this.topMenuKey) {
      const key = this.permissionList[0]
      this.setTopMenuKey(key)
    }
  },
  methods: {
    ...mapMutations({
      setTopMenuKey: 'SET_TOP_MENU_KEY',
      setSideMenu: 'SET_SIDE_MENUS'
    }),
    setTopMenu (key) {
      this.setTopMenuKey(key)
      const routes = key.children
      this.setSideMenu(routes)
      this.$router.push({ path: key.path })
    }
  }
}
</script>
<style lang='less' scoped>

</style>
