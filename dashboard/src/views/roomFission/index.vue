<template>
  <div class="room-fission">
    <div style="background: #fff;margin-bottom: 20px;padding-top: 5px;padding-left: 10px;">
      <a-tabs v-model="tabPanel" @change="callback">
        <a-tab-pane :key="1" tab="活动列表"></a-tab-pane>
        <a-tab-pane :key="2" tab="数据详情"></a-tab-pane>
      </a-tabs>
    </div>
    <div class="add-row">
      <div class="btn">
        <router-link :to="{path: '/roomFission/create'}">
          <a-button type="primary">创建活动</a-button>
        </router-link>
      </div>
      <div class="search">
        <a-input-search
          placeholder="请输入要搜索的活动"
          allow-clear
          v-model="search"
          @search="searchShakyName"
          @change="emptyShaky"
        />
      </div>
    </div>
    <a-card>
      <a-table :columns="table.columns" :data-source="table.data">
        <div class="btn-group" slot="operating" slot-scope="row">
          <a @click="$router.push('/roomFission/invite?id='+row.id)">邀请客户参与</a>
          <a-divider type="vertical"/>
          <a @click="$refs.details.show(row.id)">详情</a>
          <a-divider type="vertical"/>
          <a-popover trigger="click" placement="bottomRight">
            <template slot="content">
              <div class="divider-btn" @click="$router.push('/roomFission/update?id='+row.id)">修改</div>
              <div class="divider-btn" @click="del(row.id)">删除</div>
            </template>
            <a>编辑</a>
            <a-icon type="caret-down" :style="{ color: '#1890ff' }"/>
          </a-popover>
        </div>
        <div slot="status" slot-scope="row">
          {{ row.status === 1 ? '进行中' : '已结束' }}
        </div>
      </a-table>
    </a-card>
    <Details ref="details"/>
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { indexApi, destroyApi } from '@/api/roomFission'
import Details from './components/list/details'

export default {
  components: { Details },
  data () {
    return {
      // 选中面板
      tabPanel: 1,
      table: {
        columns: [
          {
            title: '活动名称',
            dataIndex: 'activeName'
          },
          {
            title: '活动总进群人数',
            dataIndex: 'join_user_num'
          },
          {
            title: '已完成活动人数',
            dataIndex: 'finish_user_num'
          },
          {
            title: '创建时间',
            dataIndex: 'createdAt'
          },
          {
            title: '结束时间',
            dataIndex: 'endTime'
          },
          {
            title: '状态',
            scopedSlots: { customRender: 'status' }
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'operating' }
          }
        ],
        data: []
      },
      search: ''
    }
  },
  created () {
    this.getShakyTable()
  },
  methods: {
    // 切换面板
    callback () {
      if (this.tabPanel == 2) {
        this.$router.push({ path: '/roomFission/dataShow' })
      }
    },
    // 搜索规则
    searchShakyName () {
      this.getShakyTable({
        name: this.search
      })
    },
    emptyShaky (e) {
      if (this.search == '') {
        this.getShakyTable()
      }
    },
    // 获取活动列表
    getShakyTable (params = {}) {
      indexApi(params).then((res) => {
        this.table.data = res.data.list
      })
    },
    /**
     * 删除
     */
    del (id) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          destroyApi({ id }).then((res) => {
            that.$message.success('删除成功')
            that.getShakyTable()
          })
        }
      })
    },

    /**
     * 什么是群裂变
     */
    showHelp () {
      window.open('/')
    }
  }
}
</script>

<style lang="less" scoped>
.add-row {
  display: flex;
  margin-bottom: 16px;

  .btn {
    flex: 1;

    .ant-btn {
      margin-right: 16px;
    }
  }

  span {
    cursor: pointer;
  }
}

.btn-group {
  font-size: 13px;
}

.divider-btn {
  height: 33px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

/deep/ .ant-card-body {
  padding: 0;
}

/deep/ .ant-table-pagination.ant-pagination {
  margin-right: 20px;
}

/deep/ .ant-popover-inner-content {
  padding: 0 !important;
}
</style>
