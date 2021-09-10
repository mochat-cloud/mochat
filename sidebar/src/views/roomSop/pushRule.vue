<template>
  <div style="background: #fff;">
    <Popup v-model:show="show" :closeable="true" :close-on-click-overlay="false">
      <div style="text-align: center;margin-top: 15px;font-size: 25px;">修改推送规则</div>
      <div style="padding: 20px 20px;">
        <div class="push_tips">选择推送规则后将会按照以下规则发送推送提醒<a href="" style="color: #1890FF">查看详情</a></div>
        <div class="opt_rule">
          <div style="line-height: 30px;">选择推送规则：</div>
          <div class="option_box">
            <select @change="switchGroup" v-model="ruleName">
              <option v-for="(item,index) in listRuleData" :key="index" :value="item.name">{{ item.name }}</option>
            </select>
          </div>
        </div>
        <div class="rule_title">设置当前所在群聊</div>
        <div class="" style="font-size: 20px;">
          <Steps direction="vertical" :active="0" active-color="#000000">
            <Step v-for="(item,index) in showRule.setting" :key="index">
              <div>加入规则当天，
                {{ item.time.data.first }}<span v-if="item.time.type==0">小时</span><span v-else>天</span>{{ item.time.data.last }}<span v-if="item.time.type==0">分</span>
                后提醒发送</div>
              <div class="tags_box">
                <div>
                  <div v-for="(itm,idx) in item.content" :key="idx">
                    <div class="show_rule" v-if="itm.type=='text'"> {{ itm.value }}</div>
                    <img :src="itm.value" alt="" v-else style="width: 50px;height: 50px;">
                  </div>
                </div>
                <div class="rule_num">共1条</div>
              </div>
            </Step>
          </Steps>
        </div>
        <div class="pop_foot">
          <div style="color: #1890FF;font-size: 13px;line-height: 10px;line-height: 38px;cursor: pointer;" @click="removeGroup">将群聊移除规则</div>
          <div>
            <van-button type="default" size="small" style="width: 57px;margin-right: 10px;" @click="cancelBtn">取消</van-button>
            <van-button type="primary" size="small" style="width: 57px;" @click="confirmBtn">确定</van-button>
          </div>
        </div>
      </div>
    </Popup>

  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { tipSopListApi, tipSopAddRoomApi, tipSopDelRoomApi } from '@/api/roomSop'
import { Step, Steps, Popup, Popover, Toast } from 'vant'
export default {
  components: {
    Step,
    Steps,
    Popover,
    Popup,
    Toast
  },
  data () {
    return {
      show: false,
      listRuleData: {},
      showRule: {},
      ruleName: ''
    }
  },
  created () {
    // this.getRuleList()
  },
  methods: {
    showPopup (roomId, sopName) {
      if (!sopName) {
        return
      }
      this.show = true
      this.sopName = sopName
      this.roomId = roomId
      this.getRuleList()
    },
    // 移除群聊
    removeGroup () {
      const params = {
        id: this.showRule.id,
        roomId: this.roomId
      }
      tipSopDelRoomApi(params).then((res) => {
        Toast({ position: 'top', message: '移除成功' })
        this.$emit('change', true)
        this.show = false
      })
    },
    // 确定
    confirmBtn () {
      tipSopAddRoomApi({
        id: this.showRule.id,
        roomId: this.roomId
      }).then((res) => {
        Toast({ position: 'top', message: '设置成功' })
        this.$emit('change', true)
        this.show = false
      })
    },
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
    getRuleList () {
      tipSopListApi({
      }).then((res) => {
        this.listRuleData = res.data
        this.listRuleData.forEach((item) => {
          if (item.name == this.sopName.name) {
            this.showRule = item
            this.ruleName = item.name
          }
        })
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
.rule_name_style{
  width: 230px;
  height: 57px;
  line-height: 57px;
  padding-left: 10px;
  border: 1px solid #d7d7d7;
}
.push_tips{
  font-size: 25px;
  color: #878787;
}
.opt_rule{
  display: flex;
  margin-top: 20px;
  font-size: 28px;
  color: #878787;
}
.rule_title{
  margin-top: 20px;
  border-left: 8px solid #1890FF;
  padding-left: 10px;
  font-size: 28px;
  font-weight: 700;
}
:deep(.van-popup--center){
  width: 92%;
}
.tags_box{
  width: 100%;
  min-height: 100px;
  background: #eee;
  padding-top: 10px;
  padding-bottom: 20px;
  margin-top: 10px;
}
.show_rule{
  margin-left: 25px;
  width: 90%;
  margin-top: 15px;
  min-height: 50px;
  line-height: 50px;
  background: #fff;
  padding-left: 10px;
}
.rule_num{
  margin-left: 15px;
  margin-top: 30px;
}
.pop_foot{
  margin-top: 10px;
  display: flex;
  justify-content:space-between;
}
</style>
