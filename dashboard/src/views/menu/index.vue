<template>
  <div>
    <a-card title="数据筛选">
      <a-row>
        <a-col :span="6">
          <a-input v-model="searchName" placeholder="请输入"></a-input>
        </a-col>
        <a-col :span="6" :offset="1">
          <a-button v-permission="'/menu/index@search'" type="primary" style="marginRight:10px" @click="search">查询</a-button>
          <a-button @click="reset">重置</a-button>
        </a-col>
      </a-row>
      <div class="table-wrapper">
        <div class="top-btn">
          <a-button v-permission="'/menu/index@add'" class="batch" type="primary" @click="add">添加</a-button>
        </div>
        <a-table
          bordered
          rowKey="menuId"
          :columns="columns"
          :data-source="tableData"
          :pagination="pagination"
          :indentSize="0"
          :expandIconColumnIndex="7"
          :expandIcon="expandIcon"
          @change="handleTableChange"
        >
          <div slot="icon" slot-scope="text" class="icon">
            <template>
              <a-icon v-if="text" :type="text" />
            </template>
          </div>
          <div slot="levelName" slot-scope="text, record" class="icon">
            <template>
              <div :class="['text', levelNum(record.level)]">{{ text }}</div>
            </template>
          </div>
          <div slot="status" slot-scope="text" class="icon">
            <template>
              <div class="text">{{ text == '1' ? '启用' : '禁用' }}</div>
            </template>
          </div>
          <div slot="action" slot-scope="text, record" class="action">
            <template>
              <a-button v-permission="'/menu/index@edit'" type="link" @click="edit(record.menuId)">编辑</a-button>
            </template>
          </div>
        </a-table>
      </div>
    </a-card>
    <a-modal
      :title="menuShowTitle"
      :maskClosable="false"
      :width="500"
      :visible="menuShow"
      @cancel="menuShow = false"
    >
      <a-form-model
        class="form"
        :label-col="{ span: 7 }"
        :wrapper-col="{ span: 14 }"
        :model="formData"
        :rules="rules"
        ref="ruleForm">
        <div v-if="menuShowType == 'add'">
          <a-form-model-item
            key="1"
            label="菜单级别"
            prop="level">
            <a-select v-model="level">
              <a-select-option v-for="(item, index) in levelList" :value="item.value" :key="index">
                {{ item.label }}
              </a-select-option>
            </a-select>
          </a-form-model-item>
          <a-form-model-item
            key="2"
            label="一级菜单"
            v-if="level > 1"
            prop="firstMenuId">
            <a-select
              v-model="firstMenuId"
              @change="firstMenuChange">
              <a-select-option v-for="(item, index) in firstMenuList" :value="item.menuId" :key="index">
                {{ item.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>
          <a-form-model-item
            key="3"
            label="二级菜单"
            v-if="level > 2"
            prop="secondMenuId">
            <a-select
              v-model="secondMenuId"
              :disabled="!firstMenuId"
              @change="secondMenuChange">
              <a-select-option v-for="(item, index) in secondMenuList" :value="item.menuId" :key="index">
                {{ item.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>
          <a-form-model-item
            key="4"
            label="三级菜单"
            v-if="level > 3"
            prop="thirdMenuId">
            <a-select
              v-model="thirdMenuId"
              :disabled="!secondMenuId"
              @change="thirdMenuChange">
              <a-select-option v-for="(item, index) in thirdMenuList" :value="item.menuId" :key="index">
                {{ item.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>
          <a-form-model-item
            key="5"
            label="四级菜单"
            v-if="level > 4"
            prop="fourthMenuId"
          >
            <a-select
              v-model="fourthMenuId"
              @change="fourthMenuChange"
              :disabled="!thirdMenuId">
              <a-select-option v-for="(item, index) in fourthMenuList" :value="item.menuId" :key="index">
                {{ item.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>
        </div>

        <a-form-model-item
          key="6"
          label="名称"
          prop="name">
          <a-input v-model.trim="name" :maxLength="10" placeholder="请输入"></a-input>
        </a-form-model-item>
        <a-form-model-item
          key="7"
          label="图标"
          v-if="level < 4">
          <a-icon v-if="icon" :type="icon"></a-icon>
          <a-button type="link" @click="iconShow=true">选择图标</a-button>
        </a-form-model-item>
        <a-form-model-item
          key="8"
          label="地址"
          prop="linkUrl"
          v-if="level > 2">
          <a-input v-model.trim="linkUrl" placeholder="请输入"></a-input>
        </a-form-model-item>
        <a-form-model-item
          key="9"
          label="链接类型"
          prop="linkType"
          v-if="level > 2">
          <a-radio-group
            v-model="linkType"
          >
            <a-radio :value="1">
              内部链接
            </a-radio>
            <a-radio :value="2">
              外部链接
            </a-radio>
          </a-radio-group>
        </a-form-model-item>
        <a-form-model-item
          key="11"
          label="页面权限"
          prop="isPageMenu"
          v-if="level > 3">
          <a-radio-group
            v-model="isPageMenu"
          >
            <a-radio :value="1">
              是
            </a-radio>
            <a-radio :value="2">
              否
            </a-radio>
          </a-radio-group>
        </a-form-model-item>
        <a-form-model-item
          key="10"
          label="数据权限"
          v-if="level > 3 && isPageMenu == 2"
          prop="dataPermission">
          <a-radio-group
            v-model="dataPermission"
          >
            <a-radio :value="1">
              启用
            </a-radio>
            <a-radio :value="2">
              不启用
            </a-radio>
          </a-radio-group>
        </a-form-model-item>
      </a-form-model>
      <div slot="footer" class="footer">
        <template>
          <a-button
            type="link"
            @click="submit"
            :loading="loading">
            确认
          </a-button>
          <a-button
            type="link"
            @click="remove"
            :loading="loading"
            v-if="menuShowType == 'edit' && status == '2'">
            移除
          </a-button>
          <a-button
            type="link"
            @click="forbid"
            :loading="loading"
            v-if="menuShowType == 'edit'">
            {{ status == '1' ? '禁用' : '启用' }}
          </a-button>
          <a-button
            type="link"
            @click="menuShow = false">
            取消
          </a-button>
        </template>
      </div>
    </a-modal>
    <a-modal
      title="选择图标"
      :maskClosable="false"
      :width="800"
      :visible="iconShow"
      @cancel="iconShow = false"
    >
      <icon-selector
        v-model="currentSelectedIcon"
        @change="handleIconChange"
        :usedIconList="usedIconList"/>
      <div slot="footer">
        <template>
          <a-button @click="chooseIcon">确定</a-button>
        </template>
      </div>
    </a-modal>
  </div>
</template>

<script>
import IconSelector from '@/components/IconSelector/IconSelector'
import { menuList, menuSelect, menuDetail, statusUpdate, destroy, menuStore, menuUpdate, iconUsed } from '@/api/menu'

const columns = [
  {
    title: '序号',
    dataIndex: 'menuPath'
  },
  {
    title: '菜单名称',
    align: 'center',
    dataIndex: 'name'
  },
  {
    title: '菜单级别',
    align: 'center',
    dataIndex: 'levelName',
    scopedSlots: { customRender: 'levelName' }
  },
  {
    title: '菜单图标',
    dataIndex: 'icon',
    align: 'center',
    scopedSlots: { customRender: 'icon' }
  },
  {
    title: '状态',
    align: 'center',
    dataIndex: 'status',
    scopedSlots: { customRender: 'status' }
  },
  {
    title: '最后操作人',
    align: 'center',
    dataIndex: 'operateName'
  },
  {
    title: '最后操作时间',
    align: 'center',
    dataIndex: 'updatedAt'
  },
  {
    title: '操作',
    dataIndex: 'action',
    width: '150px',
    scopedSlots: { customRender: 'action' }
  }
]

export default {
  components: {
    IconSelector
  },
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
    const vlevel = (rule, value, callback) => {
      value = this.level
      createValidate(callback, value, '请选择菜单级别')
    }
    const vfirstMenuId = (rule, value, callback) => {
      value = this.firstMenuId
      createValidate(callback, value, '请选择一级菜单')
    }
    const vsecondMenuId = (rule, value, callback) => {
      value = this.secondMenuId
      createValidate(callback, value, '请选择二级菜单')
    }
    const vthirdMenuId = (rule, value, callback) => {
      value = this.thirdMenuId
      createValidate(callback, value, '请选择三级菜单')
    }
    const vfourthMenuId = (rule, value, callback) => {
      value = this.fourthMenuId
      createValidate(callback, value, '请选择四级菜单')
    }
    const vname = (rule, value, callback) => {
      value = this.name
      let message = '请输入名称'
      if (this.name.length > 10) {
        message = '名称最多10个字符'
        value = false
      }
      createValidate(callback, value, message)
    }
    const vicon = (rule, value, callback) => {
      value = this.icon
      createValidate(callback, value, '请选择图标')
    }
    const vlinkUrl = (rule, value, callback) => {
      value = this.linkUrl
      createValidate(callback, value, '请输入地址')
    }
    const vlinkType = (rule, value, callback) => {
      value = this.linkType
      createValidate(callback, value, '请选择链接类型')
    }
    const vdataPermission = (rule, value, callback) => {
      value = this.dataPermission
      createValidate(callback, value, '请选择数据权限')
    }
    const visPageMenu = (rule, value, callback) => {
      value = this.isPageMenu
      createValidate(callback, value, '请选择是否页面权限')
    }
    return {
      columns,
      tableData: [],
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      searchName: '',
      menuShow: false,
      menuShowType: '',
      menuShowTitle: '',
      firstMenuId: '',
      firstMenuList: [],
      secondMenuId: '',
      secondMenuList: [],
      thirdMenuId: '',
      thirdMenuList: [],
      fourthMenuId: '',
      fourthMenuList: [],
      menuId: '',
      name: '',
      icon: '',
      linkUrl: '',
      linkType: '',
      dataPermission: '',
      isPageMenu: '',
      status: '',
      loading: false,
      formData: {},
      rules: {
        level: createFunc(vlevel, 'change'),
        firstMenuId: createFunc(vfirstMenuId, 'change'),
        secondMenuId: createFunc(vsecondMenuId, 'change'),
        thirdMenuId: createFunc(vthirdMenuId, 'change'),
        fourthMenuId: createFunc(vfourthMenuId, 'change'),
        name: createFunc(vname, 'change'),
        icon: createFunc(vicon, 'change'),
        linkUrl: createFunc(vlinkUrl, 'change'),
        linkType: createFunc(vlinkType, 'change'),
        dataPermission: createFunc(vdataPermission, 'change'),
        isPageMenu: createFunc(visPageMenu, 'change')
      },
      level: '',
      levelList: [
        {
          label: '一级菜单',
          value: '1'
        },
        {
          label: '二级菜单',
          value: '2'
        },
        {
          label: '三级菜单',
          value: '3'
        },
        {
          label: '四级菜单',
          value: '4'
        },
        {
          label: '四级菜单操作',
          value: '5'
        }
      ],
      currentSelectedIcon: '',
      iconShow: false,
      usedIconList: []
    }
  },
  watch: {
    menuShow (value) {
      if (!value) {
        this.level = ''
        this.firstMenuId = ''
        this.secondMenuId = ''
        this.thirdMenuId = ''
        this.fourthMenuId = ''
        this.name = ''
        this.icon = ''
        this.linkUrl = ''
        this.linkType = ''
        this.dataPermission = ''
        this.status = ''
        this.currentSelectedIcon = ''
        this.menuId = ''
        this.isPageMenu = ''
        this.loading = false
        this.$refs.ruleForm.clearValidate()
      }
    },
    iconShow (value) {
      if (value) {
        this.getUsedIcon()
      }
    }
  },
  created () {
    this.getMenuOptions()
    this.getTableData()
  },
  methods: {
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    },
    async getTableData () {
      const params = {
        name: this.searchName,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      try {
        const { data: { page: { total }, list } } = await menuList(params)
        this.pagination.total = Number(total)
        this.tableData = list
      } catch (e) {
        console.log(e)
      }
    },
    search () {
      this.pagination.current = 1
      this.getTableData()
    },
    async edit (menuId) {
      this.menuShow = true
      this.menuShowType = 'edit'
      this.menuShowTitle = '编辑菜单'
      this.menuId = menuId
      try {
        const { data } = await menuDetail({ menuId })
        Object.assign(this, data)
        this.firstMenuChange()
        this.secondMenuChange()
        this.thirdMenuChange()
      } catch (e) {
        console.log(e)
      }
    },
    reset () {
      this.searchName = ''
    },
    add () {
      this.menuShow = true
      this.menuShowType = 'add'
      this.menuShowTitle = '添加菜单'
      this.getMenuOptions()
    },
    levelNum (index) {
      const color = ['', '', 'second', 'third', 'fourth', 'fif']
      return color[index]
    },
    // 菜单展开
    expandIcon (props) {
      if (!props.record.children) {
        return
      }
      if (props.record.children.length > 0) {
        if (props.expanded) {
          return <a class="expand-wrapper" onClick={e => {
            props.onExpand(props.record, e)
          }}>收起</a>
        } else {
          return <a class="expand-wrapper" onClick={e => {
            props.onExpand(props.record, e)
          }}>展开</a>
        }
      }
    },
    handleIconChange (icon) {
      this.currentSelectedIcon = icon
    },
    getUsedIcon () {
      iconUsed().then(res => {
        this.usedIconList = res.data
      })
    },
    chooseIcon () {
      this.icon = this.currentSelectedIcon
      this.iconShow = false
    },
    async getMenuOptions () {
      try {
        const { data } = await menuSelect()
        this.firstMenuList = data
      } catch (e) {
        console.log(e)
      }
    },
    firstMenuChange (value) {
      const obj = this.firstMenuList.find(item => {
        return item.menuId == this.firstMenuId
      })
      this.secondMenuList = (obj && obj.children) || []
    },
    secondMenuChange (value) {
      const obj = this.secondMenuList.find(item => {
        return item.menuId == this.secondMenuId
      })
      this.thirdMenuList = (obj && obj.children) || []
    },
    thirdMenuChange (value) {
      const obj = this.thirdMenuList.find(item => {
        return item.menuId == this.thirdMenuId
      })
      this.fourthMenuList = (obj && obj.children) || []
    },
    fourthMenuChange (value) {
    },
    submit () {
      const parents = [
        '0',
        {
          firstMenuId: this.firstMenuId
        },
        {
          firstMenuId: this.firstMenuId,
          secondMenuId: this.secondMenuId
        },
        {
          firstMenuId: this.firstMenuId,
          secondMenuId: this.secondMenuId,
          thirdMenuId: this.thirdMenuId
        },
        {
          firstMenuId: this.firstMenuId,
          secondMenuId: this.secondMenuId,
          thirdMenuId: this.thirdMenuId,
          fourthMenuId: this.fourthMenuId
        }
      ]
      const params = {
        level: this.level,
        name: this.name,
        icon: this.icon,
        linkUrl: this.linkUrl,
        linkType: this.linkType
      }
      if (this.level > 1) {
        Object.assign(params, parents[this.level - 1])
        if (this.level > 3) {
          Object.assign(params, {
            isPageMenu: this.isPageMenu
          })
          if (this.isPageMenu == 2) {
            Object.assign(params, {
              dataPermission: this.dataPermission
            })
          }
        }
      }
      this.$refs.ruleForm.validate(async valid => {
        if (valid) {
          this.loading = true
          try {
            if (this.menuShowType == 'add') {
              await menuStore(params)
              this.menuShow = false
              this.$message.success('添加成功')
              this.getTableData()
            } else {
              params.menuId = this.menuId
              await menuUpdate(params)
              this.menuShow = false
              this.$message.success('修改成功')
              this.getTableData()
            }
          } catch (e) {
            console.log(e)
            this.loading = false
          }
        }
      })
    },
    async remove () {
      try {
        await destroy({ menuId: this.menuId })
        this.menuShow = false
        this.getTableData()
      } catch (e) {
        console.log(e)
      }
    },
    async forbid () {
      const data = ['', this.firstMenuList, this.secondMenuList, this.thirdMenuList, this.fourthMenuList]
      console.log(data)
      const params = {
        menuId: this.menuId,
        status: this.status == '1' ? '2' : '1'
      }
      try {
        await statusUpdate(params)
        this.menuShow = false
        this.getTableData()
      } catch (e) {
        console.log(e)
      }
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
.table-wrapper {
  .icon {
    text-align: center;
    font-size: 25px;
  }
  .text {
    font-size: 14px;
  }
  .second {
    color: #1890ff
  }
  .third {
    color: red
  }
  .fourth {
    color: rgb(201, 154, 240)
  }
  .fif {
    color:  rgb(105, 236, 100)
  }
}
.expand-wrapper {
  width: 60px;
  padding: 0 16px;
  display: inline-block;
}
.footer {
  text-align: left;
}
</style>
