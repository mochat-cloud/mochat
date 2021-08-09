import Vue from 'vue'
import VueRouter from 'vue-router'

import index from '../views/index/index'
//任务宝
import workFissionIndex from '../views/workFission/index'
import speed from '../views/workFission/speed'
//抽奖活动
import lotteryIndex from '../views/lottery/lotteryIndex'
import explain from "@/views/lottery/explain";
//群打卡
import success from "@/views/roomClockIn/success";
import roomClockIndex from "@/views/roomClockIn/roomClockIndex";
import roomClockInExplain from "@/views/roomClockIn/roomClockInExplain"
//门店活码
import employeeIndex from "@/views/codeShop/employeeIndex";
//群裂变
import fissionIndex from "@/views/groupFission/fissionIndex";
import fissionSpeed from "@/views/groupFission/fissionSpeed";
//无限拉群
import infiniteGroup from "@/views/infiniteGroup/infiniteGroup"
//互动雷达
import interactiveRadar from "@/views/interactiveRadar/interactiveRadar"


Vue.use(VueRouter)

const routes = [
  //任务宝
  {
    path: '/',
    component: index
  },
  {
    path: '/workFissionIndex',
    component: workFissionIndex
  },
  {
    path: '/speed',
    component: speed
  },
  //抽奖活动
  {
    path: '/lotteryIndex',
    component: lotteryIndex
  },
  //抽奖活动——规则说明
  {
    path: '/explain',
    component: explain
  },
  //  互动雷达
  {
    path: '/interactiveRadar',
    component: interactiveRadar
  },
  //群打卡
  {
    path: '/roomClockIndex',
    component: roomClockIndex
  },

  //群打卡——规则说明
  {
    path: '/roomClockInExplain',
    component: roomClockInExplain
  },

//门店活码
  {
    path: '/employeeIndex',
    component: employeeIndex
  },
//群裂变
  {
    path: '/fissionIndex',
    component: fissionIndex
  },

//群裂变——助力进度
  {
    path: '/fissionSpeed',
    component: fissionSpeed
  },
  //无限拉群
  {
    path: '/infiniteGroup',
    component: infiniteGroup
  }
]
const router = new VueRouter({
  mode: 'hash',
  base: process.env.BASE_URL,
  routes
})

export default router
