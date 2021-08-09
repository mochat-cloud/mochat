<template>
  <div class="wrapper">
    <a-card>
      <div class="syn">
        <p class="time">最后一次同步时间：{{ syncTime }}</p>
        <a-button v-permission="'/department/index@sync'" @click="syncEmployeeDefine">同步企业微信通讯录</a-button>
      </div>
      <a-form :label-col="{ span: 7 }" :wrapper-col="{ span: 14 }">
        <a-row :gutter="16">
          <a-col :lg="8">
            <a-form-item
              label="组织名称">
              <a-input v-model="name"></a-input>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item
              label="上级组织名称">
              <a-input v-model="parentName"></a-input>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item label="">
              <a-button v-permission="'/department/index@search'" type="primary" @click="search">查询</a-button>
              <a-button style="marginLeft:5px" @click="reset">重置</a-button>
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>
      <a-table
        style="marginTop: 20px"
        bordered
        rowKey="departmentId"
        :columns="columns"
        :data-source="tableData"
        :pagination="pagination"
        :indentSize="0"
        :expandIconColumnIndex="3"
        :expandIcon="expandIcon"
        @change="handleTableChange">
        <div slot="action" slot-scope="text, record" class="action">
          <template>
            <a-button v-permission="'/department/index@check'" type="link" @click="checkMember(record.departmentId)">查看成员</a-button>
          </template>
        </div>
      </a-table>
    </a-card>
    <a-modal
      title="查看人员"
      :maskClosable="false"
      :width="600"
      :visible="memberShow"
      :footer="null"
      @cancel="memberShow = false"
    >
      <a-table
        bordered
        rowKey="employeeId"
        :columns="memberColumns"
        :data-source="memberData"
        :pagination="memberPagination"
        @change="handleMemberChange"
      >
      </a-table>
    </a-modal>
  </div>
</template>

<script>
import { syncEmployee, syncTime } from '@/api/workEmployee'
import { departmentList, showEmployee } from '@/api/department'
const columns = [
  {
    align: 'center',
    title: '序号',
    dataIndex: 'departmentPath'
  },
  {
    align: 'center',
    title: '组织架构名称',
    dataIndex: 'name'
  },
  {
    align: 'center',
    title: '部门级别',
    dataIndex: 'level'
  },
  {
    align: 'center',
    title: '操作',
    dataIndex: 'action',
    scopedSlots: { customRender: 'action' }
  }
]
const memberColumns = [
  {
    title: '姓名',
    dataIndex: 'employeeName'
  },
  {
    title: '手机号码',
    dataIndex: 'phone'
  },
  {
    title: '角色',
    dataIndex: 'roleName'
  }
]
export default {
  data () {
    return {
      columns: columns,
      memberColumns: memberColumns,
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      name: '',
      parentName: '',
      syncTime: '',
      tableData: [],
      memberShow: false,
      memberData: [],
      memberPagination: {
        total: 0,
        current: 1,
        pageSize: 10
      },
      departmentId: ''
    }
  },
  created () {
    this.getSyncTime()
    this.getTableData()
  },
  methods: {
    async getTableData () {
      const params = {
        name: this.name,
        parentName: this.parentName,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      try {
        const { data: { page: { total }, list } } = await departmentList(params)
        this.pagination.total = total
        this.tableData = list
      } catch (e) {
        console.log(e)
      }
    },
    // 同步企业微信
    async syncEmployeeDefine () {
      try {
        await syncEmployee()
        this.getTableData()
        this.getSyncTime()
        this.$message.success('同步成功')
      } catch (e) {
        console.log(e)
      }
    },
    async getSyncTime () {
      const { data } = await syncTime()
      this.syncTime = data.syncTime
    },
    search () {
      this.pagination.current = 1
      this.getTableData()
    },
    reset () {
      this.name = ''
      this.parentName = ''
    },
    expandIcon (props) {
      if (!props.record.children) {
        return
      }
      if (props.record.children.length > 0) {
        if (props.expanded) {
          return <a class="expand-wrapper" onClick={e => {
            props.onExpand(props.record, e)
          }}><span class="expand">收起</span></a>
        } else {
          return <a class="expand-wrapper" onClick={e => {
            props.onExpand(props.record, e)
          }}><span class="expand">展开</span></a>
        }
      }
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    },
    checkMember (departmentId) {
      this.departmentId = departmentId
      this.getMemberData()
      this.memberShow = true
    },
    handleMemberChange ({ current, pageSize }) {
      this.memberPagination.current = current
      this.memberPagination.pageSize = pageSize
      this.getMemberData()
    },
    async getMemberData (id) {
      const params = {
        departmentId: this.departmentId,
        page: this.memberPagination.current,
        perPage: this.memberPagination.pageSize
      }
      try {
        const { data: { page: { total }, list } } = await showEmployee(params)
        this.memberPagination.total = total
        this.memberData = list
      } catch (e) {
        console.log(e)
      }
    }
  }
}
</script>

<style lang="less" scoped>
.syn {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: flex-end;
  margin-bottom: 20px;
  .time {
    margin: 0 20px 0 0;
  }
}
.expand-wrapper {
  width: 100px;
  position: relative;
  display: inline-block;
  .expand {
    position: absolute;
    top: 10px;
    left: 80px;
    display: inline-block;
    width: 50px;
  }
}
</style>
