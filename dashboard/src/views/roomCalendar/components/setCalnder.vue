<template>
  <div class="details">
    <a-modal v-model="modalShow" :width="700" @ok="ok" @cancel="hide" centered>
      <template slot="title">
        添加推送内容
      </template>
      <div class="name">
        <div class="mb10">
          设置内容名称：
        </div>
        <div>
          <a-input v-model="form.name" placeholder="设置内容名称，仅内部可见" style="width: 400px"/>
        </div>
      </div>
      <div class="time mt18">
        <div>
          设置发送时间：
        </div>
        <div class="mt8">
          <a-date-picker v-model="form.date" :disabled="dateDisabled" class="mr14"/>
          <a-time-picker format="h:mm" v-model="form.time" use24-hours/>
          <span>
            提醒群主发送
          </span>
        </div>
      </div>
      <div class="content mt18">
        <div>
          设置发送内容：
        </div>
        <div class="mt8" v-for="(v,i) in list" :key="i">
          <div class="content-box">
            <span>
              消息{{ i + 1 }}：
            </span>
            <div class="radio">
              <a-radio-group name="radioGroup" v-model="v.type">
                <a-radio value="text">
                  文字
                </a-radio>
                <a-radio value="image">
                  图片
                </a-radio>
                <a-radio value="link">
                  链接
                </a-radio>
              </a-radio-group>
            </div>
          </div>
          <div class="content-text mt10" v-if="v.type === 'text'">
            <m-enter-text :defText="false" v-model="v.content"/>
          </div>
          <div class="content-img mt10" v-if="v.type === 'image'">
            <m-upload v-model="v.pic"></m-upload>
          </div>
          <div class="content-link mt10" v-if="v.type === 'link'">
            <div class="row">
              <span>链接地址：</span>
              <a-input placeholder="链接地址" v-model="v.url"></a-input>
            </div>
            <div class="row">
              <span>链接标题：</span>
              <a-input placeholder="链接标题" v-model="v.link_title"></a-input>
            </div>
            <div class="row">
              <span>链接摘要：</span>
              <a-input placeholder="链接摘要" v-model="v.link_description"></a-input>
            </div>
            <div class="row">
              <span>链接封面：</span>
              <m-upload type="btn" :preview="false" v-model="v.link_cover"/>
            </div>
            <div class="checkbox-row">
              <a-checkbox v-model="v.action_notice"/>
              <div class="tips">
                行为通知 （当客户点击雷达链接时，发送雷达链接的员工将会收到消息提醒）
              </div>
            </div>
            <div class="checkbox-row">
              <a-checkbox v-model="v.dynamic_notice"/>
              <div class="tips">
                动态通知（当客户点击雷达链接时，会将客户的打开行为记录在客户动态里）
              </div>
            </div>
            <div class="checkbox-row">
              <a-checkbox v-model="v.tagShow"/>
              <div class="tips">
                客户标签 （给点击雷达链接的客户打上选中的标签）
              </div>
            </div>
            <div class="checkbox-row" v-if="v.tagShow">
              <a-button @click="tagShow(i)">选择标签</a-button>
              <div class="ml16">
                <a-tag v-for="tag in v.tags" :key="tag.wxContactTagId" style="width: auto">
                  {{ tag.name }}
                </a-tag>
              </div>
            </div>
            <div class="checkbox-row">
              <a-checkbox v-model="v.save_radar_link"/>
              <div class="tips">
                保存为雷达链接
              </div>
            </div>
          </div>
        </div>
        <a-button type="primary" class="mt16" @click="addContent">
          添加发送内容
        </a-button>
      </div>
    </a-modal>

    <LabelGroup ref="LabelGroup" @choiceTagsArr="choiceTagsArr"/>
  </div>
</template>
<script>
import LabelGroup from '@/components/addlabel'
import moment from 'moment'

