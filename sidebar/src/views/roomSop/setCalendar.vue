<template>
  <div>
    <Popup v-model:show="show" :closeable="true" :close-on-click-overlay="false">
      <div style="text-align: center;margin-top: 15px;font-size: 20px;">设置群日历</div>
      <div style="padding: 20px 20px;">
        <NoticeBar
          color="#1989fa"
          background="#ecf9ff"
          left-icon="info-o"
          :scrollable="false"
          :wrapable="true"
          style="font-size: 12px;">
          可将群聊添加至企业创建的群日历中，添加后将按照该日历内的推送规则，自动提醒群主给群聊发消息
        </NoticeBar>
        <div style="display: flex;">
          <div style="font-size: 15px;margin-top: 20px;">请选择群日历：</div>
          <div class="option_box">
            <select @change="switchGroup" v-model="ruleName">
              <option v-for="(item,index) in listRuleData" :key="index" :value="item.name">{{ item.name }}</option>
            </select>
          </div>
        </div>
        <div class="pop_foot">
          <van-button type="default" size="small" style="width: 57px;margin-right: 10px;" @click="cancelBtn">取消</van-button>
          <van-button type="primary" size="small" style="width: 57px;" @click="confirmBtn">确定</van-button>
        </div>
      </div>
    </Popup>
  </div>
</template>
<script>
import { Popup, NoticeBar, Popover, Toast } from 'vant'
// eslint-disable-next-line no-unused-vars
import { roomCalendarApi, setRoomCalendarApi } from '@/api/roomCalendar'
export default {
  components: {
    Popup,
    Popover,
    NoticeBar
  },
  data () {
    return {
      show: false,
      listRuleData: [],
      ruleName: ''
    }
  },
  methods: {
    cancelBtn () {
      this.show = false
    },
    switchGroup (e) {
      this.listRuleData.forEach((item) => {
        if (item.name == this.ruleName) {
          this.showRule = item
        }
      })
    },
    showPopup (roomId) {
      this.show = true
      this.roomId = roomId
      this.getCalendarlist()
    },
    // 确定
    confirmBtn () {
      const parmas = {
        id: this.showRule.id,
        roomId: this.roomId
      }
      setRoomCalendarApi(parmas).then((res) => {
        Toast({ position: 'top', message: '设置成功' })
        this.$emit('change', true)
        this.show = false
      })
    },
    //  获取群日历列表
    getCalendarlist () {
      roomCalendarApi({
      }).then((res) => {
        console.log(res)
        this.listRuleData = res.data
      })
    }
  }
}
</script>
<style scoped lang="less">
.option_box{
  width: 270px;
  height: 50px;
  border: 1px solid #cccccc;
  margin-top: 30px;
}
.option_box select{
  /*清除select的边框样式*/
  border: none;
  /*清除select聚焦时候的边框颜色*/
  outline: none;
  /*将select的宽高等于div的宽高*/
  width: 100%;
  height: 50px;
}
.option_box option{
  height: 50px;
}
:deep(.van-popup--center){
  width: 92%;
}
.pop_foot{
  margin-top: 30px;
  display: flex;
  justify-content:flex-end;
}
.opt_term{
  width: 100%;
  //position: relative;
}
.election_calendar{
  width: 220px;
  height: 57px;
  line-height: 57px;
  padding-left: 15px;
  font-size: 15px;
  margin-top: 30px;
  border: 1px solid #E8E8E8;
  cursor: pointer;
}
</style>
