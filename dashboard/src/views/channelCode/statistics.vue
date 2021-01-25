<template>
  <div class="group-statistics">
    <a-card>
      <div class="number-box">
        <div class="number one">
          <p>今日新增客户数</p>
          <h1>{{ addNum }}</h1>
        </div>
        <div class="number two">
          <p>今日被客户删除/拉黑的人数</p>
          <h1>{{ defriendNum }}</h1>
        </div>
        <div class="number thr">
          <p>今日删除人数</p>
          <h1>{{ deleteNum }}</h1>
        </div>
        <div class="number four">
          <p>今日净增人数</p>
          <h1>{{ netNum }}</h1>
        </div>
      </div>
      <div class="type-wrapper">
        <a-button class="type" :type="type == 1 ? 'primary' : ''" @click="changeType(1)">按日</a-button>
        <a-range-picker class="type" v-model="time" @change="dateChange" />
        <a-button class="type" :type="type == 2 ? 'primary' : ''" @click="changeType(2)">按周</a-button>
        <a-button :type="type == 3 ? 'primary' : ''" @click="changeType(3)">按月</a-button>
      </div>
      <div>
        这段时间里，共新增客户数 {{ addNumLong }} 人，被客户删除/拉黑 {{ defriendNumLong }} 人，员工删除客户{{ deleteNumLong }}人，净增人数{{ netNumLong }}人
      </div>
      <div class="chart-wrapper" v-if="statisticsShow">
        <v-chart :options="options">
        </v-chart>
      </div>
      <div class="table-title">
        详细数据
      </div>
      <a-table
        bordered
        rowKey="time"
        :pagination="pagination"
        @change="handleTableChange"
        :columns="columns"
        :data-source="tableData">
      </a-table>
    </a-card>
  </div>
</template>

<script>
import moment from 'moment'
import { statisticsIndex, statistics } from '@/api/channelCode'
export default {
  data () {
    return {
      statisticsShow: true,
      channelCodeId: '',
      // 日 周 月 1 2 3
      type: 2,
      time: null,
      startTime: '',
      endTime: '',
      columns: [
        {
          align: 'center',
          title: '时间',
          dataIndex: 'time'
        },
        {
          align: 'center',
          title: '新增客户数',
          dataIndex: 'addNumRange'
        },
        {
          align: 'center',
          title: '被客户删除/拉黑人数',
          dataIndex: 'defriendNumRange'
        },
        {
          align: 'center',
          title: '删除人数',
          dataIndex: 'deleteNumRange'
        },
        {
          title: '净增客户数',
          align: 'center',
          dataIndex: 'netNumRange'
        }
      ],
      tableData: [],
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      addNum: 0,
      defriendNum: 0,
      deleteNum: 0,
      netNum: 0,
      addNumLong: 0,
      defriendNumLong: 0,
      deleteNumLong: 0,
      netNumLong: 0,
      options: {
        tooltip: {
          trigger: 'axis'
        },
        legend: {
          data: ['新增客户数', '被客户删除/拉黑人数', '删除人数', '净增人数']
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
        },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          data: []
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
                color: '#1890ff',
                lineStyle: {
                  color: '#1890ff'
                }
              }
            }
          },
          {
            name: '删除人数',
            type: 'line',
            // stack: '总量',
            data: [],
            itemStyle: {
              normal: {
                color: 'violet',
                lineStyle: {
                  color: 'violet'
                }
              }
            }
          },
          {
            name: '净增人数',
            type: 'line',
            // stack: '总量',
            data: [],
            itemStyle: {
              normal: {
                color: 'pink',
                lineStyle: {
                  color: 'pink'
                }
              }
            }
          },
          {
            name: '被客户删除/拉黑人数',
            type: 'line',
            // stack: '总量',
            data: [],
            itemStyle: {
              normal: {
                color: 'yellow',
                lineStyle: {
                  color: 'yellow'
                }
              }
            }
          }
        ]
      }
    }
  },
  created () {
    this.channelCodeId = this.$route.query.channelCodeId
    this.getStatisticsIndex()
    this.getStatistics()
  },
  methods: {
    getStatisticsIndex () {
      const params = {
        channelCodeId: this.channelCodeId,
        type: this.type,
        startTime: this.startTime,
        endTime: this.endTime,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      statisticsIndex(params).then(res => {
        this.tableData = res.data.list
        this.pagination.total = res.data.page.total
      })
    },
    getStatistics () {
      const params = {
        channelCodeId: this.channelCodeId,
        type: this.type,
        startTime: this.startTime,
        endTime: this.endTime
      }
      statistics(params).then(res => {
        const { addNum, addNumLong, defriendNum, defriendNumLong, deleteNum, deleteNumLong, netNumLong, netNum, list } = res.data
        this.addNum = addNum
        this.addNumLong = addNumLong
        this.defriendNum = defriendNum
        this.defriendNumLong = defriendNumLong
        this.deleteNum = deleteNum
        this.deleteNumLong = deleteNumLong
        this.netNumLong = netNumLong
        this.netNum = netNum
        const time = []
        const addNumList = []
        const defriendNumList = []
        const deleteNumList = []
        const netNumList = []
        if (!list || list.length == 0) {
          this.statisticsShow = false
          return
        }
        this.statisticsShow = true
        list.forEach(item => {
          time.push(item.time)
          addNumList.push(item.addNumRange)
          defriendNumList.push(item.defriendNumRange)
          deleteNumList.push(item.deleteNumRange)
          netNumList.push(item.netNumRange)
        })
        this.options.xAxis.data = time
        this.options.series = this.options.series.map(item => {
          if (item.name == '新增客户数') {
            item.data = addNumList
          } else if (item.name == '删除人数') {
            item.data = deleteNumList
          } else if (item.name == '被客户删除/拉黑人数') {
            item.data = defriendNumList
          } else {
            item.data = netNumList
          }
          return item
        })
        return true
      })
    },
    changeType (type) {
      this.type = type
      this.getStatistics()
      this.getStatisticsIndex()
    },
    dateChange (date, dateString) {
      this.startTime = dateString[0]
      this.endTime = dateString[1]
      if (!dateString[0] || !dateString[1]) {
        this.time = null
      } else {
        this.time = [moment(this.startTime, 'YYYY-MM-DD'), moment(this.endTime, 'YYYY-MM-DD')]
      }
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getStatisticsIndex()
    }
  }
}
</script>

<style lang="less" scoped>
.group-statistics {
    .number-box {
      height: 120px;
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      .number {
        width: 23%;
        height: 100%;
        display: flex;
        flex-direction: column;
        padding: 10px;
        color: #fff;
        h1 {
          font-size: 24px;
          color: #fff;
        }
      }
      .one {
        background: #40A9FF;
      }
      .two {
        background: #FCC71B;
      }
      .thr {
        background: #8F8AFF;
      }
      .four {
        background: #F9688E;
      }
    }
    .type-wrapper {
      margin: 20px 0;
      .type {
        margin-right: 20px
      }
    }
  }
  .chart-wrapper{
    width: 100%;
    height: 500px;
    min-height: 500px;
  }
  .echarts {
    width: 100%;
    height: 100%;
  }
  .table-title{
    margin: 20px 0
  }
</style>
