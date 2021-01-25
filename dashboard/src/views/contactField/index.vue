<template>
  <div class="information-card">
    <a-tabs type="card">
      <a-tab-pane v-permission="'/contactField/index@advanced'" key="1" tab="高级属性">
        <a-card>
          <a-alert :show-icon="false" message="SCRM系统提供手机号、姓名、公司、年龄、性别、行业、爱好、生日及所在区域等15个通用属性字段，属性类型包括文本、单选、多选、日期等。系统通用字段只可修改使用状态和排序顺序（数值越大，在手机端展示越靠前）。" banner />
          <div class="search">
            <a-select v-permission="'/contactField/index@all'" v-if="!editModal" v-model="screenData.status" @change="changeStatus">
              <a-select-option v-for="item in statusSelect" :key="item.value">
                {{ item.label }}
              </a-select-option>
            </a-select>
            <div v-else></div>
            <div class="btn">
              <a-button v-permission="'/contactField/index@add'" v-if="!editModal" @click="() => {this.visible = true}">新增属性</a-button>
              <a-button v-permission="'/contactField/index@batch'" v-if="!editModal" :disabled="tableData.length === 0" type="primary" @click="batchEdit">批量修改</a-button>
              <a-button v-if="editModal" @click="reset" style="marginRight: 10px">取消</a-button>
              <a-button v-if="editModal" type="primary" @click="editDefine">提交</a-button>
            </div>
          </div>
          <div class="table-wrapper">
            <a-table
              :rowKey="record => record.id"
              :columns="columns"
              :data-source="editData"
              :pagination="pagination"
              @change="handleTableChange">
              <div slot="label" slot-scope="text, record, index">
                <span v-if="!editModal || record.isSys === 1">{{ record.label }}</span>
                <a-input v-model="editData[index].label" v-if="editModal && record.isSys === 0" :maxLength="8" />
              </div>
              <div slot="order" slot-scope="text, record, index">
                <span v-if="!editModal">{{ record.order }}</span>
                <a-input v-model="editData[index].order" v-if="editModal" :maxLength="8" />
              </div>
              <div slot="options" slot-scope="text, record">
                <a-tooltip v-if="record.options.length !== 0">
                  <template slot="title">
                    <div>{{ record.options.join(',') }}</div>
                  </template>
                  <div class="text-box">{{ record.options.join(',') }}</div>
                </a-tooltip>
                <div v-else>--</div>
              </div>
              <div slot="status" slot-scope="text, record, index">
                <div v-if="!editModal || record.name === 'gender'">
                  <span v-if="record.status === 1">
                    开启
                  </span>
                  <span v-if="record.status === 0">
                    关闭
                  </span>
                </div>
                <a-radio-group v-model="editData[index].status" v-if="editModal && record.name !== 'gender'">
                  <a-radio :value="1">
                    开启
                  </a-radio>
                  <a-radio :value="0">
                    关闭
                  </a-radio>
                </a-radio-group>
              </div>
              <div slot="action" slot-scope="text, record, index">
                <template v-if="!editModal">
                  <div v-permission="'/contactField/index@close'">
                    <a-button type="link" v-if="record.name !== 'gender' && record.status === 0" @click="editStatus(record.id, 1)">开启</a-button>
                    <a-button type="link" v-if="record.name !== 'gender' && record.status === 1" @click="editStatus(record.id, 0)">关闭</a-button>
                  </div>
                  <a-button v-permission="'/contactField/index@edit'" type="link" v-if="!editModal" @click="editDialog(record)">编辑</a-button>
                  <a-popconfirm
                    title="是否确认删除"
                    ok-text="确认"
                    cancel-text="取消"
                    @confirm="deleteAttribute(record.id)"
                  >
                    <a-button type="link" v-if="record.isSys === 0">删除</a-button>
                  </a-popconfirm>
                </template>
                <a-button type="link" v-if="editModal && record.isSys === 0" @click="deleteAttr(record.id, index)">删除</a-button>
              </div>
            </a-table>
          </div>
        </a-card>
      </a-tab-pane>
    </a-tabs>
    <a-modal
      :visible="visible"
      title="新增属性"
      @cancel="resetDefine">
      <a-form-model ref="ruleForm" :model="addAttribute" :rules="rules" :label-col="{ span: 5 }" :wrapper-col="{ span: 17 }">
        <a-form-model-item label="字段名称：" prop="label">
          <a-input :maxLength="8" v-model="addAttribute.label" placeholder="请填写字段名称"></a-input>
        </a-form-model-item>
        <a-form-model-item label="字段类型：" prop="type">
          <a-select v-model="addAttribute.type" @change="selectChange">
            <a-select-option v-for="item in fieldType" :key="item.value">
              {{ item.label }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item label="选项内容：" v-if="addAttribute.type === 1 || addAttribute.type === 2" prop="options">
          <a-textarea v-model="addAttribute.options"></a-textarea>
          <span>用中文逗号“，”新增选项内容,每个选项内容不超过12个字</span>
        </a-form-model-item>
        <a-form-model-item label="排序展示：">
          <a-input v-model="addAttribute.order"></a-input>
        </a-form-model-item>
        <a-form-model-item label="使用状态：">
          <a-radio-group v-model="addAttribute.status">
            <a-radio :value="1">
              开启
            </a-radio>
            <a-radio :value="0">
              关闭
            </a-radio>
          </a-radio-group>
        </a-form-model-item>
      </a-form-model>
      <template slot="footer">
        <a-button key="back" @click="resetDefine">
          取消
        </a-button>
        <a-button @click="submit" :loading="btnLoading" key="submit" type="primary">
          确定
        </a-button>
      </template>
    </a-modal>
    <a-modal
      :visible="editDis"
      @cancel="() => {this.editDis = false}"
      title="编辑属性">
      <a-form :label-col="{ span: 5 }" :wrapper-col="{ span: 17 }">
        <a-form-item label="字段名称：">
          <span>{{ editDetail.label }}</span>
        </a-form-item>
        <a-form-item label="字段类型：">
          <span>{{ editDetail.typeText }}</span>
        </a-form-item>
        <a-form-item label="选项内容：" v-if="editDetail.type === 1 || editDetail.type === 2">
          <div v-if="editDetail.isSys === 0">
            <a-tag v-for="(item, index) in editDetail.options" :key="index + 'a'" closable @close="(e) => log(e, index)">{{ item }}</a-tag>
            <a-textarea v-model="options"></a-textarea>
            <span>用“,”新增选项内容,每个选项内容不超过12个字</span>
          </div>
          <div v-else>
            <a-tag v-for="(item, index) in editDetail.options" :key="index + 'a'">{{ item }}</a-tag>
          </div>
        </a-form-item>
        <a-form-item label="排序展示：">
          <a-input v-model="editDetail.order"></a-input>
          <span style="color: red">数值越大,在手机端展示越靠前</span>
        </a-form-item>
        <a-form-item label="使用状态：">
          <div v-if="editDetail.name === 'gender'">
            <span v-if="editDetail.status === 1">开启</span>
            <span v-if="editDetail.status === 0">关闭</span>
          </div>
          <a-radio-group v-else v-model="editDetail.status">
            <a-radio :value="1">
              开启
            </a-radio>
            <a-radio :value="0">
              关闭
            </a-radio>
          </a-radio-group>
        </a-form-item>
      </a-form>
      <template slot="footer">
        <a-button key="back" @click="() => {this.editDis = false}">
          取消
        </a-button>
        <a-button @click="editAttribute" :loading="btnLoading" key="submit" type="primary">
          确定
        </a-button>
      </template>
    </a-modal>
  </div>
</template>

<script>
import { contactFieldList, delContactField, statusUpdate, addContactField, batchUpdate, editContactField } from '@/api/contactField'
export default {
  data () {
    return {
      btnLoading: false,
      screenData: {
        status: 2
      },
      visible: false,
      editDis: false,
      editModal: false,
      columns: [
        {
          align: 'center',
          title: '字段名称',
          dataIndex: 'label',
          scopedSlots: { customRender: 'label' }
        },
        {
          align: 'center',
          title: '填写格式',
          dataIndex: 'typeText'
        },
        {
          align: 'center',
          title: '选项内容',
          dataIndex: 'options',
          width: '120px',
          scopedSlots: { customRender: 'options' }
        },
        {
          align: 'center',
          title: '排序展示',
          dataIndex: 'order',
          scopedSlots: { customRender: 'order' }
        },
        {
          align: 'center',
          title: '状态',
          dataIndex: 'status',
          scopedSlots: { customRender: 'status' }
        },
        {
          title: '操作',
          dataIndex: 'action',
          width: '200px',
          scopedSlots: { customRender: 'action' }
        }
      ],
      rules: {
        label: [
          { required: true, message: '请填写字段名称', trigger: 'blur' }
        ],
        type: [{ required: true, message: '请选择字段类型', trigger: 'change' }],
        options: [{ required: true, message: '请填写选项内容', trigger: 'blur' }]
      },
      // 状态下拉框
      statusSelect: [
        {
          label: '关闭',
          value: 0
        },
        {
          label: '开启',
          value: 1
        },
        {
          label: '全部状态',
          value: 2
        }
      ],
      // 表格数据
      tableData: [],
      // 新增属性
      addAttribute: {
        status: 1
      },
      // 字段类型
      fieldType: [
        {
          label: '文本',
          value: 0
        },
        {
          label: '单选',
          value: 1
        },
        {
          label: '多选',
          value: 2
        },
        {
          label: '日期',
          value: 6
        },
        {
          label: '手机号',
          value: 9
        },
        {
          label: '邮箱',
          value: 10
        },
        {
          label: '区域',
          value: 5
        },
        {
          label: '图片',
          value: 11
        }
      ],
      // 编辑属性
      editDetail: {},
      // 编辑时新增选项内容
      options: '',
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      editData: [],
      editOptions: [],
      // 批量删除
      destroy: []
    }
  },
  created () {
    this.getTableData()
  },
  methods: {
    getTableData () {
      const params = {
        status: this.screenData.status,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      contactFieldList(params).then(res => {
        this.editData = res.data.list
        this.tableData = JSON.parse(JSON.stringify(res.data.list))
        this.pagination.total = res.data.page.total
      })
    },
    // 字段类型select
    selectChange (val) {
      this.addAttribute.type = val
    },
    // 编辑时删除选项内容
    log (e, index) {
      e.preventDefault()
      this.editDetail.options.splice(index, 1)
    },
    // 编辑属性
    editDialog (data) {
      this.editDetail = JSON.parse(JSON.stringify(data))
      this.editDis = true
    },
    editAttribute () {
      let newOptions = []
      if (this.options !== '') {
        newOptions = this.options.split('，')
      }
      const params = {
        id: this.editDetail.id,
        label: this.editDetail.label,
        type: this.editDetail.type,
        options: this.editDetail.options.concat(newOptions),
        order: this.editDetail.order,
        status: this.editDetail.status
      }
      this.btnLoading = true
      editContactField(params).then(res => {
        this.editDis = false
        this.btnLoading = false
        this.getTableData()
        this.options = ''
      })
    },
    // 新增属性
    submit () {
      this.$refs.ruleForm.validate(valid => {
        if (valid) {
          const params = {
            label: this.addAttribute.label,
            type: this.addAttribute.type,
            options: this.addAttribute.type === 1 || this.addAttribute.type === 2 ? this.addAttribute.options.split('，') : '',
            order: this.addAttribute.order,
            status: this.addAttribute.status
          }
          this.btnLoading = true
          addContactField(params).then(res => {
            this.btnLoading = false
            this.visible = false
            this.getTableData()
            this.addAttribute = {
              status: 1
            }
          }).catch(() => {
            this.visible = true
            this.btnLoading = false
          })
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    resetDefine () {
      this.visible = false
      this.addAttribute = {
        status: 1
      }
    },
    // 删除属性
    deleteAttribute (id) {
      delContactField({
        id: id
      }).then(res => {
        this.getTableData()
      })
    },
    // 批量删除
    deleteAttr (id, index) {
      this.editData.splice(index, 1)
      this.destroy.push(id)
    },
    // 修改状态
    editStatus (id, status) {
      statusUpdate({
        id: id,
        status: status
      }).then(res => {
        this.getTableData()
      })
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    },
    changeStatus (value) {
      this.screenData.status = value
      this.getTableData()
    },
    // 批量修改
    batchEdit () {
      this.editModal = true
      this.editData = [...this.tableData]
    },
    // 批量改变状态
    onChange (checked, e, index) {
      console.log(index)
    },
    // 批量修改
    editDefine () {
      batchUpdate({
        update: this.editData,
        destroy: this.destroy
      }).then(res => {
        this.getTableData()
        this.editModal = false
      })
    },
    // 取消批量修改
    reset () {
      this.editModal = false
      this.getTableData()
    }
  }
}
</script>

<style lang="less" scoped>
.information-card {
  .search {
    width: 100%;
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
  }
  .table-wrapper {
    margin-top:20px;
    .text-box {
      width: 150px;
      overflow: hidden;
      text-overflow:ellipsis;
      white-space: nowrap;
    }
  }
}
</style>
