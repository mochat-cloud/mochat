<template>
  <div class="room-fission-data">
    <div style="background: #fff;margin-bottom: 20px;padding-top: 5px;padding-left: 10px;">
      <a-tabs v-model="tabPanel" @change="callback">
        <a-tab-pane :key="1" tab="活动列表"></a-tab-pane>
        <a-tab-pane :key="2" tab="数据详情"></a-tab-pane>
      </a-tabs>
    </div>
    <!--    当前活动-->
    <div class="select-activity mb16">
      <span>当前活动：</span>
      <a-select
        show-search
        style="width: 220px"
        v-model="shakyName"
        option-label-prop="label"
        @change="selectActivity">
        <a-select-option
          :value="item.activeName"
          :label="item.activeName"
          v-for="(item,index) in activityData"
          :key="index">
          <div class="activity">
            <div class="info">
              <div class="name">{{ item.activeName }}</div>
              <div class="time"> {{ item.createdAt }}</div>
            </div>
            <div class="status">
              <a-tag color="green" v-if="item.status==1">进行中</a-tag>
              <a-tag color="red" v-if="item.status==2">已截止</a-tag>
            </div>
          </div>
        </a-select-option>
      </a-select>
      <span style="margin-left: 15px;margin-right: 15px;">创建时间：{{ shakyNews.createdAt }}</span>
      <a-tag color="green" v-if="shakyNews.status==1">进行中</a-tag>
      <a-tag color="red" v-if="shakyNews.status==2">已截止</a-tag>
    </div>
    <!--    数据总览-->
    <a-card title="数据总览" :bordered="false" class="mb16">
      <div class="data-panel">
        <div class="data">
          <div class="item">
            <div class="count">
              {{ totalRemarkData.statistics.join_room_num }}
            </div>
            <div class="desc">
              总进群人数
              <a-popover>
                <template slot="content">
                  通过此次群裂变活动加入活动群聊的总人数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
          <div class="item">
            <div class="count">
              {{ totalRemarkData.statistics.finish_person_num }}
            </div>
            <div class="desc">
              总完成人数
              <a-popover>
                <template slot="content">
                  1.邀请好友数达到设置的「活动目标人数」<br>
                  2.进入群聊即为助力成功
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
          <div class="item">
            <div class="count">
              {{ totalRemarkData.statistics.insert_person_num }}
            </div>
            <div class="desc">
              总净增人数
              <a-popover>
                <template slot="content">
                  总进群人数-总流失人数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
          <div class="item">
            <div class="count">
              {{ totalRemarkData.statistics.loss_person_num }}
            </div>
            <div class="desc">
              总流失人数
              <a-popover>
                <template slot="content">
                  总进群人数-总净增人数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
        </div>
        <div class="data">
          <div class="item">
            <div class="count">{{ totalRemarkData.statistics.today_join_room_num }}</div>
            <div class="desc">今日进群人数
              <a-popover>
                <template slot="content">
                  今日通过此次群裂变活动加入群聊的总人数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
          <div class="item">
            <div class="count">{{ totalRemarkData.statistics.today_finish_person_num }}</div>
            <div class="desc">
              今日完成人数
              <a-popover>
                <template slot="content">
                  今日邀请好友数达到设置的「活动目标人数」
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
          <div class="item">
            <div class="count">{{ totalRemarkData.statistics.today_insert_person_num }}</div>
            <div class="desc">
              今日净增人数
              <a-popover>
                <template slot="content">
                  今日进群人数-今日流失人数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
          <div class="item">
            <div class="count">{{ totalRemarkData.statistics.today_loss_person_num }}</div>
            <div class="desc">
              今日流失人数
              <a-popover>
                <template slot="content">
                  今日进群人数-今日净增人数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </div>
          </div>
        </div>
      </div>
    </a-card>
    <!--    表格数据-->
    <a-card title="客户数据详情">
      <div class="operating mb16">
        <span class="title">筛选：</span>
        <div class="input">
          <a-radio-group :options="filterOptions" v-model="current" @change="tabTable"/>
        </div>
      </div>
      <div class="group" v-if="current == '0'">
        <div class="count mb16">
          共{{ table.group.data.length }}个群聊
        </div>
        <div class="filter-input-row search">
          <div class="input">
            <div class="item">
              <span class="title">搜索群聊：</span>
              <a-input-search
                placeholder="请输入群聊名称"
                allow-clear
                v-model="room_name"
                @search="searchGroupName"
                @change="emptygroupInput"
              />
            </div>
            <div class="item">
              <span class="title">群主：</span>
              <a-select style="width: 120px" v-model="pagedataShow.name" placeholder="选择群主" @change="optGroupleader">
                <a-select-option :value="item.id" v-for="(item,index) in totalRemarkData.room_owner" :key="index">
                  {{ item.name }}
                </a-select-option>
              </a-select>
            </div>
            <div class="reset">
              <a-button @click="resetGroupBtn">
                重置
              </a-button>
            </div>
          </div>
        </div>
        <div class="table">
          <a-table :columns="table.group.col" :data-source="table.group.data"></a-table>
        </div>
      </div>
      <div class="user" v-if="current == '1'">
        <div class="count mb16">
          共{{ table.user.data.length }}个用户
        </div>
        <div class="operating search mb16">
          <div class="input">
            <div class="item">
              <span class="title">客户名称：</span>
              <a-input-search
                placeholder="请输入客户名称"
                v-model="name"
                allow-clear
                @search="searchUserName"
                @change="emptyUserInput"
              />
            </div>
            <div class="item">
              <span class="title">时间筛选：</span>
              <a-range-picker v-model="showScreenTime.time" @change="selectUserTime"/>
            </div>
            <div class="item">
              <span class="title">流失状态：</span>
              <a-select style="width: 175px" v-model="showScreenTime.state" placeholder="选择流失状态" @change="optlossState">
                <a-select-option :value="0">未流失</a-select-option>
                <a-select-option :value="1">已流失</a-select-option>
              </a-select>
            </div>
            <div class="reset">
              <a-button @click="resetUserBtn">
                重置
              </a-button>
            </div>
          </div>
        </div>
        <div class="table">
          <a-table :columns="table.user.col" :data-source="table.user.data">
            <div slot="operation" slot-scope="row">
              <a v-if="row.write_off==0" @click="writeOffBtn(row.id)">核销</a>
              <span v-else style="color: gray;">已核销</span>
            </div>
          </a-table>
        </div>
      </div>
    </a-card>
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { indexApi, showApi, showRoomApi, showContactApi, writeOffApi } from '@/api/roomFission'
export default {
  data () {
    return {
      tabPanel: 2,
      filterOptions: [
        { label: '群聊数据', value: '0' },
        { label: '客户数据', value: '1' }
      ],
      table: {
        group: {
          col: [
            {
              title: '群聊名称',
              dataIndex: 'name'
            },
            {
              title: '群主',
              dataIndex: 'owner_name'
            },
            {
              title: '群状态',
              dataIndex: 'status'
            },
            {
              title: '群人数',
              dataIndex: 'room_num'
            },
            {
              title: '今日/总群聊进群人数',
              dataIndex: 'join_room_num'
            },
            {
              title: '今日/总群聊完成活动人数',
              dataIndex: 'finish_person_num'
            },
            {
              title: '今日/总群聊净增人数',
              dataIndex: 'insert_person_num'
            },
            {
              title: '今日/总群聊流失人数',
              dataIndex: 'loss_person_num'
            }
          ],
          data: []
        },
        user: {
          col: [
            {
              title: '客户名称',
              dataIndex: 'name'
            },
            {
              title: '客户类型',
              dataIndex: 'type'
            },
            {
              title: '进群时间',
              dataIndex: 'join_time'
            },
            {
              title: '完成情况',
              dataIndex: 'status'
            },
            {
              title: '流失状态',
              dataIndex: 'loss'
            },
            {
              title: '退群时间',
              dataIndex: 'out_time'
            },
            {
              title: '邀请好友数',
              dataIndex: 'invite_count'
            },
            {
              title: '操作',
              scopedSlots: { customRender: 'operation' }
            }
          ],
          data: []
        }
      },
      current: '0',
      //  activityData
      // 活动列表
      activityData: {},
      //  活动名称
      shakyName: '',
      // 活动信息
      shakyNews: {},
      totalRemarkData: {
        // 数据总览
        statistics: {},
        // 群主列表
        room_owner: []
      },
      // 群聊搜索条件
      room_name: '',
      // 群主id
      owner_id: '',
      // 页面数据展示
      pagedataShow: [],
      showScreenTime: [],
      //  用户搜索条件
      name: '',
      start_time: '',
      end_time: '',
      loss_status: ''
    }
  },
  created () {
    this.getActivityData()
  },
  methods: {
    // 核销按钮
    writeOffBtn (id) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否核销',
        okText: '核销',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          writeOffApi({ id }).then((res) => {
            that.getUserTable({
              id: this.shakyNews.id,
              name: this.name,
              start_time: this.start_time,
              end_time: this.end_time,
              loss_status: this.loss_status
            })
            that.$message.success('核销成功')
          })
        }
      })
    },
    // 重置按钮
    resetGroupBtn () {
      this.room_name = ''
      this.owner_id = ''
      this.pagedataShow = []
      this.getGroupTable({ id: this.shakyNews.id })
    },
    // 选择群主
    optGroupleader (e) {
      this.owner_id = e
      this.getGroupTable({
        id: this.shakyNews.id,
        room_name: this.room_name,
        owner_id: this.owner_id
      })
    },
    // 筛选时间
    selectUserTime (date, dateString) {
      this.start_time = dateString[0]
      this.end_time = dateString[1]
      this.getUserTable({
        id: this.shakyNews.id,
        name: this.name,
        start_time: this.start_time,
        end_time: this.end_time,
        loss_status: this.loss_status
      })
    },
    // 用户表格重置按钮
    resetUserBtn () {
      this.name = ''
      this.loss_status = ''
      this.start_time = ''
      this.end_time = ''
      this.showScreenTime = []
      this.getUserTable({
        id: this.shakyNews.id
      })
    },
    // 流失状态
    optlossState (e) {
      this.loss_status = e
      this.getUserTable({
        id: this.shakyNews.id,
        name: this.name,
        start_time: this.start_time,
        end_time: this.end_time,
        loss_status: this.loss_status
      })
    },
    // 搜索用户名
    searchUserName () {
      this.getUserTable({
        id: this.shakyNews.id,
        name: this.name,
        start_time: this.start_time,
        end_time: this.end_time,
        loss_status: this.loss_status
      })
    },
    // 清空用户名
    emptyUserInput () {
      if (this.name == '') {
        this.getUserTable({
          id: this.shakyNews.id,
          start_time: this.start_time,
          end_time: this.end_time,
          loss_status: this.loss_status
        })
      }
    },
    // 搜索群名
    searchGroupName () {
      this.getGroupTable({
        id: this.shakyNews.id,
        room_name: this.room_name,
        owner_id: this.owner_id
      })
    },
    // 清空输入框
    emptygroupInput () {
      if (this.room_name == '') {
        this.getGroupTable({
          id: this.shakyNews.id,
          owner_id: this.owner_id
        })
      }
    },
    // 获取选中活动
    selectActivity (e) {
      this.activityData.forEach((item, index) => {
        if (item.activeName == this.shakyName) {
          this.shakyNews = item
        }
      })
      this.getPageData()
    },
    // 获取页面数据
    getPageData () {
      // 数据总览
      this.getRecordOver({ id: this.shakyNews.id })
      // 群聊表格
      this.getGroupTable({ id: this.shakyNews.id })
      this.current = '0'
    },
    // 切换表格
    tabTable () {
      if (this.current == 0) {
        if (this.table.group.data.length == 0) {
          this.getGroupTable({ id: this.shakyNews.id })
        }
      } else if (this.current == 1) {
        if (this.table.user.data.length == 0) {
          this.getUserTable({ id: this.shakyNews.id })
        }
      }
    },
    // 获取用户表格数据
    getUserTable (params) {
      showContactApi(params).then((res) => {
        console.log(res)
        this.table.user.data = res.data.list
      })
    },
    // 获取群聊表格数据
    getGroupTable (params) {
      showRoomApi(params).then((res) => {
        this.table.group.data = res.data.list
      })
    },
    // 数据总览信息
    getRecordOver (params) {
      showApi(params).then((res) => {
        this.totalRemarkData = res.data
        // 选择群主
        // console.log(this.totalRemarkData.room_owner)
      })
    },
    // 获取活动信息
    getActivityData (params) {
      indexApi(params).then((res) => {
        this.activityData = res.data.list
        this.shakyName = this.activityData[0].activeName
        this.shakyNews = res.data.list[0]
        this.getPageData()
      })
    },
    callback () {
      if (this.tabPanel == 1) {
        this.$router.push({ path: '/roomFission/index' })
      }
    }
  }
}
</script>

<style lang="less" scoped>
.ant-dropdown-link {
  color: #545454;
}

.ant-dropdown-menu {
  padding: 15px 20px;

  .ant-input-search {
    width: 100%;
    margin-top: 10px;
    margin-bottom: 10px;
  }
}

.activity {
  display: flex;
  align-items: center;

  .info {
    flex: 1;
  }
}

.data-panel {
  display: flex;
  align-items: center;
  justify-content: center;

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

.operating {
  display: flex;
  align-items: center;

  .item {
    display: flex;
    align-items: center;

    .title {
      width: 100px;
    }
  }
}

.search {
  .input {
    display: flex;
    align-items: center;

    .title {
      text-align: right;
    }
  }
}

.reset {
  margin-left: 50px;
}

.mb16 {
  margin-bottom: 16px;
}
</style>
