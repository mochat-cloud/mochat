<template>
  <div class="index_module">
    <a-row class="search" type="flex" justify="space-between">
      <a-button @click="handleAdd" type="primary">新建群发</a-button>
      <a-input-search placeholder="请输入群发名称" @search="onSearch" v-model="searchTable" />
    </a-row>
    <a-table
      class="index_table"
      rowKey="id"
      :pagination="pagination"
      :columns="columns"
      :data-source="dataList"
      @change="tableChange"
    >
      <div slot="content" slot-scope="text,record">
        <div v-for="(item,index) in record.content" :key="index">
          <div v-if="item.msgType==='text'">群发消息1：{{ item.content }}</div>
          <div style="margin-top: 10px;" v-if="item.msgType==='image' || item.msgType==='link' || item.msgType==='miniprogram'">群发消息2：</div>
          <div style="margin-left: 15px;margin-top: 10px;">
            <div v-if="item.msgType=='image'">
              <img :src="item.pic_url" alt="" style="width: 70px;height: 70px;">
            </div>
            <div v-if="item.msgType=='link'">
              <div>{{ item.url }}</div>
              <div style="display: flex;">
                <div>
                  <div>{{ item.title }}</div>
                  <div>{{ item.desc }}</div>
                </div>
                <img :src="item.pic_url" alt="" style="width: 70px;height: 70px;">
              </div>
            </div>
            <div v-if="item.msgType=='miniprogram'">
              <div class="applets">
                <div class="title">
                  {{ item.title }}
                </div>
                <div class="image">
                  <img :src="item.pic_url">
                </div>
                <div class="applets-logo">
                  小程序
                </div>
              </div>
            </div>
          </div>
          <!--          -->
        </div>
      </div>
      <span slot="action" slot-scope="text,record">
        <a @click="handleRemind(record)" v-if="record.notReceivedTotal!=0">提醒发送</a>
        <a-divider type="vertical" v-if="record.notReceivedTotal!=0"/>
        <a class="ant-dropdown-link" @click="handleJump(record)">详情 </a>
        <a-divider type="vertical" />
        <a @click="delRowTable(record)">删除</a>
      </span>
    </a-table>
  </div>
</template>
<script>

import { index, remind, destroyApi } from '@/api/roomMessageBatchSend'

const columns = [
  {
    dataIndex: 'batchTitle',
    title: '群发名称'
  },
  {
    title: '发送时间',
    dataIndex: 'definiteTime'
  },
  {
    title: '群发内容',
    dataIndex: 'textContent',
    scopedSlots: { customRender: 'content' }
  },
  {
    title: '已发送群主',
    dataIndex: 'sendTotal'
  },
  {
    title: '送达群聊',
    dataIndex: 'receivedTotal'
  },

  {
    title: '未发送群主',
    dataIndex: 'notSendTotal'
  },
  {
    title: '未送达群聊',
    dataIndex: 'notReceivedTotal'
  },

  {
    title: '操作',
    dataIndex: 'action',
    scopedSlots: { customRender: 'action' }
  }
]

export default {
  data () {
    return {
      searchTable: '',
      dataList: [],
      columns,
      pagination: {
        total: 0,
        pageSize: 10,
        current: 1
      }
    }
  },
  mounted () {
    this.selectList({
      page: this.pagination.current,
      perPage: this.pagination.pageSize
    })
  },
  methods: {
    onSearch () {
      this.selectList({
        page: 1,
        perPage: 100
      })
    },
    tableChange (e) {
      console.log(JSON.stringify(e))
      this.pagination.current = e.current
      this.selectList({
        page: e.current,
        perPage: e.pageSize
      })
    },
    selectList (e) {
      index({
        page: e.page,
        perPage: e.perPage,
        batchTitle: this.searchTable
      }).then(data => {
        data = data.data
        data.list.forEach(element => {
          if (element.msgType == 'text' || element.msgType == 'image') {
            this.contentArray.push(element)
          }
        })
        this.dataList = data.list
        this.pagination = {
          total: data.page.total,
          pageSize: parseInt(data.page.perPage),
          current: data.page.totalPage
        }
      })
    },
    handleAdd () {
      this.$router.push('/roomMessageBatchSend/store')
    },
    handleRemind (item) {
      this.$confirm({
        title: '提醒?',
        content: '确认后将会给所有未发送成员发送提醒通知，是否发送',
        onOk: () => {
          remind({
            batchId: item.id
          }).then(_ => {
            this.$message.success('已提醒')
          })
        },
        onCancel () {
        }
      })
    },
    handleJump (item) {
      this.$router.push('/roomMessageBatchSend/show?batchId=' + item.id)
    },
    // 删除
    delRowTable (record) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          destroyApi({
            batchId: record.id
          }).then((res) => {
            that.$message.success('删除成功')
            that.selectList({
              page: that.pagination.current,
              perPage: that.pagination.pageSize
            })
          })
        }
      })
    }
  }
}
</script>
<style lang="less">
.index_module {
  background-color: #fff;
  padding: 0px 15px;
}
.search {
  padding: 15px 0px;

  .ant-input-search {
    width: 200px;
  }
}
.applets {
  max-width: 183px;
  background: #fff;
  border-radius: 2px;
  padding: 7px 11px;
  font-size: 12px;
  border: 1px solid #f2f2f2;

  .title {
    font-size: 12px !important;
    font-weight: 500;
    margin-bottom: 5px !important;
  }

  .image img {
    max-width: 128px;
    max-height: 128px;
    border-radius: 2px;
  }

  .applets-logo {
    width: 100%;
    border-top: 1px solid #E7E7E7;
    margin-top: 9px;
    padding-top: 2px;
    font-size: 11px;
    display: flex;
    align-items: center;

    img {
      width: 17px;
    }
  }
}
</style>
