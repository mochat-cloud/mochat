<template>
  <div class="resign-allot-record">
    <a-card class="mb16">
      <a-tabs type="card" default-active-key="0" @change="tabSwitch">
        <a-tab-pane key="0" tab="已分配客户"></a-tab-pane>
        <a-tab-pane key="1" tab="已分配群聊"></a-tab-pane>
      </a-tabs>
      <div class="filter-form">
        <!--    搜索用户名称/搜索群名称    -->
        <div class="item">
          <label v-if="currentTab==0">客户昵称：</label>
          <label v-else>群名称：</label>
          <div class="input">
            <a-input-search
              placeholder="请输入要搜索的客户"
              v-model="paramsAskData.name"
              @search="retrievalTable"
              :allowClear="true"
              @change="emptyNickIpt"
            ></a-input-search>
          </div>
        </div>
        <!--    接替员工    -->
        <div class="item">
          <label>接替员工：</label>
          <div class="input">
            <a-select placeholder="请选择企业员工" :showSearch="true" @change="selectStaff" v-model="paramsAskData.staffName">
              <a-select-option
                v-for="(item,index) in staffArray"
                :key="index"
                :value="item.name"
              >{{ item.name }}
              </a-select-option>
            </a-select>
          </div>
        </div>
        <!--        分配时间-->
        <div class="item">
          <label>分配时间：</label>
          <div class="input">
            <a-range-picker @change="retrievalDate" v-model="showTimeSearch"/>
          </div>
        </div>
        <div class="item">
          <a-button @click="resetBtn">重置</a-button>
        </div>
      </div>
    </a-card>
    <a-card>
      <div class="flex">
        <div class="info-box">
          <span class="f-blod">共{{ table.data.length }}个待分配客户</span>
        </div>
      </div>
      <div class="table mt20">
        <a-table
          :columns="currentTab==0?table.clientCol:table.groupCol"
          :data-source="table.data">
          <!--          已分配客户-->
          <div slot="employee" slot-scope="text">
            <a-tag><a-icon type="user" />{{ text }}</a-tag>
          </div>
          <div slot="operation">
            <a>客户详情</a>
          </div>
          <!--          已分配群聊 -->
          <div slot="handGroup">
            <a>客户详情1</a>
          </div>
        </a-table>
      </div>
    </a-card>
  </div>
</template>

<script>
import { logApi, department } from '@/api/contactTransfer'
export default {
  data () {
    return {
      // 面板切换
      currentTab: 0,
      // 展示选择的时间
      showTimeSearch: [],
      // 员工数组
      staffArray: [],
      // 请求数据
      paramsAskData: {
        mode: 1,
        name: '',
        employeeId: '',
        createTimeStart: '',
        createTimeEnd: ''
      },
      // 表格
      table: {
        clientCol: [
          {
            key: 'name',
            dataIndex: 'name',
            title: '客户名称'
          },
          {
            key: 'employee',
            dataIndex: 'employee',
            title: '接替员工',
            scopedSlots: { customRender: 'employee' }
          },
          {
            key: 'state',
            dataIndex: 'state',
            title: '转接状态'
          },
          {
            key: 'createTime',
            dataIndex: 'createTime',
            title: '分配时间'
          },
          {
            key: 'operation',
            dataIndex: 'operation',
            title: '操作',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        groupCol: [
          {
            key: 'name',
            dataIndex: 'name',
            title: '群聊名称'
          },
          {
            key: 'employee',
            dataIndex: 'employee',
            title: '接替员工',
            scopedSlots: { customRender: 'employee' }
          },
          {
            key: 'roomNum',
            dataIndex: 'roomNum',
            title: '群人数'

          },
          {
            key: 'createTime',
            dataIndex: 'createTime',
            title: '分配时间'
          },
          {
            key: 'handGroup',
            dataIndex: 'handGroup',
            title: '操作',
            scopedSlots: { customRender: 'handGroup' }
          }
        ],
        data: []
      }
    }
  },
  created () {
    this.getClientData(this.paramsAskData)
    this.getNumberData()
  },
  methods: {
    // 重置
    resetBtn () {
      this.paramsAskData = {}
      this.showTimeSearch = []
      if (this.currentTab == 0) {
        this.paramsAskData.mode = 1
        this.getClientData(this.paramsAskData)
      } else if (this.currentTab == 1) {
        this.paramsAskData.mode = 2
        this.getGroupData(this.paramsAskData)
      }
    },
    // 获取员工数据
    getNumberData () {
      department().then((res) => {
        this.staffArray = res.data.employee
      })
    },
    // 获取选中的员工数据
    selectStaff (e) {
      this.staffArray.forEach((item) => {
        if (e == item.name) {
          this.paramsAskData.employeeId = item.wxUserId
        }
      })
      if (this.currentTab == 0) {
        this.getClientData(this.paramsAskData)
      } else {
        this.getGroupData(this.paramsAskData)
      }
    },
    // 客户/群聊搜索框
    retrievalTable () {
      if (this.currentTab == 0) {
        this.getClientData(this.paramsAskData)
      } else {
        this.getGroupData(this.paramsAskData)
      }
    },
    // 清空搜索框
    emptyNickIpt () {
      if (this.paramsAskData.name == '') {
        if (this.currentTab == 0) {
          this.getClientData(this.paramsAskData)
        } else {
          this.getGroupData(this.paramsAskData)
        }
      }
    },
    // 筛选时间
    retrievalDate (date, dateString) {
      this.paramsAskData.createTimeStart = dateString[0]
      this.paramsAskData.createTimeEnd = dateString[1]
      if (this.currentTab == 0) {
        this.getClientData(this.paramsAskData)
      } else {
        this.getGroupData(this.paramsAskData)
      }
    },
    // 获取已分配客户  0
    getClientData (params) {
      logApi(params).then((res) => {
        this.table.data = res.data
      })
    },
    // 获取已分配群聊  1
    getGroupData (params) {
      logApi(params).then((res) => {
        this.table.data = res.data
      })
    },
    // 面板切换
    tabSwitch (e) {
      this.currentTab = e
      // 清空表单
      this.table.data = []
      // 清空请求数据
      this.paramsAskData = {}
      // 清空时间展示
      this.showTimeSearch = []
      if (this.currentTab == 0) {
        this.paramsAskData.mode = 1
        this.getClientData(this.paramsAskData)
      } else if (this.currentTab == 1) {
        this.paramsAskData.mode = 2
        this.getGroupData(this.paramsAskData)
      }
    }
  }
}
</script>

<style lang="less" scoped>
.filter-form {
  display: flex;
  align-items: center;
  flex-wrap: wrap;

  .item {
    min-width: 266px;
    margin-top: 16px;
    padding-right: 33px;
    box-sizing: content-box;
    display: flex;
    justify-content: flex-start;
    align-items: center;

    .input {
      width: 208px;
      padding-left: 12px;
    }

    .ant-select {
      width: 100%;
    }
  }
}

.info-box {
  flex: 1;
}
</style>
