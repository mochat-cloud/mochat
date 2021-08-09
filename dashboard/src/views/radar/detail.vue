<template>
  <div class="interactive_radar_detail">
    <a-card class="mb16" title="雷达链接信息">
      <div class="info flex">
        <div class="left">
          <div class="item flex">
            <div class="title">雷达标题：</div>
            <div class="content">{{ linkSetData.title }}</div>
          </div>
          <div class="item flex">
            <div class="title">原链接：</div>
            <div class="content link">{{ linkSetData.link }}</div>
          </div>
          <div class="item flex">
            <div class="title">创建时间：</div>
            <div class="content">
              {{ linkSetData.createdAt }}
            </div>
          </div>
        </div>
        <div class="right">
          <div class="item flex">
            <div class="title">创建人：</div>
            <div class="content">{{ linkSetData.create_user }}</div>
          </div>
          <div class="item flex">
            <div class="title">行为通知：</div>
            <div class="content" v-if="linkSetData.actionNotice==1">已开启</div>
            <div class="content" v-else>已关闭</div>
          </div>
          <div class="item flex">
            <div class="title">动态通知：</div>
            <div class="content" v-if="linkSetData.dynamicNotice==1">已开启</div>
            <div class="content" v-else>已关闭</div>
          </div>
          <div class="item flex">
            <div class="title">客户标签：</div>
            <div class="content">
              <a-tag v-for="(item,index) in linkSetData.contactTags" :key="index">{{ item.tagname }}</a-tag>
            </div>
          </div>
        </div>
      </div>
    </a-card>
    <a-card class="mb16" title="数据总览">
      <div class="data-panel">
        <div class="data">
          <div class="item">
            <div class="count">{{ statistics.total_person_num }}</div>
            <div class="desc">总点击人数</div>
          </div>
          <div class="item">
            <div class="count">{{ statistics.total_click_num }}</div>
            <div class="desc">总点击次数</div>
          </div>
          <div class="item">
            <div class="count">{{ statistics.today_person_num }}</div>
            <div class="desc">今日点击人数</div>
          </div>
          <div class="item">
            <div class="count">{{ statistics.today_click_num }}</div>
            <div class="desc">今日点击次数</div>
          </div>
        </div>
      </div>
    </a-card>
    <a-card class="mb16" title="数据详情">
      <div class="filter">
        <div class="mb16">
          <span>筛选：</span>
          <a-radio-group
            :options="radio.chart.options"
            v-model="radio.chart.current"
          />
        </div>
        <div class="mb32">
          <span>时间筛选：</span>
          <a-range-picker @change="channelTimeScreen"/>
        </div>
        <div class="chart flex">
          <div class="left">
            <div class="title">渠道点击次数Top 10</div>
            <div class="echarts" ref="echarts"></div>
          </div>
          <div class="right">
            <div class="title">渠道点击人数Top 10</div>
            <div class="list">
              <div class="item" v-for="(item,index) in person_statistics" :key="index">
                <div class="info">
                  <div class="ranking">{{ index+1 }}</div>
                  <div class="name">{{ item.channelName }}</div>
                </div>
                <div class="count">{{ item.total }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </a-card>
    <!--    表格-->
    <a-card>
      <a-tabs v-model="showpanel" :animated="false">
        <a-tab-pane :key="0" tab="客户数据">
          <!--          筛选-->
          <div class="mb16">
            <span>筛选：</span>
            <a-radio-group
              :options="radio.customer.options"
              v-model="radio.customer.current"
            />
          </div>
          <div class="mb16">
            <!--          搜索-->
            <div class="filter-form">
              <!--              搜索客户-->
              <div class="item">
                <span>搜索客户：</span>
                <div class="input">
                  <a-input-search
                    placeholder="请输入要搜索的客户"
                    v-model="askclientTable.contactName"
                    @search="retrievalTable"
                    :allowClear="true"
                    @change="emptyNickIpt"
                  />
                </div>
              </div>
              <!--              点击渠道-->
              <div class="item">
                <span>点击渠道：</span>
                <div class="input">
                  <a-select
                    placeholder="请选择"
                    style="width: 220px;"
                    v-model="askclientTable.channelId"
                    @change="optChannel"
                  >
                    <a-select-option
                      v-for="(item,index) in channelArray"
                      :key="index"
                      :value="item.id" >{{ item.name }}</a-select-option>
                  </a-select>
                </div>
              </div>
              <!--              时间筛选-->
              <div class="item">
                <span>时间筛选：</span>
                <div class="input">
                  <a-range-picker
                    style="width: 220px;"
                    v-model="optCityData.clientTimeData"
                    @change="screenClientTime"
                  />
                </div>
              </div>
              <!--              所属客服-->
              <div class="item">
                <span>所属客服：</span>
                <div class="input">
                  <a-select
                    placeholder="请选择客服"
                    :showSearch="true"
                    @change="selectStore"
                    style="width: 200px;"
                    v-model="optCityData.shop"
                  >
                    <a-select-option
                      v-for="(item,index) in staffArray"
                      :key="index"
                      :value="item.name"
                    >{{ item.name }}
                    </a-select-option>
                  </a-select>
                </div>
              </div>
              <div class="item">
                <a-button @click="resetClientTable">重置</a-button>
              </div>
            </div>
            <!--            客户数据表格-->
            <div class="table mt16">
              <a-table
                :columns="table.customer.col"
                :data-source="table.customer.data">
                <div slot="employee" slot-scope="text">
                  <a-tag><a-icon type="user" />{{ text }}</a-tag>
                </div>
                <div slot="operation" slot-scope="text, record">
                  <a @click="lookDetails(showpanel,record)">点击详情</a>
                  <a-divider type="vertical" />
                  <span style="color: gray;" v-if="record.contact_id==0||record.employee_id==0">客户详情</span>
                  <a v-else @click="$router.push({ path: '/workContact/contactFieldPivot?contactId='+record.contact_id+'&employeeId='+record.employee_id+'&isContact=2'})">客户详情</a>
                </div>
              </a-table>
            </div>
          </div>
        </a-tab-pane>
        <a-tab-pane :key="1" tab="渠道数据">
          <div class="mb16">
            <span>筛选：</span>
            <a-radio-group
              :options="radio.channel.options"
              v-model="radio.channel.current"
            />
          </div>
          <div class="mb16">
            <div class="filter-form">
              <div class="item">
                <span>时间筛选：</span>
                <div class="input">
                  <a-range-picker style="width: 220px;" @change="optcanalTime" v-model="canalTimeData"/>
                </div>
              </div>
            </div>
            <div class="table mt16">
              <a-table
                :columns="table.channel.col"
                :data-source="table.channel.data"
              >
                <div slot="operation" slot-scope="text, record">
                  <a @click="lookDetails(showpanel,record)">查看详情</a>
                </div>
              </a-table>
            </div>
          </div>
        </a-tab-pane>
      </a-tabs>
    </a-card>
    <clickDetails ref="detailsPop" />
  </div>
</template>

<script>
import echarts from 'echarts'
// eslint-disable-next-line no-unused-vars
import { showApi, showContactApi, department, indexChannelApi, showChannelApi } from '@/api/radar'
import clickDetails from '@/views/radar/components/clickDetails'
export default {
  components: { clickDetails },
  data () {
    return {
      // 显示的面板
      showpanel: 0,
      // 单选数据
      radio: {
        // 图表（数据详情）
        chart: {
          options: [
            { label: '全部渠道', value: '0' },
            { label: '自建渠道', value: '1' }
          ],
          current: '0'
        },

        // 客户数据表格
        customer: {
          options: [
            { label: '全部渠道', value: '0' },
            { label: '自建渠道', value: '1' }
          ],
          current: '0'
        },

        // 渠道数据表格
        channel: {
          options: [
            { label: '全部渠道', value: '0' },
            { label: '自建渠道', value: '1' }
          ],
          current: '0'
        }
      },
      // 表格数据
      table: {
        // 客户数据表格
        customer: {
          col: [
            {
              dataIndex: 'contactName',
              title: '客户'
            },
            {
              dataIndex: 'employee',
              title: '所属成员',
              scopedSlots: { customRender: 'employee' }
            },
            {
              dataIndex: 'createdAt',
              title: '最近点击时间'
            },
            {
              dataIndex: 'channel',
              title: '最近点击渠道'
            },
            {
              dataIndex: 'click_num',
              title: '客户点击总次数'
            },
            {
              dataIndex: 'operation',
              title: '操作',
              scopedSlots: { customRender: 'operation' }
            }
          ],
          data: []
        },
        // 渠道数据表格
        channel: {
          col: [
            {
              dataIndex: 'channelName',
              title: '渠道来源'
            },
            {
              dataIndex: 'click_num',
              title: '点击次数'
            },
            {
              dataIndex: 'person_num',
              title: '点击人数'
            },
            {
              dataIndex: 'operation',
              title: '操作',
              scopedSlots: { customRender: 'operation' }
            }
          ],
          data: []
        }
      },
      //  雷达链接信息
      linkSetData: {},
      // 数据总览
      statistics: {},
      click_statistics: [],
      person_statistics: [],
      //  客户表格请求数据
      askclientTable: {
        type: 1
      },
      // 客服信息
      staffArray: [],
      // 渠道列表数据
      channelArray: [],
      //  客户表格显示信息
      optCityData: {},
      //  渠道表格请求数据
      canalAskData: {
        type: 1
      },
      //  渠道时间展示
      canalTimeData: []
    }
  },
  created () {
    this.id = this.$route.query.id
    this.askclientTable.radar_id = this.id
    this.canalAskData.radar_id = this.id
    this.getDetailData({ id: this.id })
    // 客户表格
    this.getClientTableData(this.askclientTable)
    this.getNumberData()
    this.getChannelData()
    //  渠道数据表格
    this.getCanalTable(this.askclientTable)
  },
  methods: {
    // 客户数据查看详情
    lookDetails (type, record) {
      if (type == 1) {
        record.type = 1
        record.radar_id = this.id
      }
      this.$refs.detailsPop.show(type, record)
    },
    // 渠道数据
    // 选择时间
    optcanalTime (date, dateString) {
      this.askclientTable.start_time = dateString[0]
      this.askclientTable.end_time = dateString[1]
      this.getCanalTable(this.askclientTable)
    },
    // 获取渠道表格数据  showChannelApi
    getCanalTable (params) {
      showChannelApi(params).then((res) => {
        this.table.channel.data = res.data.list
      })
    },

    // 客户数据
    // 获取点击渠道数据列表
    getChannelData () {
      indexChannelApi({ radar_id: this.id }).then((res) => {
        this.channelArray = res.data.list
      })
    },
    // 获取员工数据
    getNumberData () {
      department().then((res) => {
        this.staffArray = res.data.employee
      })
    },
    // 搜索门店名称
    retrievalTable () {
      this.getClientTableData(this.askclientTable)
    },
    // 重置
    resetClientTable () {
      this.optCityData = {}
      this.askclientTable = {}
      this.askclientTable.type = 1
      this.askclientTable.radar_id = this.id
      this.getClientTableData(this.askclientTable)
    },
    // 点击筛选渠道
    optChannel () {
      this.getClientTableData(this.askclientTable)
    },
    // 时间筛选
    screenClientTime (date, dateString) {
      this.askclientTable.start_time = dateString[0]
      this.askclientTable.end_time = dateString[1]
      this.getClientTableData(this.askclientTable)
    },
    // 选择客服
    selectStore (e) {
      this.staffArray.forEach((item) => {
        if (item.name == e) {
          this.askclientTable.employeeId = item.id
        }
      })
      this.getClientTableData(this.askclientTable)
    },
    // 清空搜索框
    emptyNickIpt () {
      if (this.askclientTable.contactName == '') {
        this.getClientTableData(this.askclientTable)
      }
    },
    // 客户数据表格
    getClientTableData (params) {
      showContactApi(params).then((res) => {
        this.table.customer.data = res.data.list
      })
    },
    // 时间筛选
    channelTimeScreen (date, dateString) {
      const startTime = dateString[0]
      const endTime = dateString[1]
      this.getDetailData({
        id: this.id,
        start_time: startTime,
        end_time: endTime
      })
    },
    // 获取详情数据
    getDetailData (params) {
      showApi(params).then((res) => {
        this.linkSetData = res.data.info
        this.statistics = res.data.statistics
        this.person_statistics = res.data.person_statistics
        this.click_statistics = res.data.click_statistics
        const col = []
        const data = []
        this.click_statistics.forEach((item, index) => {
          col[index] = item.channelName
          data[index] = item.total
        })
        this.initChart(col, data)
      })
    },
    /**
     * 初始化排行榜图表
     */
    initChart (col, data) {
      const myChart = echarts.init(this.$refs.echarts)
      const option = {
        backgroundColor: '#fff',
        xAxis: {
          data: col,
          axisLine: {
            lineStyle: {
              color: '#3d5269'
            }
          },
          axisLabel: {
            color: '#000',
            fontSize: 14
          }
        },
        yAxis: {
          name: '',
          nameTextStyle: {
            color: '#000',
            fontSize: 16
          },
          axisLine: {
            lineStyle: {
              color: '#3d5269'
            }
          },
          axisLabel: {
            color: '#000',
            fontSize: 16
          },
          splitLine: {
            show: false
          },
          interval: 500
        },
        series: [{
          type: 'bar',
          barWidth: 50,
          itemStyle: {
            normal: {
              color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                offset: 0,
                color: 'rgb(91 142 251)'
              }, {
                offset: 1,
                color: 'rgb(91 142 251)'
              }], false)
            }
          },
          label: {
            normal: {
              show: true,
              fontSize: 14,
              color: '#000',
              position: 'top'
            }
          },
          data
        }]
      }
      myChart.setOption(option)
    }
  }
}
</script>

