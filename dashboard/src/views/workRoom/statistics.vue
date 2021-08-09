<template>
  <div class="group-statistics">
    <a-card>
      <div class="number-box">
        <div class="number one">
          <p>今日新增成员数</p>
          <h1>{{ addNum }}</h1>
        </div>
        <div class="number two">
          <p>今日退群成员数</p>
          <h1>{{ outNum }}</h1>
        </div>
        <div class="number thr">
          <p>当前群成员数</p>
          <h1>{{ total }}</h1>
        </div>
        <div class="number four">
          <p>累计退群成员数</p>
          <h1>{{ outTotal }}</h1>
        </div>
      </div>
      <div class="type-wrapper">
        <a-button class="type" :type="type == 1 ? 'primary' : ''" @click="changeType(1)">按日</a-button>
        <a-range-picker class="type" v-model="time" @change="dateChange" />
        <a-button class="type" :type="type == 2 ? 'primary' : ''" @click="changeType(2)">按周</a-button>
        <a-button :type="type == 3 ? 'primary' : ''" @click="changeType(3)">按月</a-button>
      </div>
      <div>
        这段时间里，该群共新增成员数 {{ addNumRange }} 人，成员退群 {{ outNumRange }} 人
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
        <div slot="action" slot-scope="text, record">
          <template>
            <a-button type="link" @click="member(record)">群成员</a-button>
            <a-button type="link" @click="statistics(record.workRoomId)">群统计</a-button>
            <a-button type="link" @click="move(record.workRoomId)">移动分组</a-button>
          </template>
        </div>
      </a-table>
    </a-card>
  </div>
</template>

<script>
import moment from 'moment'
import { statistics, statisticsIndex } from '@/api/workRoom'
const columns = [
  {
    title: '时间',
    dataIndex: 'time'
  },
  {
    title: '新增成员数',
    dataIndex: 'addNum'
  },
  {
    title: '退群人数',
    dataIndex: 'outNum'
  },
  {
    title: '当前群成员数',
    dataIndex: 'total'
  },
  {
    title: '累计退群成员数',
    dataIndex: 'outTotal'
  }
]
export default {
  data () {
    return {
      columns: columns,
      workRoomId: '',
      // 日 周 月 1 2 3
      type: 2,
      time: null,
      startTime: '',
      endTime: '',
      addNum: 0,
      outNum: 0,
      total: 0,
      outTotal: 0,
      addNumRange: 0,
      outNumRange: 0,
      statisticsShow: true,
      options: {
        tooltip: {
          trigger: 'axis'
        },
        legend: {
          data: ['新增成员', '离开成员']
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
            name: '新增成员',
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
            name: '离开成员',
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
      },
      tableData: [],
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      }
    }
  },
  created () {
    this.workRoomId = this.$route.query.workRoomId
    this.getStatisticsData()
    this.getListData()
  },
  methods: {
    async getStatisticsData () {
      const params = {
        workRoomId: this.workRoomId,
        type: this.type,
        startTime: this.startTime,
        endTime: this.endTime
      }
      try {
        const { data: { addNum, outNum, total, outTotal, addNumRange, outNumRange, list } } = await statistics(params)
        this.addNum = addNum || 0
        this.outNum = outNum || 0
        this.total = total || 0
        this.outTotal = outTotal || 0
        this.addNumRange = addNumRange || 0
        this.outNumRange = outNumRange || 0
        const time = []
        const addNumList = []
        const outNumList = []
        if (!list || list.length == 0) {
          this.statisticsShow = false
          return
        }
        this.statisticsShow = true
        list.forEach(item => {
          time.push(item.time)
          addNumList.push(item.addNum)
          outNumList.push(item.outNum)
        })
        this.options.xAxis.data = time
        this.options.series = this.options.series.map(item => {
          if (item.name == '新增成员') {
            item.data = addNumList
          } else {
            item.data = outNumList
          }
          return item
        })
        return true
      } catch (e) {
        console.log(e)
      }
    },
    async getListData () {
      const params = {
        workRoomId: this.workRoomId,
        type: this.type,
        startTime: this.startTime,
        endTime: this.endTime,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      try {
        const { data: { page: { total }, list } } = await statisticsIndex(params)
        this.tableData = list || []
        this.pagination.total = total || 0
      } catch (e) {
        console.log(e)
      }
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
    changeType (type) {
      this.type = type
      this.getStatisticsData().then(flag => {
        if (flag) {
          this.getListData()
        }
      })
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getListData()
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
