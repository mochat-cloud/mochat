<template>
  <div class="content-top mt16">
    <a-card>
      <div class="details-box">
        <div class="title">
          <span>群打卡信息</span>
          <span>数据统计</span>
        </div>
        <div class="details-content">
          <div class="details-left">
            <div class="preview elastic">
              <span>打卡任务名称：</span>
              <div class="preview-box">
                <span class="news">{{ clockInData.name }}</span>
              </div>
            </div>
            <div class="activity mt20 elastic">
              <span>
                打卡任务说明：
              </span>
              <div class="activity-name">
                <span class="news">{{ clockInData.description }}</span>
              </div>
            </div>
            <div class="founder mt16 elastic">
              <span > 创建人：</span>
              <a-tag><a-icon type="user" />{{ clockInData.nickname }}</a-tag>
            </div>
            <div class="creation-time mt16 elastic">
              <span>创建时间:</span>
              <div class="time ml8"><span class="news">{{ clockInData.createdAt }}</span>
              </div>
            </div>
            <div class="end-date mt16 elastic">
              <span>截止时间：</span>
              <span class="news">
                {{ clockInData.time }}
              </span>
            </div>
            <div class="tag mt16 elastic">
              <span>客户标签：</span>
            </div>
            <div style="margin-top: 10px;" v-for="(item,index) in clockInData.contactClockTags" :key="index">
              <a-tag v-for="(obj,idx) in item" :key="idx">{{ obj.tagname }}</a-tag>
            </div>
            <div class="type mt16 elastic">
              <span>活动类型：</span>
              <span class="news">{{ clockInData.type }}</span>
            </div>
            <div class="num mt16 elastic">
              <span>打卡任务数量：</span>
              <span class="news">{{ clockInData.task_count }}</span>
            </div>
          </div>
          <div class="details-right">
            <div class="data-box">
              <div class="data mt15">
                <div class="item">
                  <div class="count">{{ statisticsData.today_user }}</div>
                  <div class="desc">今日打卡人数</div>
                </div>
                <div class="item">
                  <div class="count">{{ statisticsData.total_user }}</div>
                  <div class="desc">
                    总打卡人数
                  </div>
                </div>
                <div class="item">
                  <div class="count">{{ statisticsData.average_day }}</div>
                  <div class="desc">平均打卡天数</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </a-card>
    <a-card class="mt16">
      <div class="data-details">
        <div class="title">数据详情</div>
        <div class="tag-btn">
          <div class="state">
            <a-button
              v-for="(v,index) in state.list"
              :key="index"
              @click="stateSwitchClick(v,index)"
              :type="state.current === v?'primary':'' "
            >
              {{ v }}
            </a-button>
          </div>
          <div class="button">
            <a-tag color="blue">企业微信接口限制，仅支持为已添加企业员工的客户打标签</a-tag>
            <div style="margin-top: 10px;text-align: right;">
              <a-button type="primary" ghost @click="batchTag">批量打标签</a-button>
            </div>
          </div>
          <!--   弹窗           -->
          <addlableIndex @choiceTagsArr="acceptArray" ref="childRef"/>
        </div>
        <div class="search-box">
          <div class="search">
            <div class="shop-name">
              搜索客户：
              <a-input-search placeholder="请输入客户名称" style="width: 200px" @search="searchClient" @change="emptyInput(1)" v-model="paramData.nickname" />
            </div>
            <div class="shopkeeper ml30">
              打卡天数筛选：<a-input-number placeholder="请输入打卡天数" @pressEnter="searchDay" @change="emptyInput(2)" v-model="paramData.total_day" style="width: 200px"/>
            </div>
            <div class="city ml30">
              城市：
              <a-select
                style="width: 200px"
                placeholder="请选择城市"
                @change="selectInput($event)"
                option-label-prop="label"
                v-model="paramData.city"
              >
                <a-select-option v-for="(item,index) in optionCity" :key="index" :value="item.city" :label="item.city">{{ item.city }}</a-select-option>
              </a-select>
            </div>
          </div>
          <div class="reset ml200"><a-button @click="resetBtn">重置</a-button>
          </div>
        </div>
        <div class="data-table">
          <a-table
            :columns="table.col"
            :data-source="table.data"
            :rowSelection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }">
            <div slot="nickname" slot-scope="text,record">
              <img :src="record.avatar" alt="" class="user_img" v-if=" record.avatar!='' ">
              {{ text }}
            </div>
            <div slot="employees" slot-scope="text">
              <a-tag v-for="(item,index) in text" :key="index">
                <a-icon type="user" />{{ item.name }}</a-tag>
            </div>
            <div slot="contact_clock_tags" slot-scope="text">
              <a-tag v-for="(item,index) in text" :key="index">{{ item }}</a-tag>
            </div>
            <div slot="operate" slot-scope="text,record">
              <a @click="seeTableRow(record)">查看详情</a>
            </div>
          </a-table>
          <a-modal
            v-model="signInDetails"
            title="详细打卡天数"
            :footer="null"
            :maskClosable="false"
          >
            <div class="details_row" style="display: flex;">
              <div>
                <div class="title_totalclockIn">总打卡天数：</div>
                <div class="totalclockIn_day"><span>{{ userSignData.total_day }}</span>天</div>
              </div>
              <div class="separate"></div>
              <div>
                <div class="totalclockIn_right">参与打卡时间：<span>{{ userSignData.start_time }}</span></div>
                <div class="totalclockIn_right">最近打卡时间：<span>{{ userSignData.end_time }}</span></div>
              </div>
            </div>
            <div class="details_row">
              <div class="series_clockIn_title">连续打卡任务:</div>
              <div class="taskRowTips">
                <a-tag class="taskTips" v-for="(item,index) in userSignData.task" :key="index">{{ item }}</a-tag>
              </div>
            </div>
            <div class="calendar_row">
              <div class="calendar_box">
                <Calendar :fullscreen="false">
                  <template slot="dateCellRender" slot-scope="value">
                    <div v-if="getDateCellRender(value)" class="events"></div>
                  </template>
                </Calendar>
              </div>
            </div>
          </a-modal>
        </div>
      </div>
    </a-card>
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { detailsList, clientDetails, dayDetail, batchContactTagsApi } from '@/api/roomClockIn'
import addlableIndex from '@/components/addlabel/index'
import { Calendar } from 'ant-design-vue'
export default {
  components: {
    Calendar,
    addlableIndex
  },
  data () {
    return {
      // 表格选中的数据
      selectedRowKeys: [],
      // 打标签请求id
      askClientIds: [],
      // 日历打卡详情
      // 用户打卡详情
      userSignData: {},
      // 城市
      optionCity: [],
      table: {
        col: [
          {
            key: 'nickname',
            dataIndex: 'nickname',
            title: '全部客户',
            scopedSlots: { customRender: 'nickname' }
          },
          {
            key: 'contact_type',
            dataIndex: 'contact_type',
            title: '客户类型'
          },
          {
            key: 'clock_time',
            dataIndex: 'clock_time',
            title: '最近打卡时间'
          },
          {
            key: 'city',
            dataIndex: 'city',
            title: '城市'
          },
          {
            key: 'employees',
            dataIndex: 'employees',
            title: '所属员工',
            scopedSlots: { customRender: 'employees' }
          },
          {
            key: 'contact_clock_tags',
            dataIndex: 'contact_clock_tags',
            title: '客户标签',
            scopedSlots: { customRender: 'contact_clock_tags' }
          },
          {
            key: 'total_day',
            dataIndex: 'total_day',
            title: '总打卡天数'
          },
          {
            key: 'series_day',
            dataIndex: 'series_day',
            title: '连续打卡天数'
          },
          {
            key: 'user_action',
            dataIndex: 'user_action',
            title: '用户行为'
          },
          {
            key: 'operate',
            dataIndex: 'operate',
            title: '操作',
            scopedSlots: { customRender: 'operate' }
          }
        ],
        data: []
      },
      state: {
        list: ['全部', '进入页面未参与', '参与打卡未完成', '已完成'],
        current: '全部'
      },
      //  群打卡数据
      clockInData: {},
      // 统计数据
      statisticsData: {},
      //  用户名称
      clientName: '',
      //  打卡天数
      clockInDay: '',
      //  选择框
      selectValue: '',
      paramData: {
        page: 1,
        perPage: 10,
        id: 2,
        status: 0,
        nickname: '',
        total_day: ''
      },
      //  签到详情
      signInDetails: false
    }
  },
  created () {
    this.activityId = this.$route.query.activityId
    this.getGroupData(this.activityId)
    this.paramData.id = this.activityId
    this.getTableData(this.paramData)
  },
  methods: {
    // 获取选中 的客户表格数据
    onSelectChange (e) {
      this.selectedRowKeys = e
    },
    // 批量打标签
    batchTag () {
      if (this.selectedRowKeys.length == 0) {
        this.$message.warning('请先选中一个客户')
        return false
      }
      this.askClientIds = []
      this.selectedRowKeys.forEach((item, index) => {
        this.askClientIds[index] = this.table.data[item].id
      })
      this.$refs.childRef.show()
    },
    // 接收组件传值
    acceptArray (e) {
      if (e.length == 0) {
        this.$message.error('请至少选择一个标签')
        return false
      }
      const askTags = []
      e.forEach((item, index) => {
        const tags = {}
        tags.tagid = item.id
        tags.tagname = item.name
        askTags.push(tags)
      })
      batchContactTagsApi({
        ids: this.askClientIds,
        tags: askTags
      }).then((res) => {
        this.getTableData(this.paramData)
        this.selectedRowKeys = []
        this.$message.success('操作成功')
      })
    },
    // 详细打卡信息
    getDateCellRender (value) {
      const month = value._d.getMonth()
      const selectMonth = this.userSignData.list[month]
      if (selectMonth.indexOf(value.format('YYYY-MM-DD')) == -1) {
        return false
      } else {
        return true
      }
    },
    onSelect (value) {
      this.value = value
      this.selectedValue = value
    },
    onPanelChange (value) {
      this.value = value
    },
    // 查看行详情
    seeTableRow (obj) {
      dayDetail({
        id: this.activityId,
        contact_id: obj.id
      }).then((res) => {
        this.signInDetails = true
        this.userSignData = res.data
      })
    },
    // 输入框为空
    emptyInput (index) {
      if (index == 1) {
        if (this.paramData.nickname == '') {
          this.getTableData(this.paramData)
        }
      } else if (index == 2) {
        if (this.paramData.total_day == '') {
          this.getTableData(this.paramData)
        }
      }
    },
    // 选择框
    selectInput (e) {
      this.getTableData(this.paramData)
    },
    // 搜索用户
    searchClient () {
      this.getTableData(this.paramData)
    },
    // 搜索打卡天数
    searchDay () {
      this.getTableData(this.paramData)
    },
    // 重置按钮
    resetBtn () {
      var obj = {
        page: this.paramData.page,
        perPage: this.paramData.perPage,
        id: this.paramData.id,
        status: this.paramData.status
      }
      this.paramData = []
      this.paramData = obj
      this.getTableData(this.paramData)
    },
    // 获取群打卡信息
    getGroupData (activityId) {
      detailsList({
        id: activityId
      }).then((res) => {
        this.optionCity = res.data.city
        this.clockInData = res.data.clockIn
        this.statisticsData = res.data.data_statistics
      })
    },
    // 获取表格数据
    getTableData (data) {
      clientDetails(data).then((res) => {
        this.table.data = res.data.list
      })
    },
    // 切换
    stateSwitchClick (state, index) {
      this.state.current = state
      this.paramData.status = index
      this.getTableData(this.paramData)
    }
  }
}
</script>

