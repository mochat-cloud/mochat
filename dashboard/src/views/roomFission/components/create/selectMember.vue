<template>
  <div class="details">
    <a-modal v-model="modalShow" on-ok="handleOk" :width="636" :footer="false" centered>
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
            <a-input-search placeholder="请输入成员昵称"/>
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
                <a-checkbox/>
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
              <div class="radio">
                <a-icon type="close" />
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="confirm">
        <a-button @click="modalShow = false">取消</a-button>
        <a-button type="primary">确定</a-button>
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
      members: [
        {
          avatar: 'https://wework.qpic.cn/bizmail/0SuDe7Ag98Px6NFH3WNbLlNslvV0EuJP5PNJsE2lkBmaOrcnRGzfoQ/60',
          name: '中锐科技1'
        },
        {
          avatar: 'https://wework.qpic.cn/bizmail/0SuDe7Ag98Px6NFH3WNbLlNslvV0EuJP5PNJsE2lkBmaOrcnRGzfoQ/60',
          name: '中锐科技2'
        }
      ],
      selectedMembers: []
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
  height: 356px;
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
