<template>
  <div class="work-record">
    <a-card class="mb16">
      <div class="filter-form">
        <!--        接替员工-->
        <div class="item">
          <label>接替员工：</label>
          <div class="input">
            <a-select placeholder="请选择企业员工" :showSearch="true" @change="selectStaff" v-model="aksParams.staffName">
              <a-select-option
                v-for="(item,index) in staffArray"
                :key="index"
                :value="item.name"
              >{{ item.name }}
              </a-select-option>
            </a-select>
          </div>
        </div>
        <div class="item">
          <label>分配时间：</label>
          <div class="input"><a-range-picker v-model="showTimeSearch" @change="retrievalDate"/></div>
        </div>
        <div class="item">
          <a-button @click="resetBtn">重置</a-button>
        </div>
      </div>
    </a-card>
    <a-card>
      <div class="f-blod ml6 mb16">
        共{{ table.data.length }}个已分配客户
      </div>
      <div class="table">
        <a-table :columns="table.columns" :data-source="table.data">
          <div slot="employee" slot-scope="text">
            <a-tag><a-icon type="user" />{{ text }}</a-tag>
          </div>
          <div slot="operation">
            <a>客户详情</a>
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
      // 展示分配时间
      showTimeSearch: [],
      // 员工数组
      staffArray: [],
      // 请求数据
      aksParams: {
        // 模式  在职已分配客户
        mode: 3,
        // 接替员工
        employeeId: '',
        // 分配时间开始
        createTimeStart: '',
        // 分配时间结束
        createTimeEnd: ''
      },
      // 表格数据
      table: {
        columns: [
          {
            key: 'name',
            dataIndex: 'name',
            title: '客户名称'
          },
          {
            key: 'corpName',
            dataIndex: 'corpName',
            title: '客户企业'
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
        data: []
      }
    }
  },
  created () {
    this.getTableData(this.aksParams)
    this.getNumberData()
  },
  methods: {
    // 重置
    resetBtn () {
      this.aksParams = {}
      this.aksParams.mode = 3
      this.showTimeSearch = []
      this.staffName = ''
      this.getTableData(this.aksParams)
    },
    // 获取选中的员工数据
    selectStaff (e) {
      this.staffArray.forEach((item) => {
        if (e == item.name) {
          this.aksParams.employeeId = item.wxUserId
        }
      })
      this.getTableData(this.aksParams)
    },
    // 获取员工数据
    getNumberData () {
      department().then((res) => {
        this.staffArray = res.data.employee
      })
    },
    // 检索日期
    retrievalDate (date, dateString) {
      this.aksParams.createTimeStart = dateString[0]
      this.aksParams.createTimeEnd = dateString[1]
      this.getTableData(this.aksParams)
    },
    //  获取表格数据
    getTableData (params) {
      logApi(params).then((res) => {
        this.table.data = res.data
      })
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
    margin-bottom: 13px;
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
.btn-box {
  .ant-btn {
    margin-right: 10px;
  }
}
</style>