<style lang="less" scoped>
.info {
  .left, .right {
    flex: 1;
  }
  .left {
    margin-right: 16px;
  }
  .item {
    margin-bottom: 16px;
  }
  .title {
    font-size: 14px;
    color: rgba(0, 0, 0, .65);
    width: 74px;
    text-align: right;
  }
  .content {
    font-size: 14px;
    color: rgba(0, 0, 0, .95);
  }
  .link {
    word-break: break-all;
    flex: 1;
  }
}
.data-panel {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
  .data {
    flex: 1;
    height: 120px;
    background: #fbfdff;
    border: 1px solid #daedff;
    padding: 31px;
    margin-right: 25px;
    display: flex;
    align-items: center;
    .item {
      flex: 1;
      border-right: 1px solid #e9e9e9;
      .count {
        font-size: 24px;
        font-weight: 500;
        text-align: center;
      }
      .desc {
        font-size: 13px;
        text-align: center;
      }
      &:last-child {
        border-right: 0;
      }
    }
    &:last-child {
      margin-right: 0;
    }
  }
}

.chart {
  .left {
    flex: 1;
  }

  .left {
    .echarts {
      width: auto;
      height: 380px;
    }
  }

  .right {
    width: 285px;
    margin-left: 100px;

    .list {
      margin-top: 16px;
      height: 380px;
      overflow: auto;

      .item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;

        .info {
          display: flex;
          align-items: center;
          flex: 1;
        }

        .ranking {
          width: 22px;
          height: 22px;
          border-radius: 50%;
          background: #b7b7b7;
          color: #fff;
          display: flex;
          align-items: center;
          justify-content: center;
          margin-right: 6px;
        }

        .count {
          margin-right: 10px;
        }
      }
    }
  }

  .title {
    font-size: 14px;
    font-weight: 600;
    color: rgba(0, 0, 0, .85);
  }
}

.filter-form {
  display: flex;
  align-items: center;
  flex-wrap: wrap;

  .item {
    display: flex;
    align-items: center;
    margin-right: 23px;
    margin-bottom: 16px;

    span {
      font-size: 14px;
      font-weight: 400;
      color: rgba(0, 0, 0, .65);

      .input {
        width: 220px;
      }
    }
  }
}
</style>
