<template>
  <div class="group-statistics">
    <a-row>
      <a-col :span="16" style="padding-right: 5px;">
        <a-card>
          <div style="display: flex;justify-content:space-between;">
            <div style="display: flex;">
              <img src="../../assets/avatar-room-default.svg" alt="">
              <div style="margin-left: 10px;">
                <div style="font-size: 22px;color: #000;">{{ groupDataInfo.name }}</div>
                <div style="display: flex;">
                  <div>群主：
                    <img :src="groupDataInfo.ownerAvatar" alt="" style="width: 20px;height: 20px;">
                    {{ groupDataInfo.ownerName }}
                  </div>
                  <a-divider type="vertical" />
                  <div>共{{ groupDataInfo.total }}位群成员</div>
                </div>
              </div>
            </div>
          </div>
          <div style="margin-top: 15px;"><span style="color:#a9a9a9;">群聊标签：</span></div>
          <div class="group_number">
            <div style="text-align: center;">
              <div style="font-size: 22px;font-weight: bold;">{{ groupDataInfo.total }}</div>
              <div>总人数</div>
            </div>
            <div style="text-align: center;">
              <div style="font-size: 22px;font-weight: bold;">{{ groupDataInfo.totalContact }}</div>
              <div>总客户数</div>
            </div>
            <div style="text-align: center;">
              <div style="font-size: 22px;font-weight: bold;">{{ groupDataInfo.todayInsert }}</div>
              <div>今日新增</div>
            </div>
            <div style="text-align: center;">
              <div style="font-size: 22px;font-weight: bold;">{{ groupDataInfo.todayLoss }}</div>
              <div>今日流失</div>
            </div>
          </div>
        </a-card>
      </a-col>
      <a-col :span="8" style="padding-left: 5px;">
        <a-card title="群公告" style="height: 251px;">
          <Empty >
            <span slot="description" style="color: #595959;">暂无群公告~</span>
          </Empty>
        </a-card>
      </a-col>
    </a-row>
    <a-card style="margin-top: 15px;">
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
      <div class="table-title">详细数据</div>
      <a-tabs default-active-key="1">
        <a-tab-pane key="1" tab="按日期查看">
          <a-table
            bordered
            :pagination="pagination"
            @change="handleTableChange"
            :columns="columns"
            :data-source="tableData">
          </a-table>
        </a-tab-pane>
        <a-tab-pane key="2" tab="按成员查看">
          <div style="display: flex;justify-content:space-between;">
            <div>当前群成员：{{ memberNum }}人；累计退群成员：{{ outRoomNum }}人</div>
            <a-input-search
              placeholder="输入要搜索的成员名称"
              style="width: 200px"
              @search="onSearch"
              :allowClear="true"
              @change="cleanInput"
              v-model="staffInput"
            />
          </div>
          <a-table
            :columns="tableStaff.columns"
            :data-source="tableStaff.data"
            style="margin-top: 20px;"
          >
            <div slot="name" slot-scope="text, record">
              <template>
                <div class="name-wrapper">
                  <div class="img-wrapper">
                    <img v-if="record.avatar" class="img" :src="record.avatar" alt="">
                    <a-icon v-else type="user" class="icon"/>
                  </div>
                  <div class="detail">
                    <div class="name">
                      {{ record.name }}
                    </div>
                    <a-button class="owner" v-if="record.isOwner" type="primary">群主</a-button>
                  </div>
                </div>
              </template>
            </div>
            <div slot="otherRooms" slot-scope="text, record">
              <div class="otherRooms" v-for="(item, index) in record.otherRooms" :key="index">
                {{ item }}
              </div>
            </div>
            <div slot="outRoomState" slot-scope="text, record">
              <a-tag v-if="record.outRoomTime==''" color="green">未退群</a-tag>
              <a-tag v-else color="gray">已退群</a-tag>
            </div>
            <!--            <div slot="operation" slot-scope="text, record">-->
            <!--              <template v-if="record.outRoomTime==''">-->
            <!--                <a @click="seeDetails(record)">-->
            <!--                  <span v-if="record.type==2">客户详情</span>-->
            <!--                  <span v-else>员工详情</span>-->
            <!--                </a>-->
            <!--              </template>-->
            <!--              <span v-else>员工详情</span>-->
            <!--            </div>-->
          </a-table>
        </a-tab-pane>
      </a-tabs>
    </a-card>
  </div>
</template>

<script>
import moment from 'moment'
import { Empty } from 'ant-design-vue'
// eslint-disable-next-line no-unused-vars
import { statistics, statisticsIndex, workContactRoom, showRoomApi } from '@/api/workRoom'
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
  components: {
    Empty
  },
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
      },
      //  按员工查看
      memberNum: 0,
      outRoomNum: 0,
      tableStaff: {
        columns: [
          {
            title: '群名称',
            dataIndex: 'name',
            scopedSlots: { customRender: 'name' }
          },
          {
            title: '入群时间',
            dataIndex: 'joinTime'
          },
          {
            title: '所在其他群',
            dataIndex: 'otherRooms',
            scopedSlots: { customRender: 'otherRooms' }
          },
          {
            title: '退群时间',
            dataIndex: 'outRoomTime'
          },
          {
            title: '入群方式',
            dataIndex: 'joinSceneText'
          },
          {
            title: '是否退群',
            dataIndex: 'outRoomState',
            scopedSlots: { customRender: 'outRoomState' }
          }
          // {
          //   title: '操作',
          //   dataIndex: 'operation',
          //   scopedSlots: { customRender: 'operation' }
          // }
        ],
        data: []
      },
      // 按成员查询
      staffInput: '',
      // 群信息
      groupDataInfo: {}
    }
  },
  created () {
    this.workRoomId = this.$route.query.workRoomId
    this.getStatisticsData()
    this.getListData()
    //  获取成员表格数据 workContactRoom
    this.getStaffTableData()
    this.getGroupData()
  },
  methods: {
    getGroupData () {
      showRoomApi({
        workRoomId: this.workRoomId
      }).then((res) => {
        console.log(res)
        this.groupDataInfo = res.data
      })
    },
    // 查看详情 showRoomApi
    seeDetails (record) {

    },
    // 输入框清空时
    cleanInput () {
      if (this.staffInput == '') {
        this.getStaffTableData()
      }
    },
    // 按客户查询搜索
    onSearch () {
      this.getStaffTableData()
    },
    // 按成员查看表格
    getStaffTableData () {
      workContactRoom({
        workRoomId: this.workRoomId,
        name: this.staffInput
      }).then((res) => {
        this.memberNum = res.data.memberNum
        this.outRoomNum = res.data.outRoomNum
        this.tableStaff.data = res.data.list
      })
    },
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
.group_number{
  margin-top: 15px;
  background: #fbfdff;
  border: 1px solid #cfe8ff;
  padding: 20px 10% 20px 10%;
  display: flex;
  justify-content:space-between;
}
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
//按照员工查看
.name-wrapper{
  display: flex;
  flex-direction: row;
  .img-wrapper {
    flex: 0 0 50px;
    width: 50px;
    height: 50px;
    margin-right: 10px;
  }
  .icon {
    font-size: 35px;
  }
  .img{
    width: 50px;
    height: 50px;
  }
  .detail{
    flex: 1;
    .name{
      text-align: left;
      margin-bottom: 10px;
    }
    .owner{
      width: 80px;
      height: 22px
    }
  }
}
</style>
