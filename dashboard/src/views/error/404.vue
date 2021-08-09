<template>
  <a-result status="404" title="404" sub-title="对不起，MoChat该页面未找到！">
    <template #extra>
      <a-button type="primary" @click="toHome">
        回首页
      </a-button>
    </template>
  </a-result>
</template>

<script>
export default {
  name: 'Error404',
  methods: {
    toHome () {
      this.$router.push({ path: '/' })
    }
  }
}
</script>