<style lang="less" scoped>
.details_row{
  background: #fbfdff;
  border-radius: 2px;
  border: 1px solid #daedff;
  padding: 16px 0 16px 16px;
  margin-top: 15px;
}
.details_row:first-child{
  margin-top: 0;
}
.series_clockIn_title{
  font-size: 13px;
  color: rgba(0,0,0,.45);
  line-height: 20px;
}
.taskRowTips{
  margin-top: 12px;
  display: flex;
  justify-content:space-between;
}
.taskTips{
  width: calc(50% - 3px);
  text-align: center;
  padding: 6px 12px;
  font-size: 14px;
}
.title_totalclockIn{
  font-size: 14px;
  color: rgba(0,0,0,.45);
  line-height: 20px;
}
.totalclockIn_day{
  text-align: center;
  margin-top: 6px;
  font-size: 14px;
  color: #000;
  line-height: 20px;
}
.totalclockIn_day span{
  font-size: 24px;
  font-weight: 500;
  margin-right: 2px;
}
.totalclockIn_right{
  font-size: 14px;
  color: rgba(0,0,0,.45);
  line-height: 20px;
  margin-top: 10px;
}
.totalclockIn_right:first-child{
  margin-top: 0;
}
.totalclockIn_right span{
  color: #000;
}
.separate{
  width: 1px;
  height: 51px;
  background: #E8E8E8;
  margin-left: 40px;
  margin-right: 41px;
}
.calendar_row{
  display: flex;
  justify-content:center;
  margin-top: 10px;
}
.calendar_box{
  width: 375px;
  border: 1px solid #d9d9d9;
  border-radius: 4px;
}
.calendar_box:first-child{
  margin-left: 0;
}
.ant-modal-title{
  text-align: center;
  font-size: 17px;
  font-weight: 700;
}
.ant-modal-header{
  border: 0px;
}
.details-content{
  display: flex;
}
.details-left,
.details-right{
  flex:1;
}
.title {
  font-size: 15px;
  line-height: 21px;
  color: rgba(0, 0, 0, .85);
  border-bottom: 1px solid #e9ebf3;
  padding-bottom: 12px;
  margin-bottom: 16px;
  position: relative;
  display: flex;

  span{
    flex: 1;
  }
}
.elastic{
  display:flex;
}

