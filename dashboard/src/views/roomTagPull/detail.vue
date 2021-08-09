<template>
  <div class="">
    <div class="pageTop">
      <a-card style="width: 373px" class="topleft">
        <a-row>
          <a-col :span="8">
            <div class="conditionTitle">邀请入群条件</div>
          </a-col>
          <a-col :span="1">|</a-col>
          <a-col :span="15"><span>已有{{ showData.join_room_num }}人入群</span></a-col>
        </a-row>
        <div class="tl_bottom mt20">
          <div class="tlbRight mb6" v-for="(room,i) in showData.rooms" :key="i">
            <div class="tlbr_left">
              <img
                src="../../assets/avatar-room-default.svg"
                class="groupImg mr6">
              <div class="tlbrl_left">
                <div>{{ room.name }}</div>
                <div class="tlbrl_left_bottom">
                  <span>{{ room.contact_num }}/{{ room.room_max }}</span>
                  <span>|</span>
                  <span>本次入群：{{ room.contact_num }}人</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </a-card>
      <a-card class="topright">
        <div class="conditionTitle">客户统计</div>
        <a-row type="flex" justify="space-between">
          <a-col :span="7">
            <a-row class="tr_content">
              <a-col :span="11" class="trc_content">
                <div class="typeQuantity">{{ showData.join_room_num }}</div>
                <div class="trc_type">已入群客户</div>
              </a-col>
              <a-col :span="2">
                <div class="trc_middle"></div>
              </a-col>
              <a-col :span="11" class="trc_content">
                <div class="typeQuantity">{{ showData.no_join_room_num }}</div>
                <div class="trc_type">未入群客户</div>
              </a-col>
            </a-row>
          </a-col>
          <a-col :span="7">
            <a-row class="tr_content">
              <a-col :span="11" class="trc_content">
                <div class="typeQuantity">{{ showData.invite_num }}</div>
                <div class="trc_type">
                  已邀请客户
                </div>
              </a-col>
              <a-col :span="2">
                <div class="trc_middle"></div>
              </a-col>
              <a-col :span="11" class="trc_content">
                <div class="typeQuantity">{{ showData.no_invite_num }}</div>
                <div class="trc_type">
                  未邀请客户
                </div>
              </a-col>
            </a-row>
          </a-col>
          <a-col :span="7">
            <a-row class="tr_content">
              <a-col :span="11" class="trc_content">
                <div class="typeQuantity">{{ showData.send_num }}</div>
                <div class="trc_type">
                  完成发送成员
                </div>
              </a-col>
              <a-col :span="2">
                <div class="trc_middle"></div>
              </a-col>
              <a-col :span="11" class="trc_content">
                <div class="typeQuantity">{{ showData.no_send_num }}</div>
                <div class="trc_type">
                  未完成发送成员
                </div>
              </a-col>
            </a-row>
          </a-col>
        </a-row>
      </a-card>
    </div>
    <div class="pageContent">
      <a-card>
        <a-tabs default-active-key="1" @change="callback" size="large">
          <a-tab-pane key="1" tab="客户详情"></a-tab-pane>
          <a-tab-pane key="2" tab="成员详情" force-render></a-tab-pane>
        </a-tabs>
        <!--     总表单   -->
        <div class="formStyle">
          <a-form-model :model="form" :label-col="labelCol" :wrapper-col="wrapperCol">
            <template v-if="clientTable">
              <a-form-model-item label="搜索客户">
                <a-input placeholder="请输入要搜索的客户" v-model="filter.contact.contact_name"/>
              </a-form-model-item>
              <a-form-model-item label="所属客服">
                <a-select placeholder="请选择客服" v-model="filter.contact.wx_user_id">
                  <a-select-option
                    v-for="(v,i) in showData.employees"
                    :key="i"
                    :value="v.wxUserId"
                  >
                    {{ v.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
              <a-form-model-item label="送达状态">
                <a-select placeholder="请选择送达状态" v-model="filter.contact.send_status">
                  <a-select-option value="0">
                    未收到邀请
                  </a-select-option>
                  <a-select-option value="1">
                    已收到邀请
                  </a-select-option>
                  <a-select-option value="2">
                    客户已不是好友
                  </a-select-option>
                  <a-select-option value="3">
                    客户接受已达上限
                  </a-select-option>
                </a-select>
              </a-form-model-item>
              <a-form-model-item label="是否入群">
                <a-select placeholder="客户是否入群" v-model="filter.contact.is_join_room">
                  <a-select-option value="0">
                    已入群
                  </a-select-option>
                  <a-select-option value="1">
                    未入群
                  </a-select-option>
                </a-select>
              </a-form-model-item>
              <a-form-model-item label="邀请群聊">
                <a-select placeholder="请选择群聊" v-model="filter.contact.room_id">
                  <a-select-option
                    v-for="(v,i) in showData.rooms"
                    :key="i"
                    :value="v.id"
                  >
                    {{ v.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
              <a-button class="ml16 mt4" @click="resetContact">重置</a-button>
            </template>
            <template class="" v-else>
              <a-form-model-item label="搜索成员" v-if="!clientTable">
                <a-select placeholder="请选择客服" v-model="filter.staff.wx_user_id">
                  <a-select-option
                    v-for="(v,i) in showData.employees"
                    :key="i"
                    :value="v.wxUserId"
                  >
                    {{ v.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
              <a-form-model-item label="收到任务条数" class="tasksNum">
                <a-select v-model="form.region" placeholder="请选择收到任务条数">
                  <a-select-option value="0">
                    1条
                  </a-select-option>
                  <a-select-option value="1">
                    多条
                  </a-select-option>
                </a-select>
              </a-form-model-item>
              <a-form-model-item label="发送状态">
                <a-select placeholder="请选择送达状态" v-model="filter.staff.task_type">
                  <a-select-option value="0">
                    未完成发送
                  </a-select-option>
                  <a-select-option value="1">
                    已完成发送
                  </a-select-option>
                </a-select>
              </a-form-model-item>
              <a-button class="ml16 mt4" @click="resetStaff">重置</a-button>
            </template>
          </a-form-model>
        </div>
        <div class="tableStyle">
          <!--    客户详情表格      -->
          <a-table
            v-if="clientTable"
            rowKey="workRoomAutoPullId"
            :columns="one.columns"
            :data-source="one.tableData"
          >
            <div slot="contact_name" slot-scope="row">
              <img :src="row.avatar" style="width: 35px;">
              {{ row.contact_name }}
            </div>
            <div slot="employee_name" slot-scope="row">
              <div class="belongTo">
                <a-icon type="user"/>
                {{ row.employee_name }}
              </div>
            </div>
            <div slot="send_status" slot-scope="row">
              <span>
                {{ sendStatusMap[row.send_status] }}
              </span>
            </div>
            <div slot="room_name" slot-scope="row">
              {{ row.room_name }}
            </div>
            <div slot="is_join_room" slot-scope="row">
              {{ row.is_join_room === 0 ? '未入群' : '已入群' }}
            </div>
            <div slot="action">
              <template>
                <a-button type="link" @click="detail">详情</a-button>
              </template>
            </div>
          </a-table>

          <!--    成员详情表格      -->
          <a-table
            v-else
            :columns="memberColumns"
            :data-source="memberTable"
          >
            <div slot="name" class="clientColumn flex" slot-scope="row">
              <img class="clientImg mr6" :src="row.avatar">
              {{ row.name }}
            </div>
            <div slot="contact_num" slot-scope="row">
              {{ row.contact_num }}
            </div>
            <div slot="">

            </div>
            <div slot="action">
              <a-button type="link" v-if="false">提醒发送</a-button>
              <a-popover placement="topLeft" v-else>
                <template slot="content">该员工已发送该消息，无法提醒发送</template>
                <span class="warn_send">提醒发送</span>
              </a-popover>
            </div>
          </a-table>
        </div>
      </a-card>
    </div>
  </div>
</template>
<script>
import { labelShow, labelContactShow } from '@/api/workRoom'

export default {
  data () {
    return {
      labelCol: { span: 4 },
      wrapperCol: { span: 14 },
      clientTable: true,
      form: {
        type: ''
      },
      filter: {
        contact: {
          contact_name: '',
          wx_user_id: '',
          send_status: '',
          is_join_room: '',
          room_id: ''
        },
        staff: {
          wx_user_id: '',
          is_send: '',
          task_type: ''
        }
      },
      // 客户详情表格
      one: {
        columns: [
          {
            title: '客户',
            scopedSlots: { customRender: 'contact_name' }
          },
          {
            title: '所属成员',
            scopedSlots: { customRender: 'employee_name' }
          },
          {
            title: '送达状态',
            scopedSlots: { customRender: 'send_status' }
          },
          {
            title: '邀请群聊',
            scopedSlots: { customRender: 'room_name' }
          },
          {
            title: '是否入群',
            scopedSlots: { customRender: 'is_join_room' }
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'action' }
          }
        ],
        tableData: []
      },
      // 成员详情表格表头
      memberColumns: [
        {
          title: '成员',
          scopedSlots: { customRender: 'name' }
        },
        {
          title: '客户数',
          scopedSlots: { customRender: 'contact_num' }
        },
        {
          title: '已邀请',
          scopedSlots: { customRender: 'invite_num' }
        },
        {
          title: '收到任务条数',
          scopedSlots: { customRender: 'member' }
        },
        {
          title: '发送状态',
          scopedSlots: { customRender: 'member' }
        },
        {
          key: 'action',
          dataIndex: 'action',
          title: '操作',
          width: '200px',
          scopedSlots: { customRender: 'action' }
        }
      ],
      //  成员详情表格表格
      memberTable: [],

      // 详情数据
      showData: {
        employees: {},
        rooms: [{
          name: '',
          id: '',
          room_max: '',
          contact_num: ''
        }],
        join_room_num: 0,
        no_join_room_num: 0,
        invite_num: 0,
        no_invite_num: 0,
        send_num: 0,
        no_send_num: 0
      },
      sendStatusMap: {
        0: '未收到',
        1: '已收到',
        2: '客户已不是好友',
        3: '客户接受已达上限'
      }
    }
  },
  mounted () {
    this.getListData()
    this.getData()
  },
  methods: {
    // 获取详情数据
    getData () {
      labelShow({
        id: this.$route.query.id
      }).then(res => {
        this.showData.employees = res.data.employees
        this.showData.rooms = res.data.rooms
      })
    },

    // 获取列表详情数据
    getListData () {
      labelContactShow({
        page: '',
        perPage: '',
        id: this.$route.query.id,
        type: 1
      }).then(res => {
        this.one.tableData = res.data.list
      })

      labelContactShow({
        page: '',
        perPage: '',
        id: this.$route.query.id,
        type: 2
      }).then(res => {
        this.memberTable = res.data.list
      })
    },

    resetContact () {
      this.filter.contact = {
        contact_name: '',
        wx_user_id: '',
        send_status: '',
        is_join_room: '',
        room_id: ''
      }
    },

    resetStaff () {
      this.filter.staff = {
        wx_user_id: '',
        is_send: '',
        task_type: ''
      }
    },

    callback (key) {
      if (key == 2) {
        this.clientTable = false
      } else {
        this.clientTable = true
      }
    },
    detail () {
      this.$router.push({ path: '/roomTagPull/clientDetails' })
    }
  },
  watch: {
    'filter.contact': {
      handler () {
        labelContactShow({
          id: this.$route.query.id,
          type: 1,
          ...this.filter.contact,
          page: '',
          perPage: ''
        }).then(res => {
          this.one.tableData = res.data.list
        })
      },
      deep: true
    },
    'filter.staff': {
      handler () {
        labelContactShow({
          id: this.$route.query.id,
          type: 2,
          ...this.filter.staff,
          page: '',
          perPage: ''
        }).then(res => {
          this.memberTable = res.data.list
        })
      },
      deep: true
    }
  }
}
</script>
<style>
.pageTop {
  width: 100%;
  display: flex;
}

.topright {
  width: calc(100% - 383px);
  min-width: 900px;
  margin-left: 10px;
}

.topleft span {
  font-size: 12px;
  line-height: 17px;
  color: rgba(0, 0, 0, .65);
}

.conditionTitle {
  font-weight: 700;
  font-size: 16px;
  line-height: 22px;
  color: #222;
}

.tlbLeft {
  float: left;
  line-height: 52px;
}

.tlbRight {
  float: left;
  background: #fbfbfb;
  border: 1px solid #eee;
  box-sizing: border-box;
  border-radius: 1px;
  padding: 9px 14px 6px 12px;
  width: 280px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.tlbr_left {
  display: flex;
}

.groupImg {
  width: 32px;
  height: 32px;
}

.tlbrl_left {
  margin-left: 5px;
}

.tlbrl_left div {
  font-weight: 700;
  font-size: 13px;
  line-height: 22px;
  color: #222;
  overflow: hidden;
  margin-top: -4px;
}

.tlbrl_left .tlbrl_left_bottom span {
  font-weight: normal;
  margin-left: 5px;
}

.tlbrl_left .tlbrl_left_bottom span:first-child {
  margin-left: 0;
}

.qrCode_icon {
  font-size: 18px;
  color: #C0C0C0;
  cursor: pointer;
}

.group_img {
  width: 100px;
  height: 100px;
}

.tr_content {
  height: 99px;
  width: 100%;
  background: #fbfdff;
  border: 1px solid #daedff;
  box-sizing: border-box;
  border-radius: 1px;
  display: flex;
  align-items: center;
  margin-top: 10px;
}

.trc_middle {
  width: 1px;
  background: #EFEFEF;
  height: 50px;
}

.trc_content {
  text-align: center;
}

.typeQuantity {
  font-weight: 600;
  font-size: 28px;
  line-height: 39px;
  color: #222;
}

.trc_type i {
  margin-left: 5px;
}

.trc_type {
  font-size: 13px;
  line-height: 18px;
  color: rgba(0, 0, 0, .45);
}

.pageContent {
  margin-top: 16px;
}

.pageContent .ant-card-body {
  padding: 10px 24px 24px 24px;
}

.clientNumber {
  font-weight: 700;
  font-size: 14px;
  line-height: 22px;
  color: rgba(18, 18, 22, .85);
}

.formStyle .ant-form {
  display: flex;
  flex-wrap: wrap;
}

.formStyle .ant-form-item {
  width: 270px;
}

.formStyle .ant-form-item-label {
  width: 35%;
}

.formStyle .ant-form-item-control-wrapper {
  width: 65%;
}

.formStyle .formBtnRow {
  width: 98px;
  margin-left: 25px;
}

.formLeft {
  display: flex;
  flex-wrap: wrap;
}

.tasksNum {
  margin-left: 25px;
}

.clientColumn {
  display: flex;
}

.clientImg {
  width: 36px;
  height: 36px;
}

.clientName {
  margin-left: 5px;
  font-weight: 700;
  font-size: 13px;
  line-height: 18px;
  color: #333;
}

.belongTo {
  padding: 2px 10px 4px;
  font-size: 13px;
  line-height: 22px;
  color: #222;
  opacity: .85;
  background: #fff;
  border: 1px solid #d9d9d9;
  box-sizing: border-box;
  border-radius: 4px;
  width: 85px;
}

.belongTo i {
  margin-right: 5px;
  font-size: 15px;
}

.joinIs {
  color: #d53e3e;
}

.warn_send {
  color: rgba(0, 0, 0, .25);
}
</style>
