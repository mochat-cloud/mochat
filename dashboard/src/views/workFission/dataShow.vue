<template>
  <div class="word-fission-data-show">
    <div class="select-fission flex mb20">
      <span class="label">
        选择活动：
      </span>
      <div class="select">
        <a-select
          mode="multiple"
          style="width: 100%"
          v-model="select"
          placeholder="请选择活动"
        >
          <a-select-option v-for="v in activitys" :key="v.id">
            {{ v.active_name }}
          </a-select-option>
        </a-select>
      </div>
    </div>
    <a-card title="活动实时数据" class="mb20">
      <div class="data-panel">
        <div class="data">
          <div class="item">
            <div class="count">
              {{ data.user.user_count }}
            </div>
            <div class="desc">
              参与客户总数
              <a-popover>
                <template slot="content">
                  1.点击链接进入活动页面生成专属海报的客户 2.通过此裂变活动总添加企业微信客服的客户总数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
          <div class="item">
            <div class="count">
              {{ data.user.loss_count }}
            </div>
            <div class="desc">
              流失客户总数
              <a-popover>
                <template slot="content">
                  参加的活动的客户中，流失掉的全部客户数（包括新老客户）
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
          <div class="item">
            <div class="count">
              {{ data.user.insert_count }}
            </div>
            <div class="desc">
              净增客户总数
              <a-popover>
                <template slot="content">
                  参与客户总数-流失客户数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
          <div class="item">
            <div class="count">
              {{ data.user.today_increase_count }}
            </div>
            <div class="desc">
              今日新增客户数
              <a-popover>
                <template slot="content">
                  今日参加活动的客户数，是指全部客户数，不单指新客户数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
        </div>
      </div>
      <div class="data-panel">
        <div class="data">
          <div class="item">
            <div class="count">
              {{ data.user.new_increase_count }}
            </div>
            <div class="desc">
              参与新客户数
              <a-popover>
                <template slot="content">
                  扫码的新客户，是指客户从来没有加过企业的任何一个员工，这样算是新客户
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
          <div class="item">
            <div class="count">
              {{ data.user.new_loss_count }}
            </div>
            <div class="desc">
              新增客户流失数
              <a-popover>
                <template slot="content">
                  扫码的新客户数中，流失的新客户数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
          <div class="item">
            <div class="count">
              {{ data.user.net_increase }}
            </div>
            <div class="desc">
              净增新客户数
              <a-popover>
                <template slot="content">
                  参与新客户数-新增客户流失数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
        </div>
        <div class="data">
          <div class="item">
            <div class="count">
              {{ data.user.fission_rate }}
            </div>
            <div class="desc">
              裂变率
              <a-popover>
                <template slot="content">
                  裂变率=（参与客户总数-一级客户）/参数客户总数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
          <div class="item">
            <div class="count">
              {{ data.user.insert_rate }}
            </div>
            <div class="desc">
              留存率
              <a-popover>
                <template slot="content">
                  留存率=（参与客户总数-流失客户总数）/参与客户总数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
          <div class="item">
            <div class="count">
              {{ data.user.share_rate }}
            </div>
            <div class="desc">
              分享率
              <a-popover>
                <template slot="content">
                  分享率=助力人数/参与客户总数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
        </div>
      </div>
    </a-card>
    <a-card title="客户裂变数据" class="mb20">
      <div class="client-data">
        <div class="table">
          <a-table
            :columns="table.clientFission.col"
            :data-source="table.clientFission.data"
            :pagination="false"
            bordered></a-table>
        </div>
        <div class="chart">
          <div id="echarts"></div>
        </div>
      </div>
    </a-card>
    <a-card title="客户数据">
      <div class="count-box flex fz14">
        <div class="count">
          共{{ table.clientData.total }}个客户
        </div>
        <span/>
        <div class="update fz13" @click="resetFilter">
          <a-icon type="redo"/>
          更新数据
        </div>
      </div>
      <div class="filter">
        <div class="filter-input-row">
          <div class="item">
            <span class="title">搜索客户：</span>
            <a-input-search v-model="filterFormData.nickname" placeholder="请输入客户名称"/>
          </div>
          <div class="item">
            <span class="title">所属成员：</span>
            <a-dropdown>
              <a-menu slot="overlay">
                <a-menu-item
                  v-for="member in members"
                  :key="member.id"
                  @click="()=>{ filterFormData.employee.id = member.id;filterFormData.employee.wxUserId = member.wxUserId;filterFormData.employee.name = member.name}"
                >
                  {{ member.name }}
                </a-menu-item>
              </a-menu>
              <a-button style="margin-left: 8px">
                {{ filterFormData.employee.name ? filterFormData.employee.name : '请选择成员' }}
                <a-icon type="down"/>
              </a-button>
            </a-dropdown>
          </div>
          <div class="item">
            <span class="title">添加时间：</span>
            <a-range-picker v-model="filterFormData.time"/>
          </div>
          <div class="item">
            <span class="title">完成状态：</span>
            <a-dropdown>
              <a-menu slot="overlay">
                <a-menu-item @click="filterFormData.status = 1">
                  已完成
                </a-menu-item>
                <a-menu-item @click="filterFormData.status = 0">
                  未完成
                </a-menu-item>
              </a-menu>
              <a-button style="margin-left: 8px">
                <span v-if="filterFormData.status === ''">
                  请选择
                </span>
                <span v-else>
                  {{ filterFormData.status === 0 ? '未完成' : '已完成' }}
                </span>
                <a-icon type="down"/>
              </a-button>
            </a-dropdown>
          </div>
          <div class="item">
            <span class="title">流失状态：</span>
            <a-dropdown>
              <a-menu slot="overlay">
                <a-menu-item @click="filterFormData.loss = 1">
                  已流失
                </a-menu-item>
                <a-menu-item @click="filterFormData.loss = 0">
                  未流失
                </a-menu-item>
              </a-menu>
              <a-button style="margin-left: 8px">
                <span v-if="filterFormData.loss === ''">
                  请选择
                </span>
                <span v-else>
                  {{ filterFormData.loss === 0 ? '未流失' : '已流失' }}
                </span>
                <a-icon type="down"/>
              </a-button>
            </a-dropdown>
          </div>
          <div class="reset" @click="resetFilter">
            <a-button>
              重置
            </a-button>
          </div>
        </div>
      </div>
      <div class="table">
        <a-table
          :columns="table.clientData.col"
          :data-source="table.clientData.data"
          @change="switchClientDataPage"
        >
          <div class="btn-group" slot="operating" slot-scope="row">
            <span style="color: gray;" v-if="row.contact_id==0||row.employee_id==0">客户详情</span>
            <a v-else @click="$router.push({ path: '/workContact/contactFieldPivot?contactId='+row.contact_id+'&employeeId='+row.employee_id+'&isContact=2'})">客户详情</a>
            <a-divider type="vertical"/>
            <a @click="$refs.inviteDetails.show(row.id)">邀请详情</a>
          </div>
        </a-table>
      </div>
    </a-card>

    <inviteDetails ref="inviteDetails"/>
  </div>
