<template>
  <div class="room-fission-index">
    <div class="add-row">
      <div class="btn">
        <router-link :to="{path: '/workFission/create'}">
          <a-button type="primary">创建活动</a-button>
        </router-link>
      </div>
      <div class="search">
        <a-input-search placeholder="请输入要搜索的活动" @search="search"/>
      </div>
    </div>
    <a-card>
      <a-table :columns="table.columns" :data-source="table.data">
        <div class="btn-group" slot="operating" slot-scope="item">
          <a @click="goInvite(item)">邀请客户参与</a>
          <a-divider type="vertical"/>
          <a @click="$refs.details.show(item.id)">详情</a>
          <a-divider type="vertical"/>
          <a-popover trigger="click" placement="bottomRight">
            <template slot="content">
              <div class="divider-btn" @click="goUpdate(item)">修改</div>
              <div class="divider-btn" @click="del(item)">删除</div>
            </template>
            <a>编辑</a>
            <a-icon type="caret-down" :style="{ color: '#1890ff' }"/>
          </a-popover>
        </div>
        <div slot="member_use" slot-scope="item">
          <a-tag v-for="user in item.service_employees" :key="user.id">
            <a-icon type="user" two-tone-color="#7da3d1" :style="{ color: '#7da3d1' }"/>
            {{ user.name }}
          </a-tag>
        </div>
        <div slot="client_tag" slot-scope="item">
          <a-tag v-for="tag in item.contact_tags" :key="tag.id">
            {{ tag.name }}
          </a-tag>
        </div>
      </a-table>
    </a-card>

    <Details ref="details"/>
  </div>
</template>

<script>
import Details from '@/views/workFission/components/details'

import { getList, del } from '@/api/workFission'

export default {
  data () {
    return {
      table: {
        columns: [
          {
            title: '活动名称',
            dataIndex: 'active_name'
          },
          {
            title: '使用成员',
            scopedSlots: { customRender: 'member_use' }
          },
          {
            title: '扫码添加人数',
            dataIndex: 'employeeNum'
          },
          {
            title: '创建时间',
            dataIndex: 'created_at'
          },
          {
            title: '活动状态',
            dataIndex: 'status'
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'operating' }
          }
        ],
        data: []
      }
    }
  },
  mounted () {
    this.getData()
  },
  methods: {
    goInvite (data) {
      this.$router.push({
        path: '/workFission/invite',
        query: {
          id: data.id
        }
      })
    },

    goUpdate (data) {
      this.$router.push({
        path: '/workFission/edit',
        query: {
          id: data.id
        }
      })
    },

    search (e) {
      this.getData(e)
    },

    del (data) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          del({ id: data.id }).then(res => {
            if (res.code === 200) {
              that.getData()
              that.$message.success('删除成功')
            }
          })
        }
      })
    },
    getData (key = '') {
      getList({
        active_name: key
      }).then(res => {
        for (const v of res.data.list) {
          if (v.service_employees) v.service_employees = JSON.parse(v.service_employees)
          if (v.contact_tags) v.contact_tags = JSON.parse(v.contact_tags)
        }

        this.table.data = res.data.list
      })
    }
  },
  components: { Details }
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
