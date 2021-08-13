<template>
  <div>
    <div class="pageTop">
      <a-button type="primary" @click="()=>{this.$router.push({ path: '/roomQuality/newRule' })}">新建规则</a-button>
      <a-input-search
        placeholder="请输入规则名称搜索"
        style="width: 200px"
        allow-clear
        v-model="nameRule"
        @search="searchRuleName"
        @change="emptyRule"
      />
    </div>
    <a-card class="pageContent">
      <a-table
        :columns="table.columns"
        :data-source="table.data"
        :pagination="pagination"
      >
        <div slot="rooms" slot-scope="text">
          <a-tag v-for="(item,index) in text" :key="index">{{ item.name }}</a-tag>
        </div>
        <div slot="create_user" slot-scope="text">
          <a-tag><a-icon type="user" />{{ text }}</a-tag>
        </div>
        <div slot="status" slot-scope="text,record">
          <a-switch :default-checked="text==1" size="small" @change="statusRule(record)" />
        </div>
        <div slot="action" slot-scope="text, record">
          <a @click="$router.push('/roomQuality/detail?rowId='+record.id)">详情</a>
          <a-divider type="vertical"/>
          <a-dropdown>
            <a class="ant-dropdown-link">
              编辑<a-icon type="down" />
            </a>
            <a-menu slot="overlay">
              <a-menu-item>
                <a @click="$router.push('/roomQuality/edit?rowId='+record.id)">修改</a>
              </a-menu-item>
              <a-menu-item>
                <a @click="delTableRow(record)">删除</a>
              </a-menu-item>
            </a-menu>
          </a-dropdown>
        </div>
      </a-table>
    </a-card>
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { indexApi, destroyApi, statusApi } from '@/api/roomQuality'
export default {

  data () {
    return {
      // 规则名称
      nameRule: '',
      table: {
        columns: [
          {
            key: '',
            dataIndex: 'name',
            title: '规则名称'
          },
          {
            key: 'rooms',
            dataIndex: 'rooms',
            title: '使用群聊',
            scopedSlots: { customRender: 'rooms' }
          },
          {
            key: 'create_user',
            dataIndex: 'create_user',
            title: '创建人',
            scopedSlots: { customRender: 'create_user' }
          },
          {
            key: 'created_at',
            dataIndex: 'created_at',
            title: '创建时间'
          },
          {
            key: 'status',
            dataIndex: 'status',
            title: '开关',
            scopedSlots: { customRender: 'status' }
          },
          {
            key: 'action',
            dataIndex: 'action',
            title: '操作',
            width: '200px',
            scopedSlots: { customRender: 'action' }
          }
        ],
        data: []
      },
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      }
    }
  },
  created () {
    this.getTableData()
  },
  methods: {
    // 更改规则状态
    statusRule (record) {
      let status = 0
      if (record.status == 0) {
        status = 1
      }
      statusApi({
        id: record.id,
        status
      }).then((res) => {})
    },
    // 删除
    delTableRow (record) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          destroyApi({
            id: record.id
          }).then((res) => {
            that.$message.success('删除成功')
            that.getTableData()
          })
        }
      })
    },
    //  获取表格数据
    getTableData (paramData) {
      indexApi(paramData).then((res) => {
        this.table.data = res.data.list
      })
    },
    //  搜索规则名称
    searchRuleName () {
      this.getTableData({
        name: this.nameRule
      })
    },
    //  清空输入框
    emptyRule (e) {
      if (this.nameRule == '') {
        this.getTableData()
      }
    }
  }
}
</script>
<style>
  .pageTop{
    display: flex;
    justify-content:space-between;
    margin-top: 10px;
  }
  .pageContent{
    margin-top: 20px;
  }
</style>
