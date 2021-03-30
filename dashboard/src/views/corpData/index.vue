<template>
  <div class="wrapper">
    <div class="left">
      <a-card>
        <div class="data-box">
          <p>总数据，本月数据，今日数据 每10分钟更新</p>
          <div class="data">
            <div>
              <img :src="require('@/assets/systemHomePage/customer-icon.png')" alt="">
              <div class="content">
                <span>{{ weChatContactNum }}</span>
                <div>
                  <span>总微信客户数</span>
                  <img :src="require('@/assets/systemHomePage/search-icon.png')" alt="">
                </div>
              </div>
            </div>
            <div>
              <img :src="require('@/assets/systemHomePage/WechatGroup-icon.png')" alt="">
              <div class="content">
                <span>{{ weChatRoomNum }}</span>
                <div>
                  <span>总微信群数</span>
                  <img :src="require('@/assets/systemHomePage/search-icon.png')" alt="">
                </div>
              </div>
            </div>
            <div>
              <img :src="require('@/assets/systemHomePage/memberGroup-icon.png')" alt="">
              <div class="content">
                <span>{{ roomMemberNum }}</span>
                <div>
                  <span>总群成员数</span>
                  <img :src="require('@/assets/systemHomePage/search-icon.png')" alt="">
                </div>
              </div>
            </div>
            <div>
              <img :src="require('@/assets/systemHomePage/enterpriseMembers-icon.png')" alt="">
              <div class="content">
                <span>{{ corpMemberNum }}</span>
                <div>
                  <span>总企业成员数</span>
                  <img :src="require('@/assets/systemHomePage/search-icon.png')" alt="">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="chart-box">
          <p>实施概况<span>更新时间：{{ updateTime }}</span></p>
          <div class="chart-data">
            <div>
              <span class="title">新增客户数</span>
              <span class="num">{{ addContactNum }}</span>
              <span class="old">昨日：{{ lastAddContactNum }}</span>
            </div>
            <div>
              <span class="title">新增入群数</span>
              <span class="num">{{ addIntoRoomNum }}</span>
              <span class="old">昨日：{{ lastAddIntoRoomNum }}</span>
            </div>
            <div>
              <span class="title">流失客户数</span>
              <span class="num">{{ lossContactNum }}</span>
              <span class="old">昨日：{{ lastLossContactNum }}</span>
            </div>
            <div>
              <span class="title">退群人数</span>
              <span class="num">{{ quitRoomNum }}</span>
              <span class="old">昨日：{{ lastQuitRoomNum }}</span>
            </div>
          </div>
          <div class="chart-content" v-if="statisticsShow">
            <v-chart style="width: 100%;" :options="options" ref="chars"></v-chart>
          </div>
          <div class="card-box">
            <div class="card-one card-all">
              <div class="card-left">
                <span class="card-title">本月新增好友数<img :src="require('@/assets/systemHomePage/searchTwo-icon.png')" alt=""></span>
                <span class="card-num">{{ addFriendsNum }}</span>
                <span class="card-add">上月累计新增<img :src="require('@/assets/systemHomePage/searchTwo-icon.png')" alt=""></span>
                <span class="add-num">{{ lastAddFriendsNum }}</span>
              </div>
              <div class="card-right">
                <div><span>新增好友</span></div>
              </div>
            </div>
            <div class="card-two card-all">
              <div class="card-left">
                <span class="card-title">本月新增社群数<img :src="require('@/assets/systemHomePage/searchTwo-icon.png')" alt=""></span>
                <span class="card-num">{{ monthAddRoomNum }}</span>
                <span class="card-add">上月累计新增<img :src="require('@/assets/systemHomePage/searchTwo-icon.png')" alt=""></span>
                <span class="add-num">{{ lastMonthAddRoomNum }}</span>
              </div>
              <div class="card-right">
                <div><span>新增社群</span></div>
              </div>
            </div>
            <div class="card-three card-all">
              <div class="card-left">
                <span class="card-title">本月新增群成员<img :src="require('@/assets/systemHomePage/searchTwo-icon.png')" alt=""></span>
                <span class="card-num">{{ monthAddRoomMemberNum }}</span>
                <span class="card-add">上月累计新增<img :src="require('@/assets/systemHomePage/searchTwo-icon.png')" alt=""></span>
                <span class="add-num">{{ lastMonthAddRoomMemberNum }}</span>
              </div>
              <div class="card-right">
                <div><span>新增群成员</span></div>
              </div>
            </div>
            <div class="card-four card-all">
              <div class="card-left">
                <span class="card-title">本月流失客户数 <img :src="require('@/assets/systemHomePage/searchTwo-icon.png')" alt=""></span>
                <span class="card-num">{{ monthLossContactNum }}</span>
                <span class="card-add">上月累计新增 <img :src="require('@/assets/systemHomePage/searchTwo-icon.png')" alt=""></span>
                <span class="add-num">{{ lastMonthLossContactNum }}</span>
              </div>
              <div class="card-right">
                <div><span>流失客户</span></div>
              </div>
            </div>
          </div>
        </div>
      </a-card>
      <a-card title="常用功能" style="marginTop: 12px;">
        <div class="opt-box">
          <div class="opt-content" @click="routerPush('one')">
            <img :src="require('@/assets/systemHomePage/optBtn-one.png')" alt="">
            <div>
              <p>客户群发</p>
              <span>了解和客户群发相关的限制</span>
            </div>
          </div>
          <div class="opt-content" @click="routerPush('two')">
            <img :src="require('@/assets/systemHomePage/optBtn-two.png')" alt="">
            <div>
              <p>渠道活码</p>
              <span>了解渠道活码的功能和使用</span>
            </div>
          </div>
          <div class="opt-content" @click="routerPush('three')">
            <img :src="require('@/assets/systemHomePage/optBtn-three.png')" alt="">
            <div>
              <p>群管理</p>
              <span>统一管理企业客户群</span>
            </div>
          </div>
        </div>
      </a-card>
    </div>
    <div class="right">
      <a-card>
        <div class="QR-code">
          <p class="title">MoChat企业微信开源框架</p>
          <div class="edition-box"><span class="msg">版本信息：</span><span class="edition">{{ license.licenseType }}版</span></div>
          <span class="red">{{ license.licenseNote }}</span>
          <div class="code-img">
            <img :src="licenseContactLink" alt="">
          </div>
          <span class="code-detail">—扫描上方二维码可购买终身商业授权—</span>
        </div>
      </a-card>
      <a-card title="入门指引" style="marginTop: 13px;">
        <div class="btn-box">
          <div>
            <a :href="guide.docLink" target="_blank">
              <img :src="require('@/assets/systemHomePage/file-icon.png')" alt="">
              <span>开发文档</span>
            </a>
          </div>
          <div>
            <a :href="guide.faqLink" target="_blank">
              <img :src="require('@/assets/systemHomePage/help-icon.png')" alt="">
              <span>帮助中心</span>
            </a>
          </div>
        </div>
      </a-card>
      <a-card title="更新日志" style="marginTop: 13px">
        <div class="list" v-for="item in newsList" :key="item.id">
          <div>
            <span>{{ item.title }}</span>
            <span>{{ item.createdAt }}</span>
          </div>
        </div>
      </a-card>
    </div>
  </div>
