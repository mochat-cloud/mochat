<template>
  <div class="contact-sop-create">
    <a-card>
      <div class="block">
        <div class="title">规则基本信息</div>
        <div class="item">
          <span class="label required">规则名称：</span>
          <div class="input">
            <a-input placeholder="请输入规则名称" v-model="name"></a-input>
          </div>
        </div>
      </div>
      <div class="block">
        <div class="title">设置推送周期及内容</div>
        <div class="desc-text mb16">
          设置规则后将会通过「MoChat提醒」提醒员工发送推送内容
        </div>
        <div class="mb16">
          <a-button type="primary" ghost @click="$refs.addRule.show()">添加规则</a-button>
        </div>
        <div class="rules">
          <div class="rule" v-for="(v,i) in list" :key="i">
            <div class="header flex">
              <div class="icon">
                <a-icon type="bell"/>
              </div>
              <div class="name">
                客户添加企业微信客服后，
                <span v-if="v.time.type === '0'">
                  {{ v.time.data.first }}小时{{ v.time.data.last }}分
                </span>
                <span v-if="v.time.type === '1'">
                  {{ v.time.data.first }}天后，当日{{ v.time.data.last }}
                </span>
                提醒发送
              </div>
            </div>
            <div class="content flex">
              <div class="warp">
                <div
                  class="text-content"
                  v-for="(content,contentIndex) in v.content"
                  :key="contentIndex"
                >
                  <div v-if="content.type === 'text'">
                    {{ content.value }}
                  </div>
                  <div v-if="content.type === 'image'">
                    <img :src="content.value" style="max-width: 80px;">
                  </div>
                </div>
                <div class="count">
                  共{{ v.content.length }}条
                </div>
              </div>
              <div class="btn-box mr10 ml16" @click="delContent(i)">
                <a-icon type="delete"/>
              </div>
              <div class="btn-box" @click="$refs.addRule.editShow(v,i)">
                <a-icon type="edit"/>
              </div>
            </div>
          </div>
        </div>
      </div>
      <a-button type="primary" size="large" @click="confirm">
        保存规则
      </a-button>
    </a-card>

    <addRule ref="addRule" @change="e => list.push(e)" @edit="editContentChange"/>
  </div>
</template>

<script>
import addRule from '@/views/contactSop/components/create/addRule'
import { edit, getInfo } from '@/api/roomSop'

export default {
  data () {
    return {
      name: '',
      list: []
    }
  },
  mounted () {
    this.getData()
  },
  methods: {
    getData () {
      getInfo({
        id: this.$route.query.id
      }).then(res => {
        this.name = res.data.name
        this.members = res.data.employees
        this.list = res.data.setting
      })
    },

    confirm () {
      if (!this.name) {
        this.$message.error('名称未填写')

        return false
      }

      if (!this.list.length) {
        this.$message.error('未添加规则')

        return false
      }

      edit({
        id: this.$route.query.id,
        name: this.name,
        setting: JSON.stringify(this.list)
      }).then(res => {
        this.$message.success('保存成功')

        this.$router.push('/roomSop/index')
      })
    },

    editContentChange (e) {
      this.list[e.index] = e
    },

    delContent (i) {
      this.list.splice(i, 1)
    }
  },
  components: { addRule }
}
</script>

<style lang="less" scoped>
.block {
  margin-bottom: 60px;

  .title {
    font-size: 15px;
    line-height: 21px;
    color: rgba(0, 0, 0, .85);
    border-bottom: 1px solid #e9ebf3;
    padding-bottom: 16px;
    margin-bottom: 16px;
    position: relative;

    span {
      font-size: 13px;
      margin-left: 11px;
      color: rgba(0, 0, 0, .45);
      font-weight: 400;
    }
  }

  .required:after {
    content: "*";
    display: inline-block;
    margin-right: 4px;
    color: #f5222d;
    font-size: 14px;
    line-height: 1;
    position: absolute;
    left: -10px;
    top: 6px;
  }

  .item {
    display: flex;
    align-items: center;
    margin-bottom: 23px;

    .label {
      color: rgba(0, 0, 0, .85);
      position: relative;
    }
  }
}

.rules {
  .rule {
    .header {
      .icon {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        -webkit-transform: translateX(-40%);
        transform: translateX(-40%);
        color: #b4b9c3;
      }

      .name {
        font-size: 14px;
        color: rgba(0, 0, 0, .85);
        font-weight: 500;
      }
    }

    .content {
      padding: 8px 16px 24px 21px;
      border-left: 1px dashed #d8dfe4;

      .warp {
        width: 659px;
        background: #fbfbfb;
        border: 1px solid #f2f2f2;
        padding: 0 20px 8px 16px;

        .text-content {
          background: #fff;
          box-shadow: 0 0 2px 0 rgb(0 0 0 / 4%);
          border-radius: 2px 16px 16px 24px;
          border: 1px solid #f6f6f6;
          padding: 10px 16px;
          word-break: break-all;
          font-size: 14px;
          font-weight: 400;
          color: rgba(0, 0, 0, .86);
          line-height: 20px;
          margin-top: 12px;
        }

        .img-content img {
          margin-top: 12px;
          width: 80px;
          height: 80px;
        }

        .pdf-content {
          display: flex;
          align-items: center;
          margin-top: 8px;
          padding: 11px 12px;
          width: 250px;
          background: #fff;
          border-radius: 1px;
          border: 1px solid #f0f0f0;

          img {
            width: 58px;
            height: 58px;
            margin-right: 13px;
          }

          .info {
            p {
              font-size: 13px;
              font-weight: 500;
              color: rgba(0, 0, 0, .85);
              margin-bottom: 0;
            }

            .size {
              font-size: 12px;
              color: #999;
            }
          }
        }

        .link-content {
          width: 250px;
          margin-top: 8px;
          background: #fff;
          border: 1px solid #f0f0f0;
          padding: 10px 12px 13px;

          .link-title {
            font-size: 13px;
            color: #191919;
          }

          .info {
            .desc {
              font-size: 12px;
              color: #999;
              line-height: 16px;
              padding-right: 19px;
              overflow: hidden;
              text-overflow: ellipsis;
              display: -webkit-box;
              -webkit-line-clamp: 2;
              -webkit-box-orient: vertical;
            }

            img {
              width: 47px;
              height: 47px;
            }
          }
        }

        .count {
          font-size: 13px;
          font-weight: 400;
          line-height: 24px;
          margin-right: 12px;
          color: rgba(0, 0, 0, .45);
          margin-top: 5px;
        }
      }

      .btn-box {
        width: 21px;
        height: 21px;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #f0f0f0;
        border-radius: 2px;
        cursor: pointer;
      }
    }
  }
}
</style>
