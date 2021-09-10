<template>
  <div>
    <div class="tips_pop" v-if="showPopup">
      <div class="close_icon"><van-icon name="cross" @click="closeIcon" /></div>
      <div class="tips_head">
        <div class="left">
          <van-icon name="bell" color="#1890ff"/>
          <span>个人SOP提醒</span>
        </div>
        <div class="right" @click="seeTotal">查看全部</div>
      </div>
      <div class="tip_content">
        <div class="tips_cont" v-for="(item,index) in sopTipInfoData" :key="index">
          <div class="title">管理员提醒你在今日 <span style="color: #188EFD;">「{{ item.tipTime }}」</span> 给客户发送以下消息</div>
          <div class="news_row">
            <div v-for="(obj,idx) in item.task.content" :key="idx">
              <div class="news_cont" v-if="obj.type=='text'">{{ obj.value }}</div>
              <img :src="obj.value" alt="" v-else style="width: 50px;height: 50px;margin-bottom: 10px;">
            </div>
            <!--            <div class="news_cont">而非仍然发布</div>-->
          </div>
        </div>
        <Loading color="#1989fa" class="load_img" v-show="showLoad" />
      </div>
      <div style="margin-left: 10px;color: #B9BBBA;">点击 <span style="color: #188EFD;cursor: pointer;">「暂不发送」</span>弹窗将缩起，可随时再展开发送消息</div>
      <div class="foot_pop">
        <div @click="noSentout">暂不发送</div>
        <div class="send_btn" @click="sendOut">发送</div>
      </div>
    </div>
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { getSopTipInfoApi } from '@/api/contactSop'
// eslint-disable-next-line no-unused-vars
import { Loading } from 'vant'
export default {
  components: {
    Loading
  },
  data () {
    return {
      showPopup: true,
      sopTipInfoData: {},
      showLoad: false,
      //  全部数据
      totalSopData: []
    }
  },
  methods: {
    seeTotal () {
      this.showLoad = true
      setTimeout(() => {
        this.sopTipInfoData = this.totalSopData
        this.showLoad = false
      }, 1000)
    },
    show (contactId) {
      this.showPopup = true
      this.contactId = contactId
      this.getCorpData()
    },
    noSentout () {
      this.showPopup = false
    },
    sendOut () {

    },
    closeIcon () {
      this.showPopup = false
    },
    getCorpData () {
      this.sopTipInfoData = []
      const params = {
        contactId: this.contactId
      }
      getSopTipInfoApi(params).then((res) => {
        this.totalSopData = res.data
        this.sopTipInfoData.push(this.totalSopData[0])
      })
    }
  }
}
</script>
<style scoped lang="less">
.tip_content{
  width: 100%;
  min-height: 500px;
  overflow-y:scroll;
  position: relative;
  padding-bottom: 50px;
}
.load_img{
  position: absolute;
  bottom: 30px;
  left: 50%;
}
.close_icon{
  font-size: 35px;
  text-align: right;
  padding-top: 10px;
  i{
    margin-right: 20px;
    cursor: pointer;
  }
}
  .tips_pop{
    border: 4px solid #9BBEDC;
    border-radius: 10px;
    background: #fff;
    margin-left: 20px;
    margin-right: 20px;
    margin-top: 20px;
    font-size: 25px;
    min-height: 570px;
    padding-bottom: 20px;
  }
  .tips_head{
    display: flex;
    justify-content:space-between;
    padding: 20px 20px;
    border-bottom: 3px solid #EEECED;
    .right{
      color: #188EFD;
      cursor: pointer;
      span{
        color: #A5A5A5;
      }
    }
  }
  .tips_cont{
    padding: 20px 20px;
    .title{
      height: 30px;
      line-height: 32px;
      border-left: 7px solid #1890FF;
      padding-left: 10px;
    }
  }
  .news_row{
    border: 1px solid #EAE8E9;
    margin-top: 35px;
    padding: 20px 20px;
  }
  .news_cont{
    border: 1px solid #EAE8E9;
    padding: 20px 20px;
    margin-bottom: 20px;
  }
  .foot_pop{
    display: flex;
    justify-content:flex-end;
    margin-top: 50px;
    div{
      width: 157px;
      height: 57px;
      cursor: pointer;
      text-align: center;
      line-height: 57px;
      background: #EDF7FC;
      border: 3px solid #1890FE;
      margin-right: 20px;
      color: #1890FE;
    }
    .send_btn{
      background: #1890FE;
      color: #fff;
    }
  }
</style>
