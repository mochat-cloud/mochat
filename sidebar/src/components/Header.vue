<template>
  <div class="header">
    <van-nav-bar fixed :z-index="9999" :border="true" safe-area-inset-top placeholder >
      <template #title>
        <router-link class="nav" :to="{ path: '/customer', query: { agentId } }">客户画像</router-link>
        <router-link class="nav" to="/mediumGroup">素材库</router-link>
      </template>
    </van-nav-bar>
  </div>
</template>

<script>
import { getCookie } from '@/utils'

export default {
  name: 'Header',
  data () {
    return {
      agentId: ''
    }
  },
  created () {
    this.agentId = getCookie('agentId')
  }
}
</script>

<style lang="less" scoped>
.nav {
  // width: 136px;
  // height: 48px;
  margin-right: 68px;
  font-size: 34px;
  font-family: PingFangSC, PingFangSC-Medium;
  font-weight: 500;
  text-align: center;
  color:#999999;
  line-height: 48px;
  &.router-link-exact-active {
    color: #000000;
  }
}
</style>