</template>

<script>
import { corpData, lineChat, tenantIndex } from '@/api/corpData'
export default {
  data () {
    return {
      weChatContactNum: '',
      weChatRoomNum: '',
      roomMemberNum: '',
      corpMemberNum: '',
      addContactNum: '',
      lastAddContactNum: '',
      addIntoRoomNum: '',
      lastAddIntoRoomNum: '',
      lossContactNum: '',
      lastLossContactNum: '',
      quitRoomNum: '',
      lastQuitRoomNum: '',
      addFriendsNum: '',
      lastAddFriendsNum: '',
      monthAddRoomNum: '',
      lastMonthAddRoomNum: '',
      monthLossContactNum: '',
      lastMonthLossContactNum: '',
      monthAddRoomMemberNum: '',
      lastMonthAddRoomMemberNum: '',
      updateTime: '',
      statisticsShow: true,
      // 折线图
      options: {
        dataZoom: {
          type: 'inside',
          show: true,
          start: 0,
          end: 100
        },
        tooltip: {
          trigger: 'axis'
        },
        legend: {
          top: 15,
          data: ['新增客户数', '新增入群数', '流失客户', '退群人数']
        },
        grid: {
          left: '10%',
          right: '10%',
          bottom: '20%',
          width: '80%'
        },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          data: [],
          axisLabel: {
            interval: 2,
            rotate: 0,
            color: 'blue',
            margin: 20
          }
        },
        yAxis: {
          type: 'value'
        },
        series: [
          {
            name: '新增客户数',
            type: 'line',
            // stack: '总量',
            data: [],
            itemStyle: {
              normal: {
                color: '#094FFF',
                lineStyle: {
                  color: '#094FFF'
                }
              }
            }
          },
          {
            name: '新增入群数',
            type: 'line',
            // stack: '总量',
            data: [],
            itemStyle: {
              normal: {
                color: '#FF7777',
                lineStyle: {
                  color: '#FF7777'
                }
              }
            }
          },
          {
            name: '流失客户',
            type: 'line',
            // stack: '总量',
            data: [],
            itemStyle: {
              normal: {
                color: '#50C520',
                lineStyle: {
                  color: '#50C520'
                }
              }
            }
          },
          {
            name: '退群人数',
            type: 'line',
            // stack: '总量',
            data: [],
            itemStyle: {
              normal: {
                color: '#FFAC02',
                lineStyle: {
                  color: '#FFAC02'
                }
              }
            }
          }
        ]
      },
      license: {},
      licenseContactLink: '',
      newsList: [],
      guide: {}
    }
  },
  created () {
    this.getData()
    this.getLineChat()
    this.getRightData()
  },
  methods: {
    getData () {
      corpData().then(res => {
        const { addContactNum, addFriendsNum, addIntoRoomNum, corpMemberNum, lastAddContactNum, lastAddFriendsNum,
          lastAddIntoRoomNum, lastLossContactNum, lastMonthAddRoomMemberNum, lastMonthAddRoomNum,
          lastMonthLossContactNum, lastQuitRoomNum, lossContactNum, monthAddRoomMemberNum, monthAddRoomNum, monthLossContactNum,
          quitRoomNum, roomMemberNum, weChatContactNum, weChatRoomNum, updateTime } = res.data
        this.addContactNum = addContactNum || 0
        this.addFriendsNum = addFriendsNum || 0
        this.addIntoRoomNum = addIntoRoomNum || 0
        this.corpMemberNum = corpMemberNum || 0
        this.lastAddContactNum = lastAddContactNum || 0
        this.lastAddFriendsNum = lastAddFriendsNum || 0
        this.lastAddIntoRoomNum = lastAddIntoRoomNum || 0
        this.lastLossContactNum = lastLossContactNum || 0
        this.lastMonthAddRoomMemberNum = lastMonthAddRoomMemberNum || 0
        this.lastMonthAddRoomNum = lastMonthAddRoomNum || 0
        this.lastMonthLossContactNum = lastMonthLossContactNum || 0
        this.lastQuitRoomNum = lastQuitRoomNum || 0
        this.lossContactNum = lossContactNum || 0
        this.monthAddRoomMemberNum = monthAddRoomMemberNum || 0
        this.monthAddRoomNum = monthAddRoomNum || 0
        this.monthLossContactNum = monthLossContactNum || 0
        this.quitRoomNum = quitRoomNum || 0
        this.roomMemberNum = roomMemberNum || 0
        this.weChatContactNum = weChatContactNum || 0
        this.weChatRoomNum = weChatRoomNum || 0
        this.updateTime = updateTime
      })
    },
    routerPush (type) {
      if (type == 'two') {
        this.$router.push('/channelCode/index')
      } else if (type == 'three') {
        this.$router.push('/workRoom/index')
      }
    },
    getLineChat () {
      let line = []
      lineChat().then(res => {
        line = res.data
        const addContactNum = []
        const addIntoRoomNum = []
        const lossContactNum = []
        const quitRoomNum = []
        const time = []
        line.map(item => {
          time.push(item.date.substr(5, 5))
          addContactNum.push(item.addContactNum)
          addIntoRoomNum.push(item.addIntoRoomNum)
          lossContactNum.push(item.lossContactNum)
          quitRoomNum.push(item.quitRoomNum)
        })
        this.options.xAxis.data = time
        this.options.series = this.options.series.map(item => {
          if (item.name == '新增客户数') {
            item.data = addContactNum
          } else if (item.name == '新增入群数') {
            item.data = addIntoRoomNum
          } else if (item.name == '流失客户') {
            item.data = lossContactNum
          } else if (item.name == '退群人数') {
            item.data = quitRoomNum
          }
          return item
        })
        return true
      })
    },
    getRightData () {
      tenantIndex({ domain: 'mo.chat' }).then(res => {
        const { license, licenseContactLink, news, guide } = res.data
        this.newsList = news
        this.license = license
        this.licenseContactLink = licenseContactLink
        this.guide = guide
      })
    }
  }
}
</script>

