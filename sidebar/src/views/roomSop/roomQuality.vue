<template>
  <div>
    <Popup v-model:show="show" :closeable="true" :close-on-click-overlay="false" @close="closePop">
      <div style="text-align: center;margin-top: 15px;font-size: 20px;">设置群聊质检</div>
      <div style="padding: 20px 20px;">
        <NoticeBar
          color="#1989fa"
          background="#ecf9ff"
          left-icon="info-o"
          :scrollable="false"
          :wrapable="true"
          style="font-size: 12px;">
          设置质检规则后，该群若在规定时间内未恢复客户消息，MoChat助手将给选定的管理员发送提醒
        </NoticeBar>
        <div style="display: flex;">
          <div style="font-size: 15px;margin-top: 20px;">添加质检规则：</div>
          <div class="option_box">
            <select @change="switchGroup" v-model="ruleName">
              <option
                v-for="(item,index) in listRuleData"
                :key="index"
                :value="item.name"
                :disabled="ruleNameList.indexOf(item.name)!=-1"
              >{{ item.name }}</option>
            </select>
          </div>
        </div>
        <div class="rule_box">
          <div class="rule_list" v-if="ruleListData.length!=0" v-for="(item,index) in ruleListData" :key="index">
            <div class="rule_name">{{ item.name }}</div>
            <div class="rule_cont">
              <div class="rule_row" v-for="(obj,idx) in item.rule" :key="idx">
                <div class="">规则{{ idx+1 }}：</div>
                <div class="content">为超过
                  <span>
                    {{ obj.num }}
                    <span v-if="obj.time_type==1">分钟</span>
                    <span v-if="obj.time_type==2">小时</span>
                    <span v-if="obj.time_type==3">天</span>
                  </span> 未回复客户消息的群聊给管理员 <span v-for="(m,i) in obj.showEmployee" :key="i">{{ m.name }}</span> 发送MoChat提醒行为 </div>
              </div>
            </div>
          </div>
        </div>
        <div class="del_row" v-if="ruleListData.length!=0"><span @click="delRuleCont">将群聊移除规则</span></div>
        <div class="pop_foot">
          <van-button type="default" size="small" style="width: 57px;margin-right: 10px;" @click="cancelBtn">取消</van-button>
          <van-button type="primary" size="small" style="width: 57px;" @click="confirmBtn">确定</van-button>
        </div>
      </div>
    </Popup>
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { Popup, NoticeBar, Popover, Toast } from 'vant'
// eslint-disable-next-line no-unused-vars
import { roomQualityApi, setRoomQualityApi, delRoomQualityApi } from '@/api/roomQuality'
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
      ruleName: '',
      ruleListData: [],
      ruleNameList: []
    }
  },
  methods: {
    // 关闭
    closePop () {
      this.$emit('change', true)
      this.show = false
    },
    // 取消
    cancelBtn () {
      this.$emit('change', true)
      this.show = false
    },
    delRuleCont () {
      const lastIndex = this.ruleListData.length - 1
      this.listRuleData.forEach((item) => {
        if (item.name == this.ruleListData[lastIndex].name) {
          delRoomQualityApi({
            id: item.id,
            roomId: this.roomId
          }).then((res) => {
            Toast({ position: 'top', message: '删除成功' })
            this.getGroupQuality()
          })
        }
      })
    },
    switchGroup (e) {
      this.listRuleData.forEach((item) => {
        if (item.name == this.ruleName) {
          // console.log(item)
          setRoomQualityApi({
            id: item.id,
            roomId: this.roomId
          }).then((res) => {
            Toast({ position: 'top', message: '添加成功' })
            this.getGroupQuality()
          })
        }
      })
    },
    confirmBtn () {
      this.$emit('change', true)
      this.show = false
    },
    showPopup (roomId) {
      this.show = true
      this.roomId = roomId
      this.getGroupQuality()
    },
    //  获取群聊质检列表
    getGroupQuality () {
      roomQualityApi({
        roomId: this.roomId
      }).then((res) => {
        this.listRuleData = res.data.list
        this.ruleListData = res.data.roomQuality
        this.ruleNameList = []
        this.ruleListData.forEach((item, index) => {
          this.ruleNameList[index] = item.name
        })
      })
    }
  }
}
</script>
<style scoped lang="less">
.rule_box{
  width: 100%;
  height: 460px;
  margin-top: 40px;
  overflow-y:scroll;
}
.rule_list{
  margin-top: 40px;
}
.rule_list:first-child{
  margin-top: 0px;
}
.rule_name{
  font-size: 35px;
  font-weight: bold;
  border-left: 8px solid #1890FF;
  height: 35px;
  line-height: 40px;
  padding-left: 20px;
}
.rule_row{
  font-size: 32px;
  padding:27px;
  margin-top: 20px;
  border: 1px solid #e2e2e2;
  background: #FBFBFB;
  .content{
    color: #858585;
    span{
      font-size: 30px;
      font-weight: bold;
      color: #000;
    }
  }
}
.del_row{
  font-size: 30px;
  color: #1890FF;
  margin-top: 30px;
  span{
    cursor: pointer;
  }
}
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