</template>

<script>
import echarts from 'echarts'
import { getList, getStatistics, getUserList } from '@/api/workFission'
import inviteDetails from '@/views/workFission/components/inviteDetails'

export default {
  data () {
    return {
      table: {
        clientFission: {
          col: [
            {
              title: '裂变等级',
              dataIndex: 'level'
            },
            {
              title: '裂变客户数',
              dataIndex: 'clientCount'
            }
          ],
          data: [
            {
              level: '一级裂变',
              clientCount: 0
            },
            {
              level: '二级裂变',
              clientCount: 0
            },
            {
              level: '三级裂变',
              clientCount: 0
            }
          ]
        },
        clientData: {
          col: [
            {
              title: '客户',
              dataIndex: 'nickname'
            },
            {
              title: '活动名称',
              dataIndex: 'active_name'
            },
            {
              title: '所属成员',
              dataIndex: 'employees'
            },
            {
              title: '添加时间',
              dataIndex: 'created_at'
            },
            {
              title: '流失状态',
              dataIndex: 'loss'
            },
            {
              title: '达成阶梯等级',
              dataIndex: 'level'
            },
            {
              title: '完成情况',
              dataIndex: 'status'
            },
            {
              title: '邀请好友数',
              dataIndex: 'invite_count'
            },
            {
              title: '操作',
              scopedSlots: { customRender: 'operating' }
            }
          ],
          data: [],
          total: 0,
          page: 1
        }
      },
      filterFormData: {
        nickname: '',
        employee: {
          id: '',
          name: ''
        },
        time: [],
        status: '',
        loss: ''
      },
      activitys: [],
      select: [],
      data: {
        user: {
          user_count: 0,
          loss_count: 0,
          today_increase_count: 0,
          new_increase_count: 0,
          new_loss_count: 0,
          invite_count: 0,
          insert_count: 0,
          net_increase: 0,
          fission_rate: 0,
          insert_rate: 0,
          share_rate: 0
        },
        level: {
          first_level: 0,
          second_level: 0,
          third_level: 0
        }
      },
      members: []
    }
  },
  mounted () {
    this.getActivityList()
  },
  methods: {
    resetFilter () {
      this.filterFormData = {
        nickname: '',
        employee: {
          id: '',
          name: ''
        },
        time: [],
        status: '',
        loss: ''
      }
    },

    switchClientDataPage (e) {
      this.getUserList({}, e.current)

      this.table.clientData.page = e
    },

    getUserList (params = {}, page = 1) {
      getUserList({
        fission_ids: JSON.stringify(this.select),
        page,
        ...params
      }).then(res => {
        this.table.clientData.data = res.data.list
        this.table.clientData.total = res.data.page.total
      })
    },

    getStatisticsData () {
      getStatistics({
        fission_ids: JSON.stringify(this.select)
      }).then(res => {
        this.data = res.data
        this.members = res.data.employee

        this.table.clientFission.data[0].clientCount = this.data.level.first_level
        this.table.clientFission.data[1].clientCount = this.data.level.second_level
        this.table.clientFission.data[2].clientCount = this.data.level.third_level

        this.getUserList()

        this.initChart()
      })
    },

    getActivityList () {
      getList().then(res => {
        this.activitys = res.data.list
        this.select[0] = this.activitys[0].id

        this.getStatisticsData()
      })
    },

    initChart () {
      const myChart = echarts.init(document.getElementById('echarts'))
      const tableData = this.table.clientFission.data
      const myChartData = [tableData[0].clientCount, tableData[1].clientCount, tableData[2].clientCount]
      myChart.setOption({
        grid: {
          left: '5%',
          right: '5%',
          bottom: '5%',
          top: '10%',
          containLabel: true
        },
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'none'
          },
          formatter: function (params) {
            return (`
            ${params[0].name}
            <br/>
            <span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:rgba(36,207,233,0.9)"></span>
            ${params[0].seriesName}:${params[0].value}
          `)
          }
        },
        backgroundColor: '#fff',
        xAxis: {
          show: false,
          type: 'value'
        },
        yAxis: [{
          type: 'category',
          inverse: true,
          axisLabel: {
            show: true,
            textStyle: {
              color: '#000'
            }
          },
          splitLine: {
            show: false
          },
          axisTick: {
            show: false
          },
          axisLine: {
            show: false
          },
          data: ['一级裂变客户', '二级裂变客户', '三级裂变客户']
        }, {
          type: 'category',
          inverse: true,
          axisTick: 'none',
          axisLine: 'none',
          show: true,
          axisLabel: {
            textStyle: {
              color: '#000',
              fontSize: '13'
            },
            formatter: function (value) {
              if (value >= 10000) {
                return (value / 10000).toLocaleString()
              } else {
                return value.toLocaleString()
              }
            }
          },
          data: myChartData
        }],
        series: [{
          name: '客户数量',
          type: 'bar',
          zlevel: 1,
          itemStyle: {
            normal: {
              barBorderRadius: 30,
              color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [{
                offset: 0,
                color: '#1789db'
              }, {
                offset: 1,
                color: 'rgb(46,200,207,1)'
              }])
            }
          },
          barWidth: 20,
          data: myChartData
        }, {
          name: '背景',
          type: 'bar',
          barWidth: 20,
          barGap: '-100%',
          data: myChartData,
          itemStyle: {
            normal: {
              color: '#f5f5f5',
              barBorderRadius: 30
            }
          }
        }
        ]
      })
    }
  },
  watch: {
    filterFormData: {
      handler () {
        const params = {}

        params.nickname = this.filterFormData.nickname
        params.employee = this.filterFormData.employee.wxUserId
        params.status = this.filterFormData.status
        params.loss = this.filterFormData.loss

        if (this.filterFormData.time.length) {
          params.start_time = this.filterFormData.time[0].format('YYYY-MM-DD hh:ss')
          params.end_time = this.filterFormData.time[1].format('YYYY-MM-DD hh:ss')
        } else {
          params.start_time = ''
          params.end_time = ''
        }

        this.table.clientData.page = 1

        this.getUserList(params)
      },
      deep: true
    },
    select () {
      this.getStatisticsData()
    }
  },
  components: { inviteDetails }
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

.client-data {
  display: flex;
  align-items: center;

  .table {
    max-width: 893px;
    margin-right: 55px;
  }

  div {
    flex: 1;
    height: 220px;
  }
}

.count-box {
  .count {
    font-weight: bold;
    color: #000000a6;
  }

  span {
    display: block;
    width: 1px;
    height: 14px;
    background: #e9e9e9;
    margin: 0 11px;
  }

  .update {
    cursor: pointer;
    color: rgba(0, 0, 0, .45);
  }
}

.select-fission {
  .select {
    min-width: 200px;
  }
}

/deep/ .ant-table-body {
  th, td {
    text-align: center;
  }
}
</style>
