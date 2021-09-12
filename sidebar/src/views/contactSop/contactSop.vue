<template>
  <div class="big-box">
    <div class="color">
      <div class="tips-box">
        <span>
          管理员 {{ contactSopData.creator }} 创建了个人SOP任务，提醒你给已添加企业微信 {{ contactSopData.time }} 的新客户发送消息
        </span>
      </div>
      <div class="show">
        <div class="title">
          推送详情
        </div>
        <div class="tip">
          <div class="tips-box">
            <span>
              <van-icon name="bell" color="#1890ff"/>
              管理员提醒你在 <span style="color: #1890FF;">[{{ contactSopData.tipTime }}]</span>  给以下客户发送消息
            </span>
          </div>
        </div>
        <div class="content-box">
          <div class="content">
            <div class="content-title">
              推送内容
            </div>
            <div class="content-input" v-for="(item,index) in contactSopData.task.content" :key="index">
              <div class="input" v-if="item.type=='text'">{{ item.value }}</div>
              <img :src="item.value" alt="" v-else />
              <div class="button">
                <van-button @click="copyLink(item.value)" color="#c8e9ff" style="width: 80px;height: 34px;color: #1989fa;border: 1px solid #5eacff" v-if="item.type=='text'">复制</van-button>
                <div class="img_div" v-else>复制</div>
              </div>
            </div>
          </div>
        </div>
        <div class="content-box">
          <div class="content">
            <div class="content-title">
              选择客户进行任务跟进
            </div>
            <div class="content-contact">
              <div class="head">
                <img :src="contactSopData.contact.avatar" alt="" />
              </div>
              <div class="name-box">
                <div class="name">
                  {{ contactSopData.contact.name }}   <span>@微信</span>
                </div>
                <div class="time">
                  添加时间：{{ contactSopData.contact.updatedAt }}
                </div>
              </div>
              <div class="button">
                <van-button
                  color="#c8e9ff"
                  style="width: 80px;height: 34px;color: #1989fa;border: 1px solid #5eacff;margin-right: 15px;"
                  @click="followUpBtn"
                >跟进</van-button>
              </div>
            </div>
          </div>
        </div>
        <div class="tips-bottom">没有更多用户了~</div>
      </div>
    </div>
    <input type="text" class="copy-input" ref="copyInput">
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { getSopInfoApi } from '@/api/contactSop'
// eslint-disable-next-line no-unused-vars
import { openUserProfile } from '@/utils/wxCodeAuth'
import { Toast } from 'vant'
export default {
  components: {
    Toast
  },
  data () {
    return {
      contactSopData: {}
    }
  },
  created () {
    this.id = this.$route.query.id
    this.getPersonalSop(this.id)
  },
  methods: {
    // 初始化内容
    getPersonalSop (id) {
      console.log(id)
      getSopInfoApi({ id }).then((res) => {
        this.contactSopData = res.data
      })
    },
    async followUpBtn () {
      const userid = this.contactSopData.contact.wxExternalUserid
      await openUserProfile(2, userid)
    },
    // 复制
    copyLink (value) {
      Toast('复制成功')
      const inputElement = this.$refs.copyInput
      inputElement.value = value
      inputElement.select()
      document.execCommand('Copy')
    }
  }
}
</script>

<style scoped lang="less">
.copy-input{
  position: absolute;
  top: 0;
  left: 0;
  opacity: 0;
  z-index: -10;
}
.big-box{
  width: 100vw;
  min-height: 100vh;
  background: #f6f6f6;
  display: flex;
  justify-content: center;

  .color {
    width: 700px;
    height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    background-color: #ffffff;

    .tips-box{
      width: 652px;
      display: flex;
      margin-top: 30px;
      font-size: 22px;
      height: 110px;
      align-items: center;
      background-color: #f7fbff;
      border: 1px solid #cce9ff;
      justify-content: center;

      span {
        width: 620px;
        margin-left: 10px;
        color: #333333;

        span{
          color: #1989fa;
        }
      }
    }
  }

  .show {
    margin-top: 30px;
    width: 700px;

    .title{
      font-size: 34px;
      margin-left: 22px;
      padding-left: 10px;
      border-left: 8px solid #1890ff;
    }
    .tip{
      display: flex;
      width: 700px;
      align-items: center;
      justify-content: center;
    }
  }

  .content-box{
    display: flex;
    justify-content: center;
    margin-top: 40px;
    .content {
      width: 652px;
      box-shadow: 0 0 10px #dcdcdc;
      background: #fbfbfb;
      padding-bottom: 30px;
      .content-title {
        width: 626px;
        font-size: 30px;
        height: 75px;
        display: flex;
        align-items: center;
        background: #ffffff;
        padding-left: 24px;
      }

      .content-input {
        display: flex;
        justify-content:space-between;
        align-items: center;
        margin-left: 20px;
        margin-right: 20px;
        min-height: 100px;
        .input {
          margin-top: -10px;
          width: 435px;
          background: #fff;
          border-radius: 5px;
          border: 1px solid #e8e8e8;
          //min-height: 62px;
          padding-top: 15px;
          padding-bottom: 15px;
          font-size: 25px;
          padding-left: 15px;
        }
        img{
          width: 225px;
          height: 225px;
        }
      }
      .button{
        height: 130px;
        margin-left: 20px;
        .img_div{
          width: 120px;
          height: 57px;
          color: #5eacff;
          line-height: 57px;
          text-align: center;
          border: 1px solid #e3e9eD;
          background: #F3F9FD;
          font-size: 28px;
        }
      }
    }
    .content-contact{
      display: flex;
      align-items: center;
      height: 130px;

      .head{
        margin-left: 22px;
        margin-top: 18px;
        img{
          width: 90px;
          height: 90px;
        }
      }

      .name-box{
        margin-left: 16px;
        .name{
          font-size: 22px;
          span{
            color: #67ca67;
          }
        }
        .time{
          margin-top: 8px;
          font-size: 22px;
        }
      }
    }

  }

  .tips-bottom{
    display: flex;
    margin-top: 24px;
    justify-content: center;
    font-size: 34px;
    color: #727272;

  }
}
</style>
