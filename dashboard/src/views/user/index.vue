<template>
  <div>
    <a-card>
      <div class="table-page-search-wrapper">
        <a-form>
          <a-row :gutter="16">
            <a-col :lg="8">
              <a-form-item
                label="手机号码："
                :labelCol="{lg: {span: 7} }"
                :wrapperCol="{lg: {span: 14} }">
                <a-input v-model="screenData.phone"></a-input>
              </a-form-item>
            </a-col>
            <a-col :lg="8">
              <a-form-item
                label="状态："
                :labelCol="{lg: {span: 7} }"
                :wrapperCol="{lg: {span: 14} }">
                <a-select v-model="screenData.status">
                  <a-select-option v-for="item in statusList" :key="item.value">
                    {{ item.label }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </a-col>
            <a-col :lg="8">
              <a-form-item
                label=""
                :labelCol="{lg: {span: 7} }"
                :wrapperCol="{lg: {span: 14} }">
                <a-button v-permission="'/user/index@search'" type="primary" @click="search">查询</a-button>
                <a-button style="marginLeft:5px" @click="() => { this.screenData = {} }">重置</a-button>
              </a-form-item>
            </a-col>
          </a-row>
        </a-form>
        <div class="alter">
          <a-icon theme="twoTone" type="question-circle" />
          <span>已启用<span style="color: #4BA9FF">{{ usingNum }}</span> 项 未启用:<span style="color:#757E82">{{ forbiddenNum }}</span>人</span>
          <a-button type="link" @click="statusModal">启用</a-button>
          <a-modal
            :visible="statusModalDis"
            @cancel="() => {this.statusModalDis = false}"
          >
            <p>是否确认启用</p>
            <template slot="footer">
              <a-button key="back" @click="() => {this.statusModalDis = false}">
                取消
              </a-button>
              <a-button @click="status" key="submit" type="primary">
                确定
              </a-button>
            </template>
          </a-modal>
        </div>
      </div>
      <div class="table-wrapper">
        <a-button v-permission="'/user/index@add'" type="primary" @click="() => {this.modalVisible = true}"> + 添加</a-button>
        <a-table
          :columns="columns"
          :data-source="tableData"
          :rowKey="record => record.userId"
          :row-selection="{ selectedRowKeys: userIdList, onChange: onSelectChange }"
          :pagination="pagination"
          @change="handleTableChange"
        >
          <div slot="department" slot-scope="text, record">
            <div v-for="item in record.department" :key="item.departmentId">{{ item.departmentName }}</div>
          </div>
          <div slot="action" slot-scope="text, record">
            <template>
              <a-button v-permission="'/user/index@edit'" type="link" @click="editSubManagement(record.userId)">修改</a-button>
            </template>
          </div>
        </a-table>
      </div>
      <a-modal
        :visible="modalVisible"
        @cancel="reset"
        title="基础信息">
        <a-form-model ref="ruleForm" :model="userData" :rules="rules" :label-col="{ span: 6 }" :wrapper-col="{ span: 14 }">
          <a-form-model-item label="员工姓名:" prop="userName">
            <a-input v-model="userData.userName"></a-input>
          </a-form-model-item>
          <a-form-model-item label="手机号码：" prop="phone">
            <a-input v-model="userData.phone" @blur="phoneSelect"></a-input>
          </a-form-model-item>
          <a-form-model-item label="性别：" prop="gender">
            <a-radio-group v-model="userData.gender">
              <a-radio :value="1">
                男
              </a-radio>
              <a-radio :value="2">
                女
              </a-radio>
            </a-radio-group>
          </a-form-model-item>
          <a-form-model-item label="状态" prop="status">
            <a-radio-group v-model="userData.status">
              <a-radio :value="1">
                正常
              </a-radio>
              <a-radio :value="2">
                禁用
              </a-radio>
              <a-radio :value="0">
                未启用
              </a-radio>
            </a-radio-group>
          </a-form-model-item>
          <div class="role-box">
            <a-form-model-item label="部门" >
              <a-tooltip v-if="departmentList.length !== 0" v-for="item in departmentList" :key="item.workDepartmentId">
                <template slot="title">
                  {{ item.workDepartmentName }}
                </template>
                <div class="name" >{{ item.workDepartmentName }}</div>
              </a-tooltip>
            </a-form-model-item>
            <a-form-model-item label="角色" prop="roleId">
              <a-select v-model="userData.roleId">
                <a-select-option v-for="item in roleList" :key="item.roleId" :disabled="item.roleId == 0">
                  {{ item.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </div>
        </a-form-model>
        <template slot="footer">
          <a-button key="submit" :loading="btnLoading" type="primary" @click="addSubManagement">
            确定
          </a-button>
          <a-button @click="reset" key="back">
            取消
          </a-button>
        </template>
      </a-modal>
    </a-card>
  </div>
</template>

<script>
import { subManagementList, addSubManagement, getSubManagement, editSubManagement, changeStatus, selectByPhone, selectRole } from '@/api/user'
export default {
  data () {
    return {
      btnLoading: false,
      screenData: {},
      modalVisible: false,
      columns: [
        {
          title: '企业成员',
          dataIndex: 'userName',
          align: 'center'
        },
        {
          title: '所属部门',
          dataIndex: 'department',
          align: 'center',
          scopedSlots: { customRender: 'department' }
        },
        {
          title: '职务',
          dataIndex: 'roleName',
          align: 'center'
        },
        {
          title: '手机号（登录账号）',
          dataIndex: 'phone',
          align: 'center'
        },
        {
          title: '状态',
          dataIndex: 'statusText',
          align: 'center'
        },
        {
          title: '时间',
          dataIndex: 'createdAt',
          align: 'center'
        },
        {
          title: '操作',
          dataIndex: 'action',
          align: 'center',
          scopedSlots: { customRender: 'action' }
        }
      ],
      tableData: [],
      userData: {
        gender: 1,
        status: 0
      },
      // 启用数量
      usingNum: '',
      forbiddenNum: '',
      // 修改时Id
      userId: '',
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      statusList: [
        {
          label: '未启用',
          value: 0
        },
        {
          label: '正常',
          value: 1
        },
        {
          label: '禁用',
          value: 2
        }
      ],
      userIdList: [],
      rules: {
        userName: [
          { required: true, message: '请输入员工姓名', trigger: 'blur' }
        ],
        phone: [{ required: true, message: '请输入手机号', trigger: 'blur' }],
        gender: [{ required: true, message: '请选择性别', trigger: 'change' }],
        status: [{ required: true, message: '请选择状态', trigger: 'change' }],
        roleId: [{ required: true, message: '请选择角色', trigger: 'change' }]
      },
      statusModalDis: false,
      departmentList: [],
      roleList: []
    }
  },
  created () {
    this.getTableData()
    this.getSelectRole()
  },
  methods: {
    getTableData () {
      const params = {
        phone: this.screenData.phone,
        status: this.screenData.status,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      subManagementList(params).then(res => {
        this.usingNum = res.data.normalNum
        this.forbiddenNum = res.data.notEnabledNum
        this.tableData = res.data.list
        this.pagination.total = res.data.page.total
      })
    },
    // 筛选
    search () {
      this.pagination.current = 1
      this.getTableData()
    },
    // 表格选中
    onSelectChange (value) {
      this.userIdList = value
    },
    // 启用弹窗
    statusModal () {
      if (this.userIdList.length === 0) {
        this.$message.error('请选择要启用的账户')
      } else {
        this.statusModalDis = true
      }
    },
    // 批量启用
    status () {
      changeStatus({
        userId: this.userIdList.join(),
        status: 1
      }).then(res => {
        this.statusModalDis = false
        this.userIdList = []
        this.getTableData()
      })
    },
    // input失去焦点
    phoneSelect () {
      const reg = /^1[3|4|5|7|8][0-9]\d{8}$/
      if (!reg.test(this.userData.phone)) {
        this.btnLoading = false
        return this.$message.error('请输入正确的手机号')
      }
      selectByPhone({
        phone: this.userData.phone
      }).then(res => {
        this.departmentList = res.data
      })
    },
    // 获取人员
    getSelectRole () {
      const obj = {
        roleId: 0,
        name: '请选择'
      }
      selectRole().then(res => {
        this.roleList = res.data
        this.roleList.unshift(obj)
      })
    },
    // 添加子账户
    addSubManagement () {
      this.$refs.ruleForm.validate(valid => {
        if (valid) {
          if (this.userId !== '') {
            this.btnLoading = true
            const reg = /^1[3|4|5|7|8][0-9]\d{8}$/
            if (!reg.test(this.userData.phone)) {
              this.btnLoading = false
              return this.$message.error('请输入正确的手机号')
            }
            editSubManagement({
              userId: this.userId,
              userName: this.userData.userName,
              phone: this.userData.phone,
              gender: this.userData.gender,
              roleId: this.userData.roleId,
              status: this.userData.status
            }).then(res => {
              this.btnLoading = false
              this.modalVisible = false
              this.userData = {
                gender: 1,
                status: 0
              }
              this.getTableData()
            }).catch((res) => {
              this.modalVisible = true
              this.btnLoading = false
            })
          } else {
            this.btnLoading = true
            const reg = /^1[3|4|5|7|8][0-9]\d{8}$/
            if (!reg.test(this.userData.phone)) {
              this.btnLoading = false
              return this.$message.error('请输入正确的手机号')
            }
            addSubManagement(this.userData).then(res => {
              this.btnLoading = false
              this.modalVisible = false
              this.getTableData()
              this.userData = {
                gender: 1,
                status: 0
              }
            }).catch((res) => {
              this.modalVisible = true
              this.btnLoading = false
            })
          }
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    // 取消
    reset () {
      this.modalVisible = false
      this.userData = {
        gender: 1,
        status: 0
      }
      this.departmentList = []
      this.$refs.ruleForm.resetFields()
    },
    // 修改子账户
    editSubManagement (id) {
      getSubManagement({
        userId: id
      }).then(res => {
        this.modalVisible = true
        this.userId = res.data.userId
        this.userData = res.data
        selectByPhone({
          phone: this.userData.phone
        }).then(res => {
          this.departmentList = res.data
        })
      })
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    }
  }
}
</script>

<style lang="less" scoped>
.alter {
  width: 80%;
  height: 50px;
  background: #E6F7FF;
  border: 1px solid #C2EAFF;
  padding: 0 15px;
  span {
    line-height: 50px;
    margin-left: 10px;
  }
}
.table-wrapper {
  margin-top: 20px;
  .ant-btn {
    margin-bottom: 20px;
  }
}
.role-box {
  width: 100%;
  border-top: 1px dashed #e8e8e8;
  display: flex;
  justify-content: space-between;
  padding: 15px 5px;
  .ant-row {
    width: 49%;
    .name {
      overflow: hidden;
      text-overflow:ellipsis;
      white-space: nowrap;
    }
  }
}
</style>
