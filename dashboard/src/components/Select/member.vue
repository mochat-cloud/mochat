<template>
  <div class="details">
    <a-modal
      v-model="modalShow"
      on-ok="handleOk"
      :width="636"
      :footer="false"
      centered
      :maskClosable="false">
      <template slot="title">
        选择成员
      </template>
      <div class="content">
        <div class="left-content">
          <div class="tips">
            没有想要添加的成员？
            <a href="#">添加成员教程</a>
          </div>
          <div class="search">
            <a-input-search placeholder="请输入成员昵称" @search="search"/>
          </div>
          <div class="member">
            <div class="item" v-for="(v,i) in members" :key="i">
              <div class="user-info">
                <div class="avatar">
                  <img :src="v.avatar">
                </div>
                <div class="name">
                  {{ v.name }}
                </div>
              </div>
              <div class="radio">
                <a-checkbox @change="selectChange(v)" v-model="v.select"/>
              </div>
            </div>
          </div>
        </div>
        <div class="right-content">
          <div class="tips">
            已选成员：（{{ selectedMembers.length }}）
          </div>
          <div class="member">
            <div class="item" v-for="(v,i) in selectedMembers" :key="i">
              <div class="user-info">
                <div class="avatar">
                  <img :src="v.avatar">
                </div>
                <div class="name">
                  {{ v.name }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="confirm">
        <a-button @click="modalShow = false">取消</a-button>
        <a-button type="primary" @click="go">确定</a-button>
      </div>
    </a-modal>
  </div>
</template>

<script>
import { department } from '@/api/channelCode'

export default {
  data () {
    return {
      modalShow: false,
      loading: false,
      members: [],
      selectedMembers: [],
      currentData: {}
    }
  },
  methods: {
    setSelect (data, currentData = {}) {
      this.selectedMembers = []
      data.forEach((item, index) => {
        this.selectedMembers[index] = item
      })
      this.currentData = currentData
      this.getData('', _ => {
        this.selectedMembers.forEach(v => {
          this.members.forEach(v2 => {
            if (v.wxUserId === v2.wxUserId) {
              if (!v.avatar) v.avatar = v2.avatar
              v2.select = true
            }
          })
        })
      })
      this.reset()
      this.modalShow = true
    },
    search (e) {
      this.getData(e)
    },
    selectChange (e) {
      let index = -1
      const find = this.selectedMembers.filter((v, i) => {
        if (v.id === e.id) {
          index = i
          return v
        }
      })
      if (find.length) {
        this.selectedMembers.splice(index, 1)
        return false
      }
      this.selectedMembers.push(e)
    },
    reset () {
      for (const member of this.members) {
        member.select = false
      }
    },
    go () {
      this.hide()
      this.$emit('change', this.selectedMembers)
    },
    getData (key = '', fn = null) {
      department({
        searchKeyWords: key
      }).then(res => {
        this.members = res.data.employee
        if (fn) fn()
      })
    },
    show () {
      this.getData()
      this.reset()
      this.selectedMembers = []
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
  margin-top: 24px;
  padding-right: 26px;
  display: flex;
  justify-content: flex-start;
  height: 355px;

  .left-content {
    flex: 1;
  }

  .right-content {
    border-left: 1px solid #e8e8e8;
    flex: 1;
    padding-left: 15px;

    .tips {
      margin-bottom: 16px;
    }
  }

  .search {
    padding-right: 40px;
  }
}

.search {
  margin: 13px 0 20px;

  input {
    height: 28px;
  }
}

.member {
  height: 314px;
  overflow-y: auto;

  &::-webkit-scrollbar {
    width: 5px;
    height: 1px;
  }

  &::-webkit-scrollbar-thumb {
    border-radius: 10px;
    box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
    background: #d7d7d7;
  }

  &::-webkit-scrollbar-track {
    box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
    border-radius: 10px;
    background: #ededed;
  }

  .item {
    margin-bottom: 10px;
    display: flex;
    align-items: center;

    .radio {
      margin-right: 20px;
    }

    .user-info {
      display: flex;
      align-items: center;
      flex: 1;

      .avatar img {
        width: 35px;
        height: 35px;
        margin-right: 10px;
        border-radius: 2px;
      }
    }
  }
}

.confirm {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;

  button {
    margin-right: 10px;
  }
}

/deep/ .ant-modal-title {
  text-align: center;
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 1px;
}
</style>