<style lang="less" scoped>
.wrapper {
  display: flex;
  .left {
    width: 75%;
    margin-right: 10px;
    .data-box {
      border-bottom: 1px solid #e5e5e5;
      p {
        color: #666666;
        font-size: 12px;
        font-weight: 400;
      }
      .data {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        div {
          width: 26%;
          display: flex;
          margin-bottom: 5px;
          img {
            width: 50px;
            height: 50px;
            margin-right: 10px;
          }
          .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            span {
              font-size: 20px;
              font-weight: 600;
              color: #333333;
            }
            div {
              width: 100%;
              display: flex;
              align-items: center;
              span {
                font-size: 10px;
                font-weight: 400;
                color: #999999;
              }
              img {
                margin-left: 5px;
                width: 10px;
                height: 10px;
              }
            }
          }
        }
      }
    }
    .chart-box {
      width: 100%;
      display: flex;
      flex-direction: column;
      padding: 10px;
      p{
        width: 100%;
        color: #333333;
        font-size: 12px;
        text-align: center;
        span {
          color: #999999;
          font-size: 10px;
          margin-left: 5px;
        }
      }
      .chart-data {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        div {
          width: 13%;
          height: 90px;
          padding: 10px;
          margin: 10px;
          display: flex;
          flex-direction: column;
          border-radius: 5px;
          box-shadow: 0px 4px 10px 2px rgba(153,153,153,0.20);
          .title {
            font-size: 10px;
            color: #999999;
          }
          .num {
            font-size: 24px;
            color: #333333;
          }
          .old {
            font-size: 10px;
            color: #666666;
          }
        }
      }
      .chart-content {
        width: 100%;
        height: 500px;
        min-height: 500px;
      }
      .card-box {
        width: 100%;
        display: flex;
        justify-content: space-around;
        align-items: center;
        .card-all:not(:first-child) {
          margin-left: 20px;
        }
        .card-all {
          width: 220px;
          height: 128px;
          flex: 1;
          display: flex;
          color: #fff;
          padding: 5px 10px;
          .card-left {
            width: 65%;
            display: flex;
            flex-direction: column;
            .card-title{
              font-size: 10px;
              opacity: 0.8;
              img {
                width: 10px;
                height: 10px;
                margin-left: 3px;
              }
            }
            .card-num {
              font-size: 24px;
            }
            .card-add {
              font-size: 10px;
              opacity: 0.8;
               img {
                width: 10px;
                height: 10px;
                margin-left: 3px;
              }
            }
            .add-num {
              font-size: 10px;
            }
          }
          .card-right {
            width: 35%;
            div {
              opacity: 0.15;
              background: #000000;
              border-radius: 10px;
              text-align: center;
              margin-top: 10px;
              span {
                display: inline-block;
                opacity: 0.8;
                font-size: 10px;
                color: #ffffff;
              }
            }
          }
        }
        .card-one {
          background: url('../../assets/systemHomePage/card-one.png');
          background-size:100% 100%;
        }
        .card-two {
          background: url('../../assets/systemHomePage/card-two.png');
          background-size:100% 100%;
        }
        .card-three {
          background: url('../../assets/systemHomePage/card-three.png');
          background-size:100% 100%;
        }
        .card-four {
          background: url('../../assets/systemHomePage/card-four.png');
          background-size:100% 100%;
        }
      }
    }
    .opt-box {
      width: 100%;
      display: flex;
      justify-content: space-around;
      .opt-content {
        width: 22%;
        height: 90px;
        display: flex;
        align-items: center;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0px 4px 10px 2px rgba(153,153,153,0.20);
        cursor: pointer;
        img {
          width: 50px;
          height: 50px;
        }
        div {
          display: flex;
          flex-direction: column;
          margin-left: 10px;
          p {
            margin: 0;
            font-size: 14px;
            color: #333333;
            font-weight: 400;
          }
          span {
            font-size: 10px;
            color: #999999;
            font-weight: 400;
          }
        }
      }
    }
  }
  .right {
    min-width: 310px;
    .QR-code {
      width: 100%;
      display: flex;
      flex-direction: column;
      .title {
        font-size: 16px;
        color: #333333;
        font-weight: 400;
        margin: 0;
      }
      .edition-box {
        display: flex;
        height: 50px;
        align-items: center;
        .msg {
          font-size: 10px;
          color: #999999;
          font-weight: 400;
        }
        .edition {
          padding: 3px 3px;
          background: rgba(0,97,255,0.10);
          border: 1px solid #0061ff;
          border-radius: 10px;
          font-size: 10px;
          color: #005eff;
          font-weight: 400;
        }
      }
      .red {
        font-size: 10px;
        color: #fd3d30;
      }
      .code-img {
        height: 250px;
        img {
          width: 250px;
          height: 250px;
        }
      }
      .code-detail {
        width: 100%;
        text-align: center;
        margin: 0;
      }
    }
    .btn-box {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-around;
      div {
        padding: 8px;
        display: flex;
        align-items: center;
        background: rgba(0,97,255,0.10);
        border: 1px solid #0061ff;
        border-radius: 5px;
        img {
          height: 20px;
          width: 20px;
        }
        span {
          color: #0061ff;
          margin-left: 5px;
        }
      }
    }
    .list {
      width: 100%;
      display: flex;
      flex-direction: column;
      padding: 0 15px;
      div {
        width: 100%;
        height: 53px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px dashed #e5e5e5;
        span {
          font-size: 14px;
          color: #666666;
        }
      }
    }
  }
}
</style>
