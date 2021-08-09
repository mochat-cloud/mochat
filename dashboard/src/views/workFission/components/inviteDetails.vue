<template>
  <div class="word-fission-details">
    <a-modal v-model="modalShow" on-ok="handleOk" :width="760" :footer="false" centered>
      <template slot="title">
        邀请详情
      </template>
      <div class="content">
        <div class="data-panel">
          <div class="data">
            <div class="item">
              <div class="count">
                {{ statistics.total_count }}
              </div>
              <div class="desc">
                邀请总客户数
              </div>
            </div>
            <div class="item">
              <div class="count">
                {{ statistics.new_count }}
              </div>
              <div class="desc">
                邀请新客户数
                <a-popover>
                  <template slot="content">
                    新客户数为邀请好友中所有未添加企业任意员工的人数
                  </template>
                  <a-icon type="question-circle"/>
                </a-popover>
              </div>
            </div>
            <div class="item">
              <div class="count">
                {{ statistics.loss }}
              </div>
              <div class="desc">
                流失客户数
              </div>
            </div>
            <div class="item">
              <div class="count">
                {{ statistics.insert }}
              </div>
              <div class="desc">
                净增客户总数
              </div>
            </div>
          </div>
        </div>
        <div class="table">
          <a-table
            bordered
            align="center"
            :columns="table.col"
            :data-source="table.data">
            <div slot="status" slot-scope="row">
              {{ row.loss === 0 ? '未流失' : '已流失' }}
            </div>
          </a-table>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script>
import { getInviteInfo } from '@/api/workFission'

export default {
  data () {
    return {
      modalShow: false,
      table: {
        col: [
          {
            title: '客户',
            dataIndex: 'nickname'
          },
          {
            title: '状态',
            scopedSlots: { customRender: 'status' }
          },
          {
            title: '添加时间',
            dataIndex: 'createdAt'
          }
        ],
        data: []
      },
      statistics: {
        total_count: 0,
        new_count: 0,
        loss: 0,
        insert: 0
      }
    }
  },
  methods: {
    getData (id) {
      getInviteInfo({ id }).then(res => {
        this.statistics.total_count = res.data.total_count
        this.statistics.new_count = res.data.new_count
        this.statistics.loss = res.data.loss
        this.statistics.insert = res.data.insert

        this.table.data = res.data.user_list
      })
    },

    show (id) {
      this.getData(id)
      this.modalShow = true
    },

    hide () {
      this.modalShow = false
    }
  }
}
</script>

<style lang="less" scoped>
.content {
  width: 100%;
  margin-top: 24px;
  height: 500px;
}

/deep/ .ant-modal-title {
  text-align: center;
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 1px;
}

/deep/ .ant-modal-header {
  border-bottom: none;
}

.data-panel {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
  width: 100%;

  .data {
    flex: 1;
    height: 120px;
    background: #fbfdff;
    border: 1px solid #daedff;
    padding: 31px;
    margin-right: 25px;
    display: flex;
    align-items: center;

    .item {
      flex: 1;
      border-right: 1px solid #e9e9e9;

      .count {
        font-size: 24px;
        font-weight: 500;
        text-align: center;
      }

      .desc {
        font-size: 13px;
        text-align: center;
      }

      &:last-child {
        border-right: 0;
      }
    }

    &:last-child {
      margin-right: 0;
    }
  }
}
</style>
