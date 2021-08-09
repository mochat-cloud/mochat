<template>
  <div class="official-account-auth">
    <a-card>
      <div class="title">
        1、请授权认证订阅号、服务号 2、请将公众号绑定在企业的微信开放平台，
        <a href="#">如何绑定</a>
      </div>
      <div class="tips">
        <div class="head">
          授权公众号后可使用以下功能：
        </div>
        <p>素材库-文章：可同步公众号文章素材至素材库，员工可通过【侧边栏-快捷回复-文章】发送文章至客户</p>
        <p>群打卡：获取参与打卡活动的客户基本信息，可根据客户不同行为为客户打标签</p>
        <p>群红包：根据客户的openID发红包，在【客户画像-动态】中记录客户领取红包事件</p>
      </div>
      <div class="account-list">
        <div class="item" v-for="(item,index) in accounts" :key="index">
          <div class="avatar">
            <img :src="item.avatar">
          </div>
          <div class="info">
            <div class="name">{{ item.nickname }}</div>
            <div class="type">服务号</div>
          </div>
        </div>
        <div class="add" @click="$router.push('/officialAccount/create')">
          <a-icon type="plus-circle" :style="{ fontSize: '32px', color: '#ccc' }"/>
          <span>添加公众号</span>
        </div>
      </div>
    </a-card>
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { publicApi } from '@/api/officialAccount'
export default {
  data () {
    return {
      accounts: []
    }
  },
  created () {
    this.getofficialList()
  },
  methods: {
    getofficialList () {
      publicApi().then((res) => {
        this.accounts = res.data
      })
    }
  }
}
</script>

<style lang="less" scoped>
.title {
  font-size: 15px;
}

.tips {
  width: 640px;
  background: #f0f8ff;
  border-radius: 4px;
  font-size: 13px;
  color: rgba(0, 0, 0, .65);
  margin: 16px 0 24px;
  padding: 12px 24px 14px 20px;

  .head {
    font-weight: 700;
    margin-bottom: 8px;
  }

  p {
    line-height: 20px;
    margin-bottom: 0;
  }
}

.account-list {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  margin-top: 20px;

  .item {
    width: 255px;
    height: 90px;
    background: #fbfbfb;
    border-radius: 4px;
    border: 1px solid #eee;
    padding: 17px 19px 17px 12px;
    margin: 0 20px 24px 0;
    display: flex;
    align-items: center;

    .avatar {
      margin-right: 16px;

      img {
        width: 57px;
        height: 57px;
        border-radius: 28.5px;
      }
    }

    .info {
      .name {
        font-size: 15px;
        color: #333;
        line-height: 21px;
        margin-bottom: 8px;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .type {
        font-size: 13px;
        color: rgba(0, 0, 0, .65);
        line-height: 18px;
      }
    }
  }

  .add {
    width: 255px;
    height: 90px;
    background: #fff;
    border-radius: 4px;
    border: 1px dashed #eee;
    padding: 17px 19px 17px 12px;
    margin: 0 20px 24px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;

    &:hover {
      border: 1px dashed #1890ff;
    }

    span {
      margin-left: 16px;
    }
  }
}
</style>
