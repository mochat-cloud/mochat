<template>
  <div class="contact">
    <a-card title="今日增长数据" class="mb16">
      <div class="data-panel">
        <div class="data">
          <div class="item">
            <div class="title">
              客户总数
            </div>
            <div class="count">
              {{ data.today.total }}
            </div>
          </div>
          <div class="item">
            <div class="title">
              今日新增
            </div>
            <div class="count">
              {{ data.today.add }}
            </div>
          </div>
          <div class="item">
            <div class="title">
              今日流失
            </div>
            <div class="count">
              {{ data.today.loss }}
            </div>
          </div>
          <div class="item">
            <div class="title">
              今日净增数
            </div>
            <div class="count">
              {{ data.today.net }}
            </div>
          </div>
        </div>
      </div>
    </a-card>
    <a-card title="趋势图" class="mb16">
      <div class="mb20">
        <a-radio-group
          v-model="filterTypeRadio.current"
          :options="filterTypeRadio.list"
          @change="getData('')"
        />
      </div>
      <div class="mb16 flex">
        <div class="item mr23">
          <span>
            日期筛选：
          </span>
          <a-range-picker v-model="date" @change="getData('')" />
        </div>
        <div class="item">
          <span>
            请选择员工：
          </span>
          <a-button @click="$refs.selectMember.setSelect(memberData)">选择</a-button>
        </div>
        <div style="margin-left: 10px;">
          <a-tag v-for="(item,index) in memberData" :key="index">{{ item.name }}</a-tag>
        </div>
        <div class="item ml60">
          <a-button @click="reset">
            重置
          </a-button>
        </div>
      </div>
      <div class="chart">
        <div id="chart"></div>
      </div>
    </a-card>
    <a-card title="按日期查看">
      <a-table
        :columns="table.col"
        :data-source="table.data"
        bordered
      />
    </a-card>

    <selectMember @change="selectMemberChange" ref="selectMember" />
  </div>
</template>

<script>
import echarts from 'echarts'
import { contactInfo } from '@/api/statistic'
import moment from 'moment'
import selectMember from '@/components/Select/member'

export default {
  data () {
    return {
      filterTypeRadio: {
        list: [
          { label: '客户总数', value: '1' },
          { label: '新增客户数', value: '2' },
          { label: '流失客户数', value: '3' },
          { label: '净增客户数', value: '4' }
        ],
        current: '1'
      },
      table: {
        col: [
          {
            title: '日期',
            dataIndex: 'date'
          },
          {
            title: '客户总数',
            dataIndex: 'total'
          },
          {
            title: '新增客户数',
            dataIndex: 'add'
          },
          {
            title: '流失客户数',
            dataIndex: 'loss'
          }
        ],
        data: []
      },
      date: [
        moment('2021-6-16').subtract(1, 'weeks').format('YYYY-MM-DD'),
        moment().format('YYYY-MM-DD')
      ],
      data: {
        today: {
          total: 0,
          add: 0,
          loss: 0,
          net: 0
        }
      },
      memberData: []
    }
  },
  mounted () {
    this.getData()
  },
  methods: {
    selectMemberChange (e) {
      this.memberData = e
      const ids = e.map(v => {
        return v.id
      })
      this.getData(ids)
    },

    reset () {
      this.date = [
        moment('2021-6-16').subtract(1, 'weeks').format('YYYY-MM-DD'),
        moment().format('YYYY-MM-DD')
      ]
      this.filterTypeRadio.current = '1'
      this.memberData = []
      this.getData()
    },
    getData (employeeId = '') {
      const params = {
        startTime: moment(this.date[0]).format('YYYY-MM-DD'),
        endTime: moment(this.date[1]).format('YYYY-MM-DD'),
        mode: this.filterTypeRadio.current,
        employeeId
      }
      contactInfo(params).then(res => {
        this.data = res.data
        this.table.data = res.data.any
        this.initChart()
      })
    },

    initChart () {
      const lineX = []
      const value = []

      for (const i in this.data.table) {
        lineX.push(i)
        value.push(this.data.table[i])
      }

      const myChart = echarts.init(document.getElementById('chart'))

      const charts = {
        unit: '人数',
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
  },
  components: { selectMember }
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

      .title {
        font-size: 13px;
        color: #8691a4;
        font-weight: 600;
        margin-bottom: 4px;
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

.chart, #chart {
  width: 100%;
  height: 372px;
}

.chart {
  position: relative;
  left: -15px;
}
</style>
