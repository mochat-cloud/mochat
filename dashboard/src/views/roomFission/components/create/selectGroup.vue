<template>
  <div class="select-group">
    <a-modal v-model="modalShow" on-ok="handleOk" :width="526" :footer="false">
      <template slot="title">
        选择群聊
      </template>
      <div class="content">
        <div class="search">
          <a-input-search placeholder="请输入群名"/>
        </div>
        <div class="tips">
          全部群聊（1）：
        </div>
        <div class="groups">
          <div class="item" v-for="v in groups" :key="v.name">
            <div class="icon">
              <img src="../../../../assets/avatar-room-default.svg">
            </div>
            <div class="info">
              <div class="name">
                {{ v.name }}
              </div>
              <div class="count">
                {{ v.count.current }}/{{ v.count.max }}
              </div>
            </div>
            <div class="select">
              <a-checkbox/>
            </div>
          </div>
        </div>
        <div class="confirm">
          <a-button @click="modalShow = false">取消</a-button>
          <a-button type="primary">确定</a-button>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script>
export default {
  data () {
    return {
      modalShow: false,
      loading: false,
      groups: [
        {
          name: '群名1',
          count: {
            current: 0,
            max: 300
          }
        },
        {
          name: '群名',
          count: {
            current: 0,
            max: 300
          }
        }
      ]
    }
  },
  methods: {
    show () {
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
