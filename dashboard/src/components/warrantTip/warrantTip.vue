<template>
  <div>
    <a-modal v-model="warrantTip" title="设置公众号" :footer="null" :maskClosable="false" :closable="false">
      <p>设置授权公众号后可完整使用功能，可通过公众号授权收集客户信息和客户行为记录</p>
      <div class="warrant_row">
        <h3>提示：</h3>
        <p>1.公众号类型必须为认证服务号</p>
        <p>2.公众号需绑定在企业的微信开放平台</p>
      </div>
      <div style="margin-top: 20px;display: flex;justify-content:flex-end;">
        <a-button type="primary" @click="empowerAccount">授权公众号</a-button>
      </div>
    </a-modal>
  </div>
</template>
<script>
export default {
  data () {
    return {
      warrantTip: false
    }
  },
  methods: {
    show () {
      this.warrantTip = true
    },
    empowerAccount () {
      this.$router.push({ path: '/officialAccount/index' })
    }
  }
}
</script>
<style lang="less" scoped>
/deep/ .ant-modal-header{
  text-align: center;
  font-weight: bold;
}
.warrant_row{
  width:100%;
  padding: 20px 20px 10px 20px;
  border: 1px solid #99C6FF;
  background: #F3F8FF;
}
</style>