.news{
  color: #000000;
}

.data-box {
  display: flex;
  justify-content: center;
  flex-direction: column;
  margin-top: 15px;
  height: 125px;

  .data {
    flex: 1;
    height: 120px;
    background: #fbfdff;
    border: 1px solid #daedff;
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
.state {
  padding-top: 10px;
  padding-bottom: 10px;

  button {
    margin-right: 8px;
  }
}
.tag-btn{
  display: flex;
  align-items: center;

  .state{
    flex: 1;
  }
}
.search-box{
  display: flex;
  margin-top: 20px;
  margin-bottom: 20px;
}
.search{
  display: flex;
  flex: 1;
}
.user_img{
  width: 20px;
  height: 20px;
}
/deep/ .ant-select.ant-fullcalendar-year-select {
  display: none;
}
/deep/ .ant-fullcalendar-header .ant-radio-group {
  display: none;
}
/deep/ .ant-fullcalendar-selected-day .ant-fullcalendar-value, .ant-fullcalendar-month-panel-selected-cell .ant-fullcalendar-value{
  color: rgba(0, 0, 0, 0.65);
  background: transparent;
}
.events{
  width: 26px;
  height: 26px;
  background: rgba(24,144,255,.4);
  margin-left: 12px;
  margin-top: -42px;
}
</style>
