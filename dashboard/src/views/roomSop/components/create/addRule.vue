<template>
  <div class="details">
    <a-modal v-model="modalShow" on-ok="handleOk" :width="767" :footer="false" centered>
      <template slot="title">
        设置推送内容
      </template>
      <div class="block">
        <div class="title">
          <span class="bar"/>
          设置内容名称
        </div>
        <div class="block-content">
          <a-input placeholder="设置内容名称，仅内部可见" v-model="form.name"/>
        </div>
      </div>
      <div class="block">
        <div class="title">
          <span class="bar"/>
          设置发送时间
        </div>
        <div class="block-content">
          <a-alert type="info" message="当客户添加客服成员时，系统将在侧边栏快捷回复中智能提醒该规则内容" banner/>
          <div class="radio flex mb16 mt16">
            <a-radio
              value="0"
              :checked="sendTimeCurrent === '0'"
              @change="sendTimeChange"
            />
            加入规则后
            <div class="input-number">
              <a-input-number
                v-model="form.hour.first"
                size="small"
                :min="0"
                :max="100"
              />
            </div>
            小时
            <div class="input-number">
              <a-input-number
                v-model="form.hour.last"
                size="small"
                :min="1"
                :max="100"
              />
            </div>
            分钟后提醒发送
          </div>
          <div class="radio flex">
            <a-radio
              value="1"
              :checked="sendTimeCurrent === '1'"
              @change="sendTimeChange"
            />
            加入规则后
            <div class="input">
              <a-input-number
                v-model="form.day.first"
                size="small"
                :min="1"
                :max="100"
              />
            </div>
            天后，当日
            <div class="input">
              <a-time-picker
                v-model="form.day.last"
                format="HH:mm"
                style="width: 110px;"
              />
            </div>
            提醒发送
          </div>
        </div>
      </div>
      <div class="block">
        <div class="title">
          <span class="bar"/>
          设置发送内容
        </div>
        <div class="block-content">
          <div class="content-item" v-for="(v,i) in list" :key="i">
            <div class="flex">
              <div class="select">
                消息{{ i + 1 }}：
                <a-radio-group
                  :options="contentRadio.list"
                  v-model="v.type"
                  @change="v.value = ''"
                />
              </div>
              <div class="del">
                <a @click="delContent(i)">删除</a>
              </div>
            </div>
            <div class="input ml50">
              <div class="content mt16" v-if="v.type === 'text'">
                <textarea placeholder="请输入文字" v-model="v.value"></textarea>
              </div>
              <div class="content-img mt16" v-if="v.type === 'image'">
                <m-upload :def="false" text="请上传图片" v-model="v.value"/>
              </div>
            </div>
          </div>
        </div>
        <a-button
          class="ml65 mt16"
          type="primary"
          ghost
          @click="addContent"
        >
          添加发送内容
        </a-button>
      </div>
      <div class="footer">
        <a-button class="mr16" @click="hide()">
          取消
        </a-button>
        <a-button key="submit" type="primary" @click="confirm">
          确定
        </a-button>
      </div>
    </a-modal>
  </div>
</template>

<script>
import moment from 'moment'

export default {
  data () {
    return {
      modalShow: false,
      loading: false,
      sendTimeCurrent: '0',
      contentRadio: {
        value: 'text',
        list: [
          { label: '文字', value: 'text' },
          { label: '图片', value: 'image' }
        ]
      },
      list: [
        { type: 'text', value: '' }
      ],
      form: {
        name: '',
        hour: {
          first: '',
          last: ''
        },
        day: {
          first: '',
          last: ''
        }
      },
      type: 'add',
      editIndex: ''
    }
  },
  methods: {
    confirm () {
      const form = JSON.parse(JSON.stringify(this.form))

      if (!form.name) {
        this.$message.error('名称未填写')

        return false
      }

      if (this.sendTimeCurrent === '0') {
        if (!form.hour.first || !form.hour.last) {
          this.$message.error('提醒发送时间未填写')

          return false
        }
      }

      if (this.sendTimeCurrent === '1') {
        if (!form.day.first || !form.day.last) {
          this.$message.error('提醒发送时间未填写')

          return false
        }
      }

      for (const v of this.list) {
        if (!v.value) {
          this.$message.error('发送内容为空')

          return false
        }
      }

      const params = {
        name: form.name,
        time: {
          type: this.sendTimeCurrent,
          data: {}
        },
        content: this.list
      }

      if (this.sendTimeCurrent === '0') {
        params.time.data = form.hour
      } else {
        params.time.data = form.day
        params.time.data.last = moment(params.time.data.last).format('HH:mm')
      }

      if (this.type === 'add') {
        this.$emit('change', params)
      } else {
        this.$emit('edit', {
          ...params,
          index: this.editIndex
        })
      }

      this.hide()
    },

    delContent (i) {
      if (this.list.length === 1) {
        this.$message.error('不能删除最后一个')

        return false
      }

      this.list.splice(i, 1)
    },

    addContent () {
      this.list.push({ type: 'text', value: '' })
    },

    sendTimeChange (e) {
      this.sendTimeCurrent = e.target.value
    },

    editShow (data, index) {
      this.type = 'edit'
      this.editIndex = index
      this.modalShow = true

      this.sendTimeCurrent = data.time.type

      if (data.time.type === '0') {
        this.form.hour = data.time.data
      } else {
        this.form.day = data.time.data
      }

      this.form.name = data.name

      this.list = data.content
    },

    show () {
      this.type = 'add'

      this.modalShow = true
    },

    hide () {
      this.modalShow = false

      this.form = {
        name: '',
        hour: {
          first: '',
          last: ''
        },
        day: {
          first: '',
          last: ''
        }
      }

      this.list = [{ type: 'text', value: '' }]
      this.sendTimeCurrent = '0'
    }
  }
}
</script>

<style lang="less" scoped>
/deep/ .ant-modal-title {
  text-align: center;
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 1px;
}

.block {
  margin-bottom: 16px;

  .title {
    font-weight: 600;
    display: flex;
    align-items: center;
    color: #333;

    .bar {
      display: block;
      width: 3px;
      height: 12px;
      margin-right: 4px;
      background: #1990ff;
    }
  }

  .block-content {
    margin-top: 15px;
    padding-left: 15px;

    .item {
      display: flex;
      align-items: center;
      margin-bottom: 16px;
    }
  }
}

.radio {
  .input {
    margin: 0 5px;
  }

  .ant-input-number {
    width: 66px;
  }

  .ant-time-picker {
    width: 78px;
    height: 25px;
  }

  /deep/ input {
    height: 25px;
  }
}

.content {
  padding: 16px 12px;
  width: 650px;
  height: 112px;
  background: #fbfbfb;
  border: 1px solid #eee;

  textarea {
    width: 100%;
    height: 100%;
    border: none;
    resize: none;
    outline: none;
    font-size: 14px;
    font-weight: 400;
    color: rgba(0, 0, 0, .85);
    line-height: 20px;
    box-sizing: border-box;
    overflow: auto;
    background: #fbfbfb;
  }

  input {
    width: 100%;
    border: none !important;
    background: #fbfbfb;
    outline: none;
    box-shadow: none;
  }
}

.content-item {
  margin-bottom: 16px;

  .select {
    flex: 1;
  }
}

.content-link {
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.footer {
  display: flex;
  justify-content: flex-end;
}
</style>
