<template>
  <div class="white-bg-module">
    <div class="search">
      <div class="total"><span class="b">共{{ clientTable.data.length }}个客户</span></div>
      <div class="an-form">
        添加状态
        <a-select style="width: 200px" v-model="askClientTable.status" @change="selectRadio">
          <a-select-option :value="4">全部</a-select-option>
          <a-select-option :value="0">未分配</a-select-option>
          <a-select-option :value="1">待添加</a-select-option>
          <a-select-option :value="2">待通过</a-select-option>
          <a-select-option :value="3">已添加</a-select-option>
        </a-select>
        <a-input-search
          placeholder="请输入要搜索电话 / 备注名"
          style="width: 240px"
          @search="searchClientData"
          v-model="askClientTable.searchKey"
          :allowClear="true"
          @change="cleanInput"
        />
      </div>
    </div>
    <a-table
      class="table"
      :columns="clientTable.columns"
      :data-source="clientTable.data"
    >
      <div slot="tags" slot-scope="text">
        <a-tag v-for="(item,index) in text" :key="index">{{ item.name }}</a-tag>
      </div>
      <div slot="status" slot-scope="text">
        <a-tag v-if="text==0" color="gray">未分配</a-tag>
        <a-tag v-if="text==1" color="orange">待添加</a-tag>
        <a-tag v-if="text==2" color="cyan">待通过</a-tag>
        <a-tag v-if="text==3" color="green">已添加</a-tag>
      </div>
      <div slot="allotEmployee" slot-scope="text">
        <a-tag>{{ text.name }}</a-tag>
      </div>
      <!--    slot-scope="text,record"  -->
      <div slot="operation" slot-scope="text,record">
        <a @click="remindAdd(record)">提醒添加</a>
      </div>
    </a-table>
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { contactList } from '@/api/contactBatchAdd'
export default {
  data () {
    return {
      clientTable: {
        columns: [
          {
            title: '电话号码',
            dataIndex: 'phone'
          },
          {
            title: '客服备注名字',
            dataIndex: 'remark'
          },
          {
            title: '导入时间',
            dataIndex: 'uploadAt'
          },
          {
            title: '客户标签',
            dataIndex: 'tags',
            scopedSlots: { customRender: 'tags' }
          },
          {
            title: '添加状态',
            dataIndex: 'status',
            scopedSlots: { customRender: 'status' }
          },
          {
            title: '添加时间',
            dataIndex: 'addAt'
          },
          {
            title: '分配员工',
            dataIndex: 'allotEmployee',
            scopedSlots: { customRender: 'allotEmployee' }
          },
          {
            title: '分配次数',
            dataIndex: 'allotNum'
          },
          {
            title: '操作',
            dataIndex: 'operation',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        data: []
      },
      //  请求数据
      askClientTable: {
        // 分配状态
        status: 4,
        // 搜索关键字
        searchKey: '',
        // 指定导入批次
        recordId: ''
      }
    }
  },
  created () {
    this.askClientTable.recordId = this.$route.query.recordId
    this.getClientTableData()
  },
  methods: {
    // 提醒添加
    remindAdd (record) {
      // const that = this
      this.$confirm({
        title: '提示',
        content: '将通过提醒给该成员添加该手机号为好友，是否确认发送？',
        okText: '发送',
        okType: 'primary',
        cancelText: '取消',
        onOk () {

        }
      })
    },
    // 选择状态
    selectRadio () {
      this.getClientTableData()
    },
    // 搜索潜在客户
    searchClientData () {
      this.getClientTableData()
    },
    // 清除输入框
    cleanInput () {
      if (this.askClientTable.searchKey == '') {
        this.getClientTableData()
      }
    },
    // 获取表格数据
    getClientTableData () {
      const params = {
        // 分配状态
        status: '',
        // 搜索关键字
        searchKey: this.askClientTable.searchKey,
        // 指定导入批次
        recordId: this.askClientTable.recordId
      }
      if (this.askClientTable.status == 4) {
        params.status = ''
      } else {
        params.status = this.askClientTable.status
      }
      contactList(params).then((res) => {
        this.clientTable.data = res.data.data
      })
    }
  }
}
</script>
<style scoped lang="less">
.white-bg-module {
  background-color: #fff;
  .search {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    align-items: center;
    padding: 15px 15px 0px;
    .total {
      .b {
        font-weight: bold;
      }
    }
    .ant-select {
      width: 120px;
      margin-right: 15px;
    }
    .ant-calendar-picker {
      margin-right: 15px;
    }
  }
}

.user {
  font-size: 13px;
  border: 1px solid #e9e9e9;
  margin-bottom: 5px;
  border-radius: 5px;
  text-align: center;
  display: inline-block;
  padding: 2px 5px;
}
</style>
