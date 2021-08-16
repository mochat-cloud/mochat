<template>
  <div class="automatic-pulling">
    <a-card>
      <a-row>
        <a-button class="batch" type="primary" @click="establishGroup">创建群邀请</a-button>
      </a-row>
      <div class="table-wrapper">
        <a-table
          :columns="columns"
          :data-source="tableData">
          <div slot="name" slot-scope="row">
            {{ row.name }}
          </div>
          <div slot="rooms" slot-scope="row">
            <a-tag class="mb6" v-for="groupName in row.rooms" :key="groupName.id">
              {{ groupName }}
            </a-tag>
          </div>
          <div slot="employees" slot-scope="row">
            <a-tag class="mb6" v-for="member in row.employees" :key="member.memberid">
              {{ member }}
            </a-tag>
          </div>
          <div slot="action" slot-scope="text, record">
            <template>
              <a-button type="link" @click="send(record.id)">提醒发送</a-button>
              <a-button type="link" @click="detail(record.id)">详情</a-button>
              <a-button type="link" @click="deleteRow(record.id)">删除</a-button>
            </template>
          </div>
        </a-table>
      </div>
    </a-card>
  </div>
</template>

<script>
import { tagGetList, delRoomTag, remindRoomTag } from '@/api/workRoom'

export default {
  data () {
    return {
      abc: true,
      columns: [
        {
          title: '任务名称',
          dataIndex: 'name'
        },
        {
          title: '群名称',
          scopedSlots: { customRender: 'rooms' }
        },
        {
          title: '发送邀请成员',
          scopedSlots: { customRender: 'employees' }
        },
        {
          title: '创建时间',
          dataIndex: 'created_at'
        },
        {
          title: '已邀请客户',
          dataIndex: 'invite_num'
        },
        {
          title: '已入群客户',
          dataIndex: 'join_room_num'
        },
        {
          title: '未发送成员',
          dataIndex: 'no_send_num'
        },
        {
          title: '未邀请客户',
          dataIndex: 'no_invite_num'
        },
        {
          title: '操作',
          width: '150px',
          scopedSlots: { customRender: 'action' }
        }
      ],
      tableData: [{}],
      selectedRowKeys: [],
      workRoomAutoPullId: ''
    }
  },
  mounted () {
    this.getList()
  },
  methods: {
    getList () {
      tagGetList({
        name: ''
      }).then(res => {
        this.tableData = res.data.list
      })
    },

    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
    },

    establishGroup () {
      this.$router.push({ path: '/roomTagPull/create' })
    },

    async detail (id) {
      this.$router.push({ path: '/roomTagPull/detail', query: { id } })
    },

    deleteRow (id) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          delRoomTag({ id }).then(res => {
            that.$message.success('删除成功')
            that.getList()
          })
        }
      })
    },
    send (id) {
      remindRoomTag({ id }).then(res => {
        this.$message.success('提醒成功')
        this.getList()
      })
    }
  }
}
</script>

<style lang="less" scoped>
.automatic-pulling {
  height: 100%;

  .left {
    background: #fff;
    padding: 15px 15px;
  }

  .lists {
    margin-bottom: 20px;
  }

  .table-wrapper {
    margin-top: 20px;

    .img {
      width: 50px;
      height: 50px
    }

    .table-room {
      margin-bottom: 5px;
    }

    .status {
      width: 60px;
      display: inline-block;
      margin-left: 10px;
      background: #1890ff;
      text-align: center;
      color: #fff
    }

    .top-btn {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 10px;

      .batch {
        margin-right: 20px;
      }
    }
  }

  .download {
    padding: 0 15px;
  }
}

.tipBtn {
  color: #aaa;
  cursor: pointer;
  padding-left: 15px;
}

</style>