export default {
  data () {
    return {
      modalShow: false,
      form: {
        name: '',
        date: moment(new Date()).add('year', 0),
        time: moment()
      },
      list: [
        {
          type: 'text',
          tags: []
        }
      ],
      currentContentIndex: '',
      type: 'add',
      dateDisabled: true
    }
  },
  methods: {
    ok () {
      if (!this.form.name) {
        this.$message.error('内容名称未填写')

        return false
      }

      if (!this.form.date) {
        this.$message.error('日期未选择')

        return false
      }

      if (!this.form.time) {
        this.$message.error('时间未选择')

        return false
      }

      const form = {
        name: this.form.name,
        date: this.form.date.format('YYYY-MM-DD'),
        time: this.form.time.format('h:mm')
      }

      this.$emit('change', {
        form,
        list: this.list,
        type: this.type
      })

      this.hide()
    },

    choiceTagsArr (e) {
      this.list[this.currentContentIndex].tags = JSON.parse(JSON.stringify(e))
    },

    tagShow (index) {
      this.$refs.LabelGroup.show()
      this.currentContentIndex = index
    },

    addContent () {
      this.list.push({
        type: 'text',
        tags: [],
        content: ''
      })
    },

    editShow (data) {
      const newData = JSON.parse(JSON.stringify(data))

      newData.form.time = moment(newData.form.time, 'h:mm')
      newData.form.date = moment(newData.form.date)

      this.form = newData.form
      this.list = newData.list

      this.modalShow = true

      this.type = 'edit'
    },

    show (date = '', dateDisabled = true) {
      this.form.date = date || moment()

      this.modalShow = true
      this.dateDisabled = dateDisabled

      this.type = 'add'
    },

    hide () {
      this.modalShow = false

      this.form = {
        name: '',
        date: moment(new Date()).add('year', 0),
        time: moment()
      }

      this.list = [
        {
          type: 'text',
          tags: []
        }
      ]
    }
  },
  components: { LabelGroup }
}
</script>

<style lang="less" scoped>
/deep/ .ant-modal-title {
  text-align: center;
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 1px;
}

/deep/ .ant-modal-body {
  height: 577px;
  overflow: auto;
}

.elastic {
  margin-top: 14px;
  display: flex;
  align-items: center;
}

.shop-address {
  display: flex;
  margin-top: 14px;
}

.maps-bottom {
  width: 540px;
  height: 260px;
  background-color: #8d8d8d;
}

/deep/ .ant-modal-body {
  height: 300px;
}

.preview-box {
  width: 200px;
  height: 90px;
  border: 1px solid #e7e7e7;
  padding: 6px;
  background-color: #fff;
}

.mode-one,
.mode-two {
  display: flex;
  background-color: #f6f6f6;
  padding: 14px;
}

.preview-bottom {
  display: flex;
}

.download {
  display: block;
  font-size: 10px;
  margin-left: 24px;
  margin-top: 6px;
}

.mode-bottom {
  display: flex;
}

.link {
  background-color: #ffffff;
  width: 260px;
  height: 110px;
  padding: 2px 10px;
}

.copy {
  display: block;
  margin-left: 212px;
  margin-top: 6px;
  font-size: 10px;
}

/deep/ .ant-modal-body {
  height: 500px;
}

.search-bottom {
  display: flex;
  align-items: center;
  margin-top: 4px;
  margin-bottom: 12px;
}

.bottom-title {
  flex: 1;
}

.check {

  input {
    margin-right: 2px;
  }
}

.list-data {
  display: flex;
  border-bottom: 1px dashed #bdbdbd;
  padding-bottom: 10px;

  .left {
    flex: 1;
    display: flex;

    .name-box {
      .group-synopsis {
        font-size: 10px;
        color: #a3a3a3;
      }
    }
  }
}

.content-link {
  span {
    width: 100px;
  }

  .row {
    width: 50%;
    display: flex;
    align-items: center;
    margin-bottom: 13px;
  }
}

.checkbox-row {
  display: flex;
  align-items: center;
  margin-bottom: 8px;

  .tips {
    width: 100%;
    margin-left: 6px;
  }
}

.content-box {
  display: flex;
}
</style>
