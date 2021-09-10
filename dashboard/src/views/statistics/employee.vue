<template>
  <div class="employee">
    <a-card title="联系客户数据" class="mb16">
      <div class="mb16 flex">
        <span>
          自定义日期：
        </span>
        <a-range-picker v-model="contact.date" @change="getContactData" />
        <div class="ml30">
          <a-button @click="resetContactData">
            重置
          </a-button>
        </div>
      </div>
      <div class="data-panel">
        <div class="data">
          <div class="item">
            <div class="count">
              {{ contact.data.chat_cnt }}
            </div>
            <div class="desc">
              聊天总数(条)
            </div>
          </div>
          <div class="item">
            <div class="count">
              {{ contact.data.message_cnt }}
            </div>
            <div class="desc">
              发送消息数(条)
            </div>
          </div>
          <div class="item">
            <div class="count">
              {{ contact.data.reply_percentage }}%
            </div>
            <div class="desc">
              已回复聊天占比
            </div>
          </div>
          <div class="item">
            <div class="count">
              {{ contact.data.avg_reply_time }}
            </div>
            <div class="desc">
              平均首次回复时长(分)
            </div>
          </div>
        </div>
      </div>
    </a-card>
    <a-card title="趋势图" class="mb16">
      <div class="mb20">
        <a-radio-group
          v-model="radio.filterType.current"
          :options="radio.filterType.list"
          @change="switchTrend"
        />
      </div>
      <div class="mb16 flex">
        <div class="item mr23">
          <span>
            日期筛选：
          </span>
          <a-range-picker v-model="trend.date" @change="switchTrendDate" />
        </div>
        <a-select
          mode="multiple"
          v-model="askTrendInfo.employees"
          style="min-width: 200px;max-width: 570px;"
          placeholder="请选择员工"
          @change="choiceStaff"
        >
          <a-select-option v-for="(item,index) in employeesList" :key="index" :value="item.id">{{ item.name }}</a-select-option>
        </a-select>
        <div class="item ml60">
          <a-button @click="resetBtn">重置</a-button>
        </div>
      </div>
      <div class="trend-chart">
        <div id="TrendChart"></div>
      </div>
    </a-card>
    <a-card title="数据表格" class="mb16">
      <div class="mb23">
        <a-radio-group v-model="radio.tableType.current" :options="radio.tableType.list" />
      </div>
      <div v-if="radio.tableType.current === '0'">
        <a-table
          :columns="table.date.col"
          :data-source="table.date.data"
          bordered
        />
      </div>
      <div v-if="radio.tableType.current === '1'">
        <a-table
          :columns="table.staff.col"
          :data-source="table.staff.data"
          bordered
        >
          <div class="flex" slot="member" slot-scope="record">
            <div class="avatar">
              <img :src="record.avatar">
            </div>
            <div class="name">{{ record.name }}</div>
          </div>
        </a-table>
      </div>
    </a-card>
    <a-card title="成员客户数TOP10">
      <div class="customer-chart">
        <div id="CustomerChart"></div>
      </div>
    </a-card>
  </div>
</template>

<script>
import echarts from 'echarts'
import moment from 'moment'
// eslint-disable-next-line no-unused-vars
import { employeesInfo, employeesTrendInfo, getEmployeeCounts, topList, department } from '@/api/statistic'

