<template>
  <a-config-provider :locale="locale">
    <a-config-provider>
      <div id="app">
        <router-view />
      </div>
    </a-config-provider>
  </a-config-provider>
</template>

<script>
import zhCN from 'ant-design-vue/lib/locale-provider/zh_CN'
export default {
  data () {
    return {
      locale: zhCN
    }
  }
}
</script>
