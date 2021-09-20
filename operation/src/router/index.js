import Vue from 'vue'
import VueRouter from 'vue-router'

import index from '../views/index/index'
//任务宝
import workFissionIndex from '../views/workFission/index'
import speed from '../views/workFission/speed'
//抽奖活动
import lotteryIndex from '../views/lottery/index'
import explain from "@/views/lottery/explain";
//群打卡
import roomClockIndex from "@/views/roomClockIn/index";
//门店活码
import shopCodeIndex from "@/views/shopCode/shopCodeIndex";
//群裂变
import fissionIndex from "@/views/roomFission/fissionIndex";
import fissionSpeed from "@/views/roomFission/fissionSpeed";
//无限拉群
import roomInfinitePull from "@/views/roomInfinitePull/roomInfinitePull"
//互动雷达
import radar from "@/views/radar/radar"


Vue.use(VueRouter)

const routes = [
  //任务宝
  {
    path: '/',
    component: index
  },
  {
    path: '/workFission',
    name: 'workFissionIndex',
    component: workFissionIndex
  },
  {
    path: '/speed',
    component: speed
  },
  //抽奖活动
  {
    path: '/lottery',
    name: 'lotteryIndex',
    component: lotteryIndex
  },
  //抽奖活动——规则说明
  {
    path: '/explain',
    component: explain
  },
  //  互动雷达
  {
    path: '/radar',
    name: 'radar',
    component: radar
  },
  //群打卡
  {
    path: '/roomClockIn',
    name: '/roomClockIn',
    component: roomClockIndex
  },
  //门店活码
  {
    path: '/shopCode',
    name: 'shopCodeIndex',
    component: shopCodeIndex
  },
  //群裂变
  {
    path: '/roomFission',
    component: fissionIndex
  },

//群裂变——助力进度
  {
    path: '/fissionSpeed',
    component: fissionSpeed
  },
  //无限拉群
  {
    path: '/roomInfinitePull',
    component: roomInfinitePull
  }
]
const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes
})

export default router