export default {
  data () {
    return {
      radio: {
        filterType: {
          list: [
            { label: '聊天总数', value: '1' },
            { label: '发送消息数', value: '2' },
            { label: '已回复聊天占比', value: '3' },
            { label: '平均首次回复时长', value: '4' }
          ],
          current: '1'
        },
        tableType: {
          list: [
            { label: '按日期查看', value: '0' },
            { label: '按员工查看', value: '1' }
          ],
          current: '0'
        }
      },
      table: {
        date: {
          col: [
            {
              title: '日期',
              dataIndex: 'date'
            },
            {
              title: '聊天总数',
              dataIndex: 'chat_cnt'
            },
            {
              title: '发送消息数',
              dataIndex: 'message_cnt'
            },
            {
              title: '已回复聊天占比',
              dataIndex: 'reply_percentage'
            },
            {
              title: '平均首次回复时长',
              dataIndex: 'avg_reply_time'
            }
          ],
          data: []
        },
        staff: {
          col: [
            {
              title: '成员',
              scopedSlots: { customRender: 'member' }
            },
            {
              title: '聊天总数',
              dataIndex: 'chat_cnt'
            },
            {
              title: '发送消息数',
              dataIndex: 'message_cnt'
            },
            {
              title: '已回复聊天占比',
              dataIndex: 'reply_percentage'
            },
            {
              title: '平均回复时长',
              dataIndex: 'avg_reply_time'
            }
          ],
          data: []
        }
      },
      contact: {
        date: [],
        data: {}
      },
      trend: {
        date: [],
        data: {}
      },
      // 员工下拉列表
      employeesList: [],
      // 趋势图请求数据
      askTrendInfo: {
        startTime: '',
        endTime: '',
        mode: 1,
        employees: []
      }
    }
  },
  mounted () {
    this.getContactData()
    this.getTrendData()
    this.getEmployeeData()
    this.gettopListData()
  },
  created () {
    // 本月第一天
    const date = new Date()
    const initialTime = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + '01'
    this.trend.date[0] = moment(initialTime).format('YYYY-MM-DD')
    this.trend.date[1] = moment().format('YYYY-MM-DD')
    this.askTrendInfo.startTime = this.trend.date[0]
    this.askTrendInfo.endTime = this.trend.date[1]
    this.contact.date[0] = moment(initialTime).format('YYYY-MM-DD')
    this.contact.date[1] = moment().format('YYYY-MM-DD')
    //  获取员工下拉列表数据
    this.getEmployeeList()
  },
  methods: {
    // 重置按钮
    resetBtn () {
      const date = new Date()
      this.radio.filterType.current = '1'
      this.askTrendInfo.mode = 1
      this.askTrendInfo.employees = []
      const initialTime = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + '01'
      this.trend.date = [
        initialTime,
        moment().format('YYYY-MM-DD')
      ]
      this.askTrendInfo.startTime = this.trend.date[0]
      this.askTrendInfo.endTime = this.trend.date[1]
      this.getTrendData()
    },
    // 选择员工
    choiceStaff () {
      this.getTrendData()
    },
    // 获取员工下拉列表数据
    getEmployeeList () {
      department().then((res) => {
        this.employeesList = res.data.employee
      })
    },
    // 切换趋势图日期
    switchTrendDate (date, dateString) {
      if (dateString[0] != '') {
        this.askTrendInfo.startTime = dateString[0]
        this.askTrendInfo.endTime = dateString[1]
        this.getTrendData()
      }
    },
    // 切换趋势图
    switchTrend (e) {
      this.askTrendInfo.mode = e.target.value
      this.getTrendData()
    },
    /**
     * 重置联系客户数据
     */
    resetContactData () {
      const date = new Date()
      const initialTime = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + '01'
      this.contact.date = [
        initialTime,
        moment().format('YYYY-MM-DD')
      ]
      this.getContactData()
    },

    /**
     * 获取趋势图数据
     */
    getTrendData () {
      const params = this.askTrendInfo
      employeesTrendInfo(params).then(res => {
        this.trend.data = res.data
        this.table.date.data = res.data.list
        this.initTrendChart()
      })
    },

    /**
     * 获取联系客户数据
     */
    getContactData () {
      employeesInfo({
        startTime: moment(this.contact.date[0]).format('YYYY-MM-DD'),
        endTime: moment(this.contact.date[1]).format('YYYY-MM-DD')
      }).then(res => {
        this.contact.data = res.data
      })
    },
    /**
     * 获取员工列表数据
     */
    getEmployeeData () {
      getEmployeeCounts({
        startTime: moment(this.contact.date[0]).format('YYYY-MM-DD'),
        endTime: moment(this.contact.date[1]).format('YYYY-MM-DD')
      }).then(res => {
        this.table.staff.data = res.data.table
      })
    },
    /**
     * 获取排行榜前十数据
     */
    gettopListData () {
      topList().then(res => {
        const col = []
        const data = []
        res.data.list.forEach((item, index) => {
          col[index] = item.name
          data[index] = item.total
        })
        this.initCustomerChart(col, data)
      })
    },
    // 成员客户数
    initCustomerChart (col, data) {
      const myChart = echarts.init(document.getElementById('CustomerChart'))
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
    },
    /**
     * 初始化趋势图图表
     */
    initTrendChart () {
      const lineX = []
      const value = []
      for (const i in this.trend.data.table) {
        lineX.push(i)
        value.push(this.trend.data.table[i])
      }
      const myChart = echarts.init(document.getElementById('TrendChart'))
      const charts = {
        unit: '条数',
        names: [''],
        lineX,
        value: [
          value
        ]
      }
      const color = ['rgba(91, 142, 251', 'rgba(91, 142, 251']
      const lineY = []
      for (let i = 0; i < charts.names.length; i++) {
        let x = i
        if (x > color.length - 1) {
          x = color.length - 1
        }
        const data = {
          name: charts.names[i],
          type: 'line',
          color: color[x] + ')',
          smooth: true,
          areaStyle: {
            normal: {
              color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                offset: 0,
                color: color[x] + ', 0.8)'
              }, {
                offset: 0.8,
                color: color[x] + ', 0)'
              }], false),
              shadowColor: 'rgba(0, 0, 0, 0.1)',
              shadowBlur: 10
            }
          },
          symbol: 'circle',
          symbolSize: 6,
          data: charts.value[i]
        }
        lineY.push(data)
      }
      const option = {
        backgroundColor: '#fff',
        tooltip: {
          trigger: 'axis'
        },
        legend: {
          data: charts.names,
          textStyle: {
            fontSize: 12,
            color: '#000'
          },
          right: '4%'
        },
        grid: {
          top: '14%',
          left: '4%',
          right: '4%',
          bottom: '12%',
          containLabel: true
        },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          data: charts.lineX,
          axisLabel: {
            textStyle: {
              color: '#000'
            },
            formatter: function (params) {
              return params.split(' ')[0]
            }
          }
        },
        yAxis: {
          name: charts.unit,
          type: 'value',
          axisLabel: {
            formatter: '{value}',
            textStyle: {
              color: '#000'
            }
          },
          splitLine: {
            show: false
          },
          axisLine: {
            show: false
          }
        },
        series: lineY
      }

      myChart.setOption(option)
    }
  }
}
</script>

<style lang="less" scoped>
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

.trend-chart, #TrendChart {
  width: 100%;
  height: 372px;
}

.customer-chart, #CustomerChart {
  width: 100%;
  height: 400px;
}

.trend-chart {
  position: relative;
  left: -15px;
}

.avatar img {
  width: 35px;
  height: 35px;
  margin-right: 8px;
  border-radius: 2px;
}
</style>
