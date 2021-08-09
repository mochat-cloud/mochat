<template>
  <div class="select-group">
    <a-modal v-model="modalShow" :width="526" :footer="false">
      <template slot="title">
        选择群聊
      </template>
      <div class="content">
        <div class="tips">
          全部群聊（{{ groups.length }}）：
        </div>
        <div class="groups">
          <div class="item" v-for="v in groups" :key="v.wxChatId">
            <div class="icon">
              <img src="../../../assets/avatar-room-default.svg">
            </div>
            <div class="info">
              <div class="name">
                {{ v.name }}
              </div>
              <div class="count">
                {{ v.contact_num }} / {{ v.roomMax }}
              </div>
            </div>
            <div class="select">
              <a-button type="primary" @click="del(v.wxChatId)">
                将群聊移除该规则
              </a-button>
            </div>
          </div>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script>
import { delRoom } from '@/api/roomCalendar'

export default {
  data () {
    return {
      modalShow: false,
      loading: false,
      groups: [],
      id: ''
    }
  },
  methods: {
    del (wxChatId) {
      delRoom({
        id: this.id,
        chatid: wxChatId
      }).then(res => {
        this.$message.success('移除成功')
        this.hide()
        this.$emit('change')
      })
    },

    show (data, id) {
      this.groups = data
      this.id = id
      this.modalShow = true
    },

    hide () {
      this.modalShow = false
    }
  }
}
</script>

<style lang="less" scoped>
/deep/ .ant-modal-content {
  height: 549px !important;
}

.content {
  padding-right: 26px;
  height: 355px;
}

.tips {
  margin-top: 14px;
}

.groups {
  margin-top: 16px;
  width: 100%;
  height: 330px;
  overflow: auto;

  .item {
    display: flex;
    align-items: center;
    margin-bottom: 16px;

    .icon img {
      width: 40px;
      height: 40px;
      border-radius: 3px;
      margin-right: 11px;
    }

    .info {
      flex: 1;

      .name {
        color: #222;
        font-size: 14px;
        font-weight: 500;
      }

      .count {
        color: #999;
        opacity: .85;
      }
    }

    .select {
      margin-right: 20px;
    }
  }
}

.confirm {
  width: 100%;
  display: flex;
  justify-content: flex-end;

  button {
    margin-right: 10px;
  }
}

/deep/ .search {
  width: 100%;
}

/deep/ .ant-modal-title {
  text-align: center;
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 1px;
}
</style>
