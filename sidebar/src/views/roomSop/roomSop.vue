<template>
  <div class="big-box">
    <div class="color">
      <div class="tips-box">
        <span>
          管理员 {{ sopInfoData.creator }} 创建了群推送任务，提醒你给群聊发送消息
        </span>
      </div>
      <div class="show">
        <div class="title">
          推送详情
        </div>
        <div class="tip">
          <div class="tip-box">
            <span>
              <van-icon name="bell" color="#1890ff"/>
              管理员提醒你在 <span>[{{ sopInfoData.time }}]</span> 给以下客户发送消息
            </span>
          </div>
        </div>

        <div class="content-box">
          <div class="content">
            <div class="content-title">
              <div class="content-contact">
                <div class="name-box">
                  <div class="name">
                    {{ sopInfoData.room.name }}
                  </div>
                  <div class="time">
                    创建时间：{{ sopInfoData.room.createTime }}
                  </div>
                </div>
                <div class="button">
                  <van-button
                    color="#c8e9ff"
                    style="width: 80px;height: 26px;color: #1b8cff;border: 1px solid #5eacff;margin-left: 20px"
                    @click="openGroup"
                  >打开</van-button>
                </div>
              </div>
            </div>
            <div class="content-text" v-for="(item,index) in sopInfoData.task.content" :key="index">
              <div class="left" v-if="item.type=='text'">
                {{ item.value }}
              </div>
              <img :src="item.value" alt="" v-else>
              <div class="button">
                <van-button @click="copyLink(item.value)" color="#c8e9ff" style="width: 80px;height: 26px;color: #1989fa;border: 1px solid #5eacff;margin-left: 20px" v-if="item.type=='text'">复制</van-button>
                <div class="open_btn_style" v-else>复制</div>
              </div>
            </div>
            <div class="complete">
              <van-button type="primary" @click="completeBtn" :disabled="sopInfoData.state!=0">我已完成</van-button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <input type="text" class="copy-input" ref="copyInput">
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { getSopInfoApi, logStateApi } from '@/api/roomSop'
// eslint-disable-next-line no-unused-vars
import { openExistedChatWithMsg } from '@/utils/wxCodeAuth'
import { Toast } from 'vant'
export default {
  components: {
    Toast
  },
  data () {
    return {
      sopInfoData: {}
    }
  },
  created () {
    this.id = this.$route.query.id
    this.getSopInfoData(this.id)
  },
  methods: {
    copyLink (value) {
      Toast('复制成功')
      const inputElement = this.$refs.copyInput
      inputElement.value = value
      inputElement.select()
      document.execCommand('Copy')
    },
    getSopInfoData (id) {
      getSopInfoApi({ id }).then((res) => {
        this.sopInfoData = res.data
      })
    },
    //  完成按钮
    completeBtn () {
      logStateApi({
        id: this.id
      }).then((res) => {
        this.getSopInfoData(this.id)
      })
    },
    //  打开群聊
    async openGroup () {
      await openExistedChatWithMsg(this.sopInfoData.room.wxChatId, '')
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
        margin-left: 4px;
        color: #333333;

        span{
          color: #1989fa;
        }
      }
    }

    .tip-box{
      width: 652px;
      display: flex;
      margin-top: 30px;
      font-size: 22px;
      height: 70px;
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
      display: flex;
      align-items: center;

      span{
        font-size: 30px;
        margin-left:  200px;
        color: #656565;
      }
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

      .content-title {
        width: 626px;
        font-size: 30px;
        height: 130px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #efefef;
        background: #ffffff;
        padding-left: 24px;
      }

      .content-input {
        display: flex;
        align-items: center;
        margin-left: 20px;
        height: 100px;

        .input {
          input {
            width: 450px;
            border: 1px solid #e8e8e8;
            height: 62px;
          }
        }
      }
      .button{
        height: 40px;
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
            font-size: 16px;
            color: #a7a7a7;
          }
        }
        .time{
          margin-top: 8px;
          font-size: 22px;
          color: #848484;
        }
        .button{
          margin-left: 20px;
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

  .content-text{
    min-height: 100px;
    margin-top: 20px;
    display: flex;
    justify-content:space-between;
    border-bottom: 1px solid #efefef;
    font-size: 22px;
    padding-bottom: 20px;
    img{
      width: 157px;
      height: 157px;
      margin-left: 30px;
    }
    .left{
      min-height: 80px;
      width: 498px;
      margin-left: 42px;
    }
    .open_btn_style{
      width: 140px;
      height: 49px;
      font-size: 25px;
      background: #d5d5d5;
      text-align: center;
      color: #fff;
      line-height: 49px;
    }
    .button{
      width: 200px;
    }
  }

  .complete{
    height: 90px;
    button{
      width: 180px;
      height: 57px;
      color: #ffffff;
      float: right;
      margin-right: 25px;
      margin-top: 15px;
    }
  }
}
</style>
