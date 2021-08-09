<template>
  <div class="big-box">
    <a-card>
      <div class="top">
        <div class="create" style="flex: 1">
          <a-button type="primary" style="width: 110px" @click="$router.push('/roomCalendar/create')">
            创建日历
          </a-button>
        </div>
        <div>
          <a-input-search placeholder="请输入要搜索的名称" @search="getListData" v-model="name" style="width: 200px"/>
        </div>
      </div>
      <div class="table mt20">
        <a-table :columns="table.col" :data-source="table.data">
          <div slot="nickname" slot-scope="row">
            <a-tag>
              <a-icon type="user"/>
              {{ row.nickname }}
            </a-tag>
          </div>
          <div slot="operation" slot-scope="row">
            <a href="#" @click="openSelectGroup(row)">添加群聊</a>
            <a-divider type="vertical"/>
            <a @click="$router.push({path:'/roomCalendar/show',query:{id:row.id}})">详情</a>
            <a-divider type="vertical"/>
            <a href="#" @click="delClick(row.id)">删除
            </a>
          </div>
        </a-table>
      </div>
    </a-card>

    <selectGroup ref="selectGroup" @change="selectGroupChange"/>
  </div>
</template>

<script>
import { getList, del, addRoom } from '@/api/roomCalendar'
import selectGroup from '@/components/Select/group'

export default {
  data () {
    return {
      table: {
        col: [
          {
            title: '名称',
            dataIndex: 'name'
          },
          {
            title: '创建人',
            scopedSlots: { customRender: 'nickname' }
          },
          {
            title: '创建时间',
            dataIndex: 'created_at'
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        data: []
      },
      currentId: {},
      name: ''
    }
  },
  mounted () {
    this.getListData()
  },
  components: {
    selectGroup
  },
  methods: {
    delClick (id) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          del({ id }).then(res => {
            that.$message.success('删除成功')
            that.getListData()
          })
        }
      })
    },
    selectGroupChange (e) {
      addRoom({
        id: this.currentId,
        rooms: e
      }).then(res => {
        this.$message.success('添加成功')
        this.getListData()
      })
    },

    openSelectGroup (data) {
      this.currentId = data.id

      this.$refs.selectGroup.setSelect(data.room_ids)
    },

    getListData () {
      getList({
        status: 0,
        name: this.name
      }).then(res => {
        this.table.data = res.data.list
      })
    }
  }
}
</script>

<style lang="less" scoped>
.top {
  display: flex;
}

</style>
