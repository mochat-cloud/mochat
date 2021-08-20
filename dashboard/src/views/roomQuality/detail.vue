<template>
  <div class="">
    <a-card title="规则详情" style="min-width: 900px;">
      <div class="page_top">
        <div class="left">
          <div class="row">
            <span class="title">规则名称：</span>
            <span>{{ detailsData.name }}</span>
          </div>
          <div class="row">
            <span class="title">创建人：</span>
            <a-tag><a-icon type="user" />
              {{ detailsData.createUserName }}</a-tag>
            <a-tag color="green" v-if="detailsData.status==1">已开启</a-tag>
            <a-tag color="grey" v-else>已关闭</a-tag>
          </div>
          <div class="row">
            <span class="title">创建时间：</span>
            <span>{{ detailsData.createdAt }}</span>
          </div>
          <div class="row">
            <span class="title">质检群聊：</span>
            <div class="groupRow">
              <div class="groupStyle" v-for="(item,index) in detailsData.rooms" :key="index">
                <img src="../../assets/avatar-room-default.svg" alt="">
                <div class="group_information">
                  <div class="groupName">{{ item.name }}</div>
                  <div class="groupNumber">{{ item.contact_num }}/{{ item.roomMax }}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <span class="title">质检时间：</span>
            <span v-if="detailsData.qualityType==1">全天检测</span>
            <span v-if="detailsData.qualityType==2">自定义检测</span>
          </div>
          <div v-if="detailsData.qualityType==2">
            <div v-for="(item,index) in detailsData.workCycle" :key="index" class="workTime">
              <span class="title">工作周期{{ index+1 }}：</span><span v-for="(obj,idx) in item.week" :key="idx">
                <a-tag v-if="obj==0">周日</a-tag>
                <a-tag v-if="obj==1">周一</a-tag>
                <a-tag v-if="obj==2">周二</a-tag>
                <a-tag v-if="obj==3">周三</a-tag>
                <a-tag v-if="obj==4">周四</a-tag>
                <a-tag v-if="obj==5">周五</a-tag>
                <a-tag v-if="obj==6">周六</a-tag>
              </span>
              <div class="work_time">
                <span class="start_time">开始时间：</span><span>{{ item.start_time }}</span>
                <span class="start_time">结束时间：</span><span>{{ item.end_time }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="right">
          <div class="row">
            <span class="title">规则：</span>
            <div class="rule_particulars">
              <div class="rule_sub" v-for="(item,index) in detailsData.rule" :key="index">
                <div class="rule_name">规则{{ index+1 }}：</div>
                <div class="rule_set">为超过
                  <span>{{ item.num }}</span>
                  <span v-if="item.time_type==1">分钟</span>
                  <span v-if="item.time_type==2">小时</span>
                  <span v-if="item.time_type==3">天</span>
                  未回复客户消息的群聊,给
                  <span v-if="item.employee_type==1">管理员</span>
                  <span v-if="item.employee_type==2">群主</span>
                  <a-tag v-for="(obj,idx) in item.employee" :key="idx">
                    <a-icon type="user" />{{ obj.name }}</a-tag>
                  发送提醒行为</div>
              </div>
            </div>
          </div>
          <div class="row">
            <span class="title">群聊质检白名单：</span>
            <div class="rule_particulars pure_list">
              <div v-if="detailsData.whiteListStatus==1">
                <a-tag v-for="(item,index) in detailsData.keyword" :key="index">{{ item }}</a-tag>
              </div>
              <Empty v-else />
            </div>
          </div>
        </div>
      </div>
    </a-card>
    <a-card style="min-width: 900px;margin-top: 15px;">
      <div>数据总览</div>
      <div class="over_view">
        <div class="record_view">
          <h2>{{ overViewData.quality_total_num }}</h2>
          <div class="tips">总触发规则次数</div>
        </div>
        <a-divider type="vertical" class="num" />
        <div class="record_view">
          <h2>{{ overViewData.quality_today_num }}</h2>
          <div class="tips">今日触发规则次数</div>
        </div>
        <a-divider type="vertical" class="num" />
        <div class="record_view">
          <h2>{{ overViewData.quality_week_num }}</h2>
          <div class="tips">本周触发规则次数</div>
        </div>
        <a-divider type="vertical" class="num" />
        <div class="record_view">
          <h2>{{ overViewData.quality_month_num }}</h2>
          <div class="tips">本月触发规则次数</div>
        </div>
      </div>
    </a-card>
    <a-card style="min-width: 900px;margin-top: 15px;">
      <div class="table_top">
        <div>群聊详情数据<a-divider type="vertical" />共{{ table.data.length }}个群聊</div>
        <!-- <a-button>导出Excel</a-button> -->
      </div>
      <a-table :columns="table.columns" :data-source="table.data" class="tableStyle">
        <div slot="room_name" slot-scope="text"><a-tag>{{ text }}</a-tag></div>
        <div slot="owner_name" slot-scope="text"><a-tag>
          <a-icon type="user" />{{ text }}
        </a-tag></div>
        <div slot="status" slot-scope="text">
          <span v-if="text==0">正常</span>
          <span v-if="text==1">跟进人离职</span>
          <span v-if="text==2">离职继承中</span>
          <span v-if="text==3">离职继承完成</span>
        </div>
        <div slot="operation" slot-scope="text,record">
          <a @click="groupDetails(record.room_id)">查看详情</a>
        </div>
      </a-table>
      <a-modal v-model="groupShow" title="群详情" :maskClosable="false" :footer="null" width="820px">
        <div class="row_head">群聊内员工({{ room_employee.length }})</div>
        <div class="group_row">
          <div class="number_box" v-for="(item,index) in room_employee" :key="index">
            <img :src="item.avatar" alt="">
            <div class="number_right">
              <div class="number_name">{{ item.name }}</div>
              <a-tag color="blue" v-if="item.audit_status==1">已开通消息存档</a-tag>
              <a-tag v-else>未开通消息存档</a-tag>
            </div>
          </div>
        </div>
        <div class="row_head" style="margin-top: 22px;">回复数据</div>
        <div class="">
          <div class="card">
            <div class="message-wrapper">
              <ul class="message-content">
                <li
                  class="message-item"
                  v-for="(item, index) in messageData"
                  :key="index">
                  <div class="people-wrapper" :class="item.isCurrentUser == 1 ? 'people-self' : ''">
                    <div class="people-avatar">
                      <img
                        v-if="item.avatar"
                        :src="item.avatar"
                        :onerror="errorImg"
                        class="img"
                        alt="">
                      <a-icon v-else type="user" class="icon"/>
                    </div>
                    <div class="people-info">
                      <div class="name-wrapper" :class="item.isCurrentUser == 1 ? 'self-name-wrapper' : ''">
                        <span class="name">{{ item.name }}</span>
                        <span class="message-time">{{ item.msgDataTime }}</span>
                      </div>
                      <div
                        v-if="item.type == 1 || item.type > 7"
                        :class="[ 'info', item.isCurrentUser == 1 ? 'self-info' : '']">
                        {{ item.content.content }}
                        <div v-if="item.content.item" class="info-item">
                          <div v-for="(inner, innerIndex) in item.content.item" :key="innerIndex">
                            <div>
                              {{ inner.content }}
                            </div>
                          </div>
                        </div>
                      </div>
                      <div v-else :class="[ 'info', 'white', item.isCurrentUser == 1 ? 'self-info' : '']">
                        <a :href="item.content.ossFullPath" target="blank" v-if="item.type == 2">
                          <img
                            :src="item.content.ossFullPath"
                            :onerror="errorImg"
                            class="img"
                            alt="">
                        </a>
                        <audio controls class="audio" v-if="item.type == 4">
                          <source :src="item.content.ossFullPath" type="audio/mpeg">
                          <source :src="item.content.ossFullPath" type="audio/ogg">
                        </audio>
                        <video
                          v-if="item.type == 5"
                          class="video"
                          poster=""
                          controls
                          data-setup="{}"
                          preload="auto">
                          <source :src="item.content.ossFullPath">
                        </video>
                        <div v-if="item.type == 6" class="little">
                          <div class="wrpper">小程序</div>
                          <div class="title-wrapper">
                            <div class="name">{{ item.content.displayname }}</div>
                            <div class="title">{{ item.content.title }}</div>
                            <div class="description">
                              {{ item.content.description }}
                            </div>
                          </div>

                        </div>
                        <a v-if="item.type == 7" :href="item.content.ossFullPath" target="blank">
                          <div class="other">
                            <a-icon type="file" class="file" />
                            <span class="file-name">文件</span>
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </a-modal>
    </a-card>
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { infoApi, showContactApi, indexgroupApi, contactDetailApi } from '@/api/roomQuality'
import { Empty } from 'ant-design-vue'
export default {
  components: { Empty },
  data () {
    return {
      // 群成员
      room_employee: [],
      // 显示群聊弹窗
      groupShow: false,
      // 群聊消息
      messageData: [],
      errorImg: 'this.src="' + require('@/assets/avatar.png') + '"',
      table: {
        columns: [
          {
            key: 'room_name',
            dataIndex: 'room_name',
            title: '群聊名称',
            scopedSlots: { customRender: 'room_name' }
          },
          {
            key: 'owner_name',
            dataIndex: 'owner_name',
            title: '群主',
            scopedSlots: { customRender: 'owner_name' }
          },
          {
            key: 'status',
            dataIndex: 'status',
            title: '群状态',
            scopedSlots: { customRender: 'status' },
            align: 'center'
          },
          {
            key: 'room_total_num',
            dataIndex: 'room_total_num',
            title: '群人数',
            align: 'center'
          },
          {
            key: 'quality_total_num',
            dataIndex: 'quality_total_num',
            title: '总触发规则次数',
            align: 'center',
            scopedSlots: { customRender: 'quality_total_num' }
          },
          {
            key: 'quality_today_num',
            dataIndex: 'quality_today_num',
            title: '今日触发规则次数',
            align: 'center',
            scopedSlots: { customRender: 'quality_today_num' }
          }, {
            key: 'room_employee_num',
            dataIndex: 'room_employee_num',
            title: '群聊内员工人数',
            align: 'center',
            scopedSlots: { customRender: 'room_employee_num' }
          },
          {
            key: 'last_time',
            dataIndex: 'last_time',
            title: '近期提醒时间',
            align: 'center',
            scopedSlots: { customRender: 'last_time' }
          },
          {
            key: 'operation',
            dataIndex: 'operation',
            title: '操作',
            scopedSlots: { customRender: 'operation' }
          }

        ],
        data: []
      },
      //  页面详情数据
      detailsData: {},
      //  数据总览
      overViewData: {}
    }
  },
  created () {
    this.rowId = this.$route.query.rowId
    this.getDetailsData({
      id: this.rowId
    })
    this.getTableData({
      id: this.rowId
    })
  },
  methods: {
    // 获取群聊天消息
    getMessageList () {
      indexgroupApi().then((res) => {
      })
    },
    // 查看详情
    groupDetails (id) {
      contactDetailApi({ room_id: id }).then((res) => {
        this.groupShow = true
        this.room_employee = res.data.room_employee
      })
    },
    //  获取详情数据
    getDetailsData (params) {
      infoApi(params).then((res) => {
        this.detailsData = res.data
      })
    },
    //  获取表格数据
    getTableData (params) {
      showContactApi(params).then((res) => {
        this.table.data = res.data.list
        this.overViewData = res.data.data
      })
    }
  }
}
</script>
<style lang="less" scoped>
.page_top{
  display: flex;
  .left{
    width: 45%;
  }
  .row{
    display: flex;
    margin-top: 15px;
  }
  .right{
    width: 55%;
    .row{
      justify-content:flex-end;
    }
  }
  .row:first-child{
    margin-top: 0;
  }
  .title{
    font-size: 14px;
    color: rgba(0,0,0,.45);
    text-align: right;
  }
}
.groupRow{
  display: flex;
  flex-wrap: wrap;
}
.groupStyle{
  min-width: 150px;
  padding: 8px 5px 8px 8px;
  border-radius: 2px;
  border: 1px solid #e8e8e8;
  background: #fff;
  height: 50px;
  display: flex;
  margin-left: 10px;
}
.groupStyle:first-child{
  margin-left: 0;
}
.groupStyle img{
  width: 32px;
  height: 32px;
}
.groupName{
  color: rgba(0,0,0,.65);
  font-size: 10px;
}
.groupNumber{
  color: rgba(0,0,0,.45);
  font-size: 12px;
}
.group_information{
  line-height: 16px;
  margin-left: 7px;
}
.rule_particulars{
  width: calc(100% - 112px);
  min-height: 215px;
  padding: 12px;
  overflow-y: auto;
  border: 1px solid #eee;
  background: #fbfbfb;
}
.pure_list{
  min-height: 120px;
}
.rule_name{
  color: #000;
  font-size: 14px;
  font-weight: 600;
  height: 18px;
  border-left: 4px solid #C8C8C8;
  padding-left: 7px;
}
.rule_set{
  padding-left: 10px;
  margin-top: 5px;
  span{
    margin-left: 5px;
  }
}
.over_view{
  background: #fbfdff;
  border: 1px solid #cfe8ff;
  margin: 16px 0 24px;
  padding: 0 10px;
  display: flex;
  width: 50%;
  min-width: 670px;
  align-items: center;
  justify-content:space-around;
  height: 118px;
  .num{
    height: 30px;
  }
}
.record_view{
  text-align: center;
  .tips{
  }
}
.table_top{
  display: flex;
  justify-content:space-between;
}
.tableStyle{
  margin-top: 15px;
}
.workTime{
  margin-top: 15px;
  .title{
    font-size: 13px;
    color: rgba(0, 0, 0, 0.45);
  }
  .start_time{
    font-size: 13px;
    color: rgba(0, 0, 0, 0.45);
    margin-left: 20px;
  }
}
.rule_sub{
  margin-top: 10px;
}
.rule_sub:first-child{
  margin-top: 0;
}
.work_time{
  margin-left: 50px;
  margin-top: 7px;
}
.row_head{
  border-left: 3px solid #1990ff;
  font-size: 15px;
  font-weight: 600;
  color: rgba(0,0,0,.85);
  height: 18px;
  line-height: 18px;
  padding-left: 5px;
}
.group_row{
  width: 820px;
  display: flex;
  flex-wrap: wrap;
  margin-left: -24px;
  border-bottom: 1px dashed #eee;
  padding-bottom: 20px;
}
.number_box{
  display: flex;
  margin-left: 24px;
  margin-top: 20px;
  img{
    width: 40px;
    height: 40px;
    border-radius: 50%;
  }
}
.number_right{
  margin-left: 7px;
  margin-top: -5px;
  width: 110px;
}
.number_name{
  width: 100%;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
  color: #333;
  font-size: 14px;
}
/deep/ .ant-modal-title{
  text-align: center;
  font-size: 17px;
  font-weight: 700;
}
</style>
