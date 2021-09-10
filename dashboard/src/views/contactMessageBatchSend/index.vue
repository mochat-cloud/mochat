<template>
  <div class="index_module">
    <a-row class="search" type="flex" justify="space-between">
      <a-button type="primary" @click="$router.push({ path: '/contactMessageBatchSend/store' })">新建群发</a-button>
      <!--      <a-input-search placeholder="请输入群发名称" @search="onSearch" />-->
    </a-row>
    <a-table :columns="table.columns" :data-source="table.data">
      <div slot="content" slot-scope="text">
        <div v-for="(item,index) in text" :key="index">
          <div v-if="item.msgType==='text'">群发消息1：{{ item.content }}</div>
          <div style="margin-top: 10px;" v-if="item.msgType==='image' || item.msgType==='link' || item.msgType==='miniprogram'">群发消息2：</div>
          <div style="margin-left: 15px;margin-top: 10px;">
            <div v-if="item.msgType==='image'">
              <img :src="item.pic_url" alt="" style="width: 70px;height: 70px;">
            </div>
            <div v-if="item.msgType==='link'">
              <div>{{ item.url }}</div>
              <div style="display: flex;">
                <div>
                  <div>{{ item.title }}</div>
                  <div>{{ item.desc }}</div>
                </div>
                <img :src="item.pic_url" alt="" style="width: 70px;height: 70px;">
              </div>
            </div>
            <div v-if="item.msgType==='miniprogram'">
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
        </div>
      </div>
      <div slot="sendWay" slot-scope="text">
        <span v-if="text==1">立即发送</span>
        <span v-else>定时发送</span>
      </div>
      <div slot="action" slot-scope="text,record">
        <a @click="remindSend(record)" v-if="record.notReceivedTotal!=0">提醒发送</a>
        <span v-else style="color: gray;">提醒发送</span>
        <a-divider type="vertical" />
        <a @click="$router.push({ path: '/contactMessageBatchSend/show?batchId='+record.id })">详情</a>
        <a-divider type="vertical" />
        <a @click="delRowTable(record)">删除</a>
      </div>
    </a-table>
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { indexApi, destroyApi, remindApi } from '@/api/contactMessageBatchSend'
export default {
  data () {
    return {
      table: {
        columns: [
          {
            title: '群发内容',
            dataIndex: 'content',
            scopedSlots: { customRender: 'content' }
          },
          {
            title: '群发类型',
            dataIndex: 'sendWay',
            key: 'sendWay',
            scopedSlots: { customRender: 'sendWay' }
          },
          {
            title: '发送时间',
            dataIndex: 'definiteTime',
            key: 'definiteTime'
          },
          {
            title: '已发送成员',
            key: 'sendTotal',
            dataIndex: 'sendTotal'
          },
          {
            title: '送达客户',
            key: 'receivedTotal',
            dataIndex: 'receivedTotal'
          },
          {
            title: '未发送成员',
            key: 'notSendTotal',
            dataIndex: 'notSendTotal'
          },
          {
            title: '未送达客户',
            key: 'notReceivedTotal',
            dataIndex: 'notReceivedTotal'
          },
          {
            title: '创建时间',
            key: 'createdAt',
            dataIndex: 'createdAt'
          },
          {
            title: '操作',
            key: 'action',
            scopedSlots: { customRender: 'action' }
          }
        ],
        data: [
          {}
        ]
      }
    }
  },
  created () {
  //  获取表格数据
    this.getTableData()
  },
  methods: {
    // 提醒发送
    remindSend (record) {
      remindApi({
        batchId: record.id
      }).then((res) => {
        this.$message.success('提醒成功')
      })
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
            that.getTableData()
          })
        }
      })
    },
    getTableData () {
      indexApi().then((res) => {
        console.log(res.data.list)
        this.table.data = res.data.list
      })
    },
    //  搜索
    onSearch () {

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
.index_table{
  margin:0px -15px;
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
