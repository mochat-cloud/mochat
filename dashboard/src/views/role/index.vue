<template>
  <div>
    <a-card title="数据筛选">
      <a-row>
        <a-col :span="6">
          <a-input v-model="search" placeholder="请输入角色名称"></a-input>
        </a-col>
        <a-col :span="6" :offset="1">
          <a-button v-permission="'/role/index@search'" type="primary" style="marginRight:10px" @click="searchData">查询</a-button>
          <a-button @click="reset">重置</a-button>
        </a-col>
      </a-row>
      <div class="table-wrapper">
        <div class="top-btn">
          <a-button v-permission="'/role/index@add'" class="batch" type="primary" @click="add">添加</a-button>
        </div>
        <a-table
          bordered
          rowKey="roleId"
          :columns="columns"
          :data-source="tableData"
          :pagination="pagination"

          @change="handleTableChange"
        >
          <div slot="employeeNum" slot-scope="text, record">
            <template>
              <a-button v-permission="'/role/index@checkMember'" type="link" @click="showMemberData(record.roleId)">{{ text }}</a-button>
            </template>
          </div>
          <div slot="status" slot-scope="text, record">
            <template>
              <a-checkbox v-permission="'/role/index@use'" :checked="judgeChecked(text)" @change="checkChange($event, record)"></a-checkbox>
            </template>
          </div>
          <div slot="action" slot-scope="text, record">
            <template>
              <a-button v-permission="'/role/permissionShow'" type="link" @click="detail(record.roleId)">设置权限</a-button>
              <a-button v-permission="'/role/index@edit'" type="link" @click="edit(record.roleId)">编辑</a-button>
              <a-button v-permission="'/role/index@copy'" type="link" @click="copyRole(record)">复制权限</a-button>
              <a-button v-permission="'/role/index@delete'" type="link" v-if="record.employeeNum == 0" @click="deleteRole(record.roleId)">删除</a-button>
            </template>
          </div>
        </a-table>
      </div>
    </a-card>
    <a-modal
      title="角色信息"
      :maskClosable="false"
      :width="600"
      :visible="roleInfoShow"
      :confirm-loading="confirmLoading"
      @ok="roleSubmit"
      @cancel="roleInfoShow = false"
    >
      <a-form-model
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 20}"
        :model="formData"
        :rules="rules"
        ref="ruleForm">
        <div class="name">
          <span class="star">*</span>
          <a-form-model-item class="form-item" label="角色名称" prop="name">
            <a-input v-model="name" class="input" :maxLength="8" placeholder="请输入角色名称"></a-input>
          </a-form-model-item>
        </div>

        <a-form-model-item label="角色描述">
          <a-textarea v-model="remarks" class="input" placeholder="请输入角色描述" :rows="4" />
        </a-form-model-item>
        <a-form-model-item label="部门数据全览">
          <a-radio-group v-model="dataPermission">
            <a-radio :value="1">
              是
            </a-radio>
            <a-radio :value="2">
              否
            </a-radio>
          </a-radio-group>
        </a-form-model-item>
      </a-form-model>
    </a-modal>

    <a-modal
      title="角色人员"
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
import { roleList, roleShowEmployee, roleStore, statusUpdate, roleUpdate, roleDelete, roleDetail } from '@/api/role'
const columns = [
  {
    title: '角色名称',
    dataIndex: 'name'
  },
  {
    title: '角色人员',
    dataIndex: 'employeeNum',
    scopedSlots: { customRender: 'employeeNum' }
  },
  {
    title: '角色描述',
    dataIndex: 'remarks',
    ellipsis: true
  },
  {
    title: '更新时间',
    dataIndex: 'updatedAt'
  },
  {
    title: '启用',
    dataIndex: 'status',
    scopedSlots: { customRender: 'status' }
  },
  {
    title: '操作',
    dataIndex: 'action',
    width: '150px',
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
    title: '邮箱地址',
    dataIndex: 'email'
  },
  {
    title: '部门',
    dataIndex: 'department'
  }
]
export default {
  data () {
    const createValidate = (callback, value, message) => {
      if (!value) {
        return callback(new Error(message))
      } else {
        callback()
      }
    }
    const createFunc = (func, change) => {
      return {
        validator: func,
        trigger: change || 'blur'
      }
    }
    const vname = (rule, value, callback) => {
      value = this.name
      createValidate(callback, value, '请输入角色名称')
    }
    return {
      columns,
      memberColumns,
      tableData: [],
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      search: '',
      roleInfoShow: false,
      formData: {},
      name: '',
      remarks: '',
      dataPermission: '',
      rules: {
        name: createFunc(vname, 'change')
      },
      roleInfoType: 'add',
      copyRoleId: '',
      editRoleId: '',
      memberShow: false,
      memberData: [],
      memberId: '',
      memberPagination: {
        total: 0,
        current: 1,
        pageSize: 10
      },
      confirmLoading: false
    }
  },
  watch: {
    roleInfoShow (value) {
      if (!value) {
        this.name = ''
        this.remarks = ''
        this.dataPermission = ''
      }
    }
  },
  created () {
    this.getTableData()
  },
  methods: {
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    },
    handleMemberChange ({ current, pageSize }) {
      this.memberPagination.current = current
      this.memberPagination.pageSize = pageSize
      this.getMemberData()
    },
    searchData () {
      this.pagination.current = 1
      this.getTableData()
    },
    async getTableData () {
      const params = {
        name: this.search,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      try {
        const { data: { page: { total }, list } } = await roleList(params)
        this.pagination.total = Number(total)
        this.tableData = list
      } catch (e) {
        console.log(e)
      }
    },
    async statusChange (param) {
      try {
        await statusUpdate(param)
        this.getTableData()
      } catch (e) {
        console.log(e)
      }
    },
    checkChange (e, record) {
      const { roleId, status, employeeNum } = record
      if (status == 1) {
        if (employeeNum > 0) {
          this.$message.warn('此角色不可以禁用')
        } else {
          this.statusChange({ roleId, status: 2 })
          this.$message.success('角色已禁用')
        }
      } else {
        this.statusChange({ roleId, status: 1 })
        this.$message.success('角色已启用')
      }
    },
    judgeChecked (text) {
      return text == 1
    },
    detail (roleId) {
      this.$router.push({ path: '/role/permissionShow', query: { roleId } })
    },
    showMemberData (roleId) {
      this.memberShow = true
      this.memberId = roleId
      this.getMemberData()
    },
    async getMemberData () {
      const params = {
        roleId: this.memberId,
        page: this.memberPagination.current,
        perPage: this.memberPagination.pageSize
      }
      try {
        const { data: { page: { total }, list } } = await roleShowEmployee(params)
        this.memberPagination.total = Number(total)
        this.memberData = list
      } catch (e) {
        console.log(e)
      }
    },
    reset () {
      this.search = ''
    },
    add () {
      this.roleInfoType = 'add'
      this.roleInfoShow = true
    },
    async edit (roleId) {
      this.roleInfoType = 'edit'
      this.roleInfoShow = true
      this.editRoleId = roleId
      const { data: { name, remarks, dataPermission } } = await roleDetail({ roleId })
      this.name = name
      this.remarks = remarks
      this.dataPermission = dataPermission
    },
    copyRole (record) {
      this.copyRoleId = record.roleId
      this.roleInfoShow = true
      this.roleInfoType = 'copy'
    },
    deleteRole (roleId) {
      this.$confirm({
        title: '提示',
        content: '确认删除此角色吗？',
        onOk: async () => {
          try {
            await roleDelete({ roleId })
            this.getTableData()
          } catch (e) {
            console.log(e)
          }
        },
        onCancel () {}
      })
    },
    roleSubmit () {
      this.$refs.ruleForm.validate(async valid => {
        if (valid) {
          try {
            let message = ''
            const params = {
              name: this.name,
              remarks: this.remarks,
              dataPermission: this.dataPermission
            }
            this.confirmLoading = true
            if (this.roleInfoType == 'add') {
              await roleStore(params)
              message = '添加成功'
            } else if (this.roleInfoType == 'copy') {
              params.roleId = this.copyRoleId
              await roleStore(params)
              message = '复制成功'
            } else {
              params.roleId = this.editRoleId
              await roleUpdate(params)
              message = '修改成功'
            }
            this.$message.success(message)
            this.roleInfoShow = false
            this.confirmLoading = false
            this.getTableData()
          } catch (e) {
            this.confirmLoading = false
            console.log(e)
          }
        } else {
          console.log('error submit!!')
          return false
        }
      })
    }
  }
}
</script>

<style lang="less" scoped>
.top-btn{
  display: flex;
  justify-content: flex-end;
  margin-bottom: 10px;
  .batch{
    // margin-right: 20px;
  }
}
.name {
  display: flex;
}
.star {
  flex: 0 0 15px;
  margin-right: -20px;
  font-size: 20px;
  color: red;
  padding-top: 8px;
}
.form-item {
  flex: 1
}
.input {
  max-width: 400px;
}
</style>
