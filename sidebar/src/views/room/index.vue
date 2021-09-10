<template>
  <div>
    <div class="group_box">
      <div class="group_title">群管理</div>
      <div class="group_row">
        <div class="name">
          群sop： <span v-if="setGroupName.sop.length==0">未设置</span>
          <template v-else>
            <span v-for="(item,index) in setGroupName.sop" :key="index">{{ item.name }}</span>
          </template>
        </div>
        <div class="set_btn" @click="setGroupSop">设置</div>
      </div>
      <div class="group_row">
        <div class="name">群日历：<span v-if="setGroupName.calendar.length==0">未设置</span>
          <template v-else>
            <span v-for="(item,index) in setGroupName.calendar" :key="index">{{ item.name }}</span>
          </template>
        </div>
        <div class="set_btn" @click="clickSetCalendar">设置</div>
      </div>
      <div class="group_row">
        <div class="name">群聊质检：<span v-if="setGroupName.quality.length==0">未设置</span>
          <template v-else>
            <span v-for="(item,index) in setGroupName.quality" :key="index">{{ item.name }}</span>
          </template>
        </div>
        <div class="set_btn" @click="setGroupInfo">设置</div>
      </div>
      <pushRule ref="pushRule" @change="handleRule" />
      <setCalendar ref="setCalendar" @change="handleCalendar" />
      <roomQuality ref="roomQuality" @change="handleGroupTest" />
    </div>
  </div>
</template>
<script>
import pushRule from '@/views/roomSop/pushRule'
import setCalendar from '@/views/roomSop/setCalendar'
import roomQuality from '@/views/roomSop/roomQuality'
// eslint-disable-next-line no-unused-vars
import { getCurExternalChat, getContext } from '@/utils/wxCodeAuth'
// eslint-disable-next-line no-unused-vars
import { roomManageApi } from '@/api/room'
export default {
  components: {
    pushRule,
    setCalendar,
    roomQuality
  },
  data () {
    return {
      setGroupName: {
        top: [],
        calendar: [],
        quality: []
      }
    }
  },
  async created () {
    const entry = await getContext()
    if (entry !== 'group_chat_tools') {
      this.$toast({ position: 'top', message: '请从群聊会话的工具栏进入' })
      return
    }

    this.getGroupId()
    // this.getGroupSetData()
  },
  methods: {
    // 设置刷新群sop
    handleRule (e) {
      if (e) {
        this.getGroupSetData()
      }
    },
    // 设置刷新群日历
    handleCalendar (e) {
      if (e) {
        this.getGroupSetData()
      }
    },
    // 设置群聊质检
    handleGroupTest (e) {
      if (e) {
        this.getGroupSetData()
      }
    },
    // 获取群聊
    async getGroupId () {
      this.groupId = await getCurExternalChat()
      this.getGroupSetData()
    },
    // 获取群聊设置信息
    getGroupSetData () {
      const params = {
        roomId: this.groupId
      }
      roomManageApi(params).then((res) => {
        this.setGroupName = res.data
      })
    },
    // 设置群sop
    setGroupSop () {
      this.$refs.pushRule.showPopup(this.groupId, this.setGroupName.sop)
    },
    // 设置群日历
    clickSetCalendar () {
      this.$refs.setCalendar.showPopup(this.groupId)
    },
    //  设置群聊质检
    setGroupInfo () {
      this.$refs.roomQuality.showPopup(this.groupId)
    }
  }
}
</script>
<style lang="less">
.group_box{
  background: #fff;
  padding: 30px 50px;
}
.group_title{
  font-size: 35px;
  font-weight: bold;
  border-left: 8px solid #1890FF;
  padding-left: 15px;
}
.group_row{
  display: flex;
  justify-content:space-between;
  font-size: 30px;
  margin-top: 30px;
  .name{
    color: #8F8F8F;
    width: calc(100% - 90px);
    span{
      margin-left: 25px;
    }
    span:first-child{
      margin-left: 0px;
    }
  }
}
.set_btn{
  color: #1990FF;
  width: 75px;
  cursor: pointer;
}
</style>
