<template>
  <div class="official-account-auth-create">
    <a-card>
      <a-alert message="请授权认证公众号(群打卡、门店活码、互动雷达、裂变、抽奖功能需授权认证服务号)" type="info" show-icon/>
      <div class="images">
        <img
          src="../../assets/wx-mp.png"
          class="icon-wx">
        <img
          src="../../assets/room-punch-auth.png"
          class="icon-link">
        <img
          src="../../assets/logo-no-word.png"
          class="icon-logo">
      </div>
      <div class="btn">
        <a-button type="primary" size="large" @click="go" :disabled="skiplink === ''">
          授权公众号
        </a-button>
      </div>
      <div class="text">
        需要公众号管理员（即公众号注册者）进行扫码授权哦～
      </div>
    </a-card>
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { componentloginpageApi } from '@/api/officialAccount'
export default {
  data () {
    return {
      // 跳转链接
      skiplink: ''
    }
  },
  created () {
    this.getAuthorizeData()
  },
  methods: {
    // 获取授权信息
    getAuthorizeData () {
      componentloginpageApi().then((res) => {
        this.skiplink = res.data.url
      })
    },
    go () {
      window.open(this.skiplink)
    }
  }
}
</script>

<style lang="less" scoped>
.official-account-auth-create {
  width: 79vw;
  height: 69vh;
  display: flex;
  align-items: center;
  justify-content: center;

  .ant-card {
    width: 438px;
  }

  /deep/ .ant-alert-message {
    font-size: 13px;
  }

  .images {
    margin-top: 60px;
    display: flex;
    align-items: center;
    justify-content: center;

    .icon-wx, .icon-logo {
      width: 55px;
    }

    .icon-link {
      width: 30px;
      margin: 0 34px;
    }
  }

  .btn {
    margin: 62px auto 0;
    text-align: center;

    button {
      width: 288px;
      height: 48px;
      font-size: 14px;
    }
  }

  .text {
    margin-top: 31px;
    font-size: 14px;
    color: rgba(0, 0, 0, .65);
    line-height: 20px;
    text-align: center;
  }
}
</style>
