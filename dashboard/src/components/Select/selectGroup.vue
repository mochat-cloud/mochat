<template>
  <div class="select-group">
    <a-modal v-model="modalShow" on-ok="handleOk" :width="526" :footer="false">
      <template slot="title">
        选择群聊
      </template>
      <div class="content">
        <div class="tips">
          全部群聊（{{ groups.length }}）：
        </div>
        <div class="groups">
          <a-radio-group name="radioGroup" v-model="groupRadio">
            <div class="item" v-for="(v,i) in groups" :key="i">
              <div style="display: flex;">
                <div class="icon">
                  <img src="../../assets/avatar-room-default.svg">
                </div>
                <div class="info">
                  <div class="name">
                    {{ v.name }}
                  </div>
                  <div class="count">
                    {{ v.contact_num }} / {{ v.roomMax }}
                  </div>
                </div>
              </div>
              <div class="select">
                <a-radio @change="selectChange($event,v)" :value="i"/>
              </div>
            </div>
          </a-radio-group>
        </div>
        <div class="confirm">
          <a-button @click="modalShow = false">取消</a-button>
          <a-button type="primary" @click="confirm">确定</a-button>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script>
import { optCroup } from '@/api/workRoom'

export default {
  data () {
    return {
      groupRadio: '',
      modalShow: false,
      loading: false,
      groups: [],
      currentData: {}
    }
  },
  methods: {
    setSelect (data, currentData = {}) {
      this.currentData = currentData

      this.getData(_ => {
        this.reset()
        data.forEach(v => {
          this.groups.forEach(v2 => {
            if (v.id === v2.id) {
              v2.select = true
            }
          })
        })
      })
      this.modalShow = true
    },
    selectChange (e, data) {
      data.select = e.target.checked
    },
    confirm () {
      this.hide()
      const select = this.groups.filter(v => {
        return v.select === true
      })
      this.$emit('change', select, this.currentData)
    },

    getData (fn = null) {
      // type: 2,
      optCroup({
        roomGroupId: 0,
        page: 1,
        perPage: 1000
      }).then(res => {
        this.groups = res.data

        if (fn) fn()
      })
    },

    show () {
      this.getData()
      this.reset()
      this.groupRadio = ''
      this.modalShow = true
    },

    hide () {
      this.modalShow = false
    },

    reset () {
      for (const group of this.groups) {
        group.select = false
      }
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
  .ant-radio-group{
    width: 100%;
  }
  .item {
    display: flex;
    width: 100%;
    justify-content: space-between;
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
