<template>
  <div class="index_module">
    <a-row class="search" type="flex" justify="space-between">
      <a-button @click="handleAdd" type="primary">新建群发</a-button>
      <a-input-search placeholder="请输入群发名称" @search="onSearch" />
    </a-row>
    <a-table
      class="index_table"
      rowKey="id"
      :pagination="pagination"
      :columns="columns"
      :data-source="dataList"
      @change="tableChange"
    >
      <span slot="content" slot-scope="text,record">
        <div v-for="(v,i) in record.content" :key="i">
          <div v-if="v.msgType === 'text'">
            {{ v.content }}
          </div>
          <div v-if="v.msgType === 'link'">
            <span class="type-text">[链接]</span>
            {{ v.title }}
          </div>
          <div v-if="v.msgType === 'image'">
            <img class="table-pic" :src="v.pic_url">
          </div>
          <div v-if="v.msgType === 'miniprogram'">
            <div class="applets">
              <div class="title">
                {{ v.title }}
              </div>
              <div class="image">
                <img :src="v.pic_media_id">
              </div>
              <div class="applets-logo">
                <img src="../../assets/link.jpg">
                小程序
              </div>
            </div>
          </div>
        </div>
      </span>
      <span slot="action" slot-scope="text,record">
        <a @click="handleRemind(record)">提醒发送</a>
        <a-divider type="vertical" />
        <a class="ant-dropdown-link" @click="handleJump(record)">详情 </a>
      </span>
    </a-table>
  </div>
</template>
<script>

import { index, remind } from '@/api/roomMessageBatchSend'

const columns = [
  {
    dataIndex: 'batchTitle',
    title: '群发名称'
  },
  {
    title: '发送时间',
    dataIndex: 'createdAt'
  },
  {
    title: '群发内容',
    dataIndex: 'textContent',
    scopedSlots: { customRender: 'content' }
  },
  {
    title: '已发送成员',
    dataIndex: 'sendTotal'
  },
  {
    title: '送达客户',
    dataIndex: 'receivedTotal'
  },

  {
    title: '未发送成员',
    dataIndex: 'notSendTotal'
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
    onSearch (value) {
      this.pagination.current = 1
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
        perPage: e.perPage
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
      this.$router.push('/roomMessageBatchSend/show?id=' + item.id)
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
