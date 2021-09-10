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
          <a-date-picker v-model="form.date" :disabled="!isModify" class="mr14"/>
          <a-time-picker format="HH:mm" v-model="form.time" use24-hours/>
          <span>
            提醒群主发送
          </span>
        </div>
      </div>
      <div class="content mt18">
        <div>
          设置发送内容：
        </div>
        <div class="mt8" v-for="(v,i) in listArr" :key="i">
          <div class="content-box">
            <span>
              消息{{ i + 1 }}：
            </span>
            <div class="radio">
              <a-radio-group name="radioGroup" v-model="v.type" @change="updatePage">
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
          <div class="content-text mt10" v-show="v.type === 'text'">
            <a-textarea v-model="v.content" :rows="4" />
          </div>
          <div class="content-img mt10" v-show="v.type === 'image'">
            <upload :file-type="1" :ref="`sendImg${i}`" v-model="v.pic"></upload>
          </div>
          <div class="content-link mt10" v-show="v.type === 'link'">
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
              <upload :file-type="1" :ref="`linkImg${i}`" v-model="v.link_cover"></upload>
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
            <div class="checkbox-row" v-show="v.tagShow">
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
import upload from '@/components/MaUpload'
import moment from 'moment'
export default {
  components: { LabelGroup, upload },
  data () {
    return {
      abc: '',
      modalShow: false,
      isModify: false,
      form: {
        name: '',
        date: moment(new Date()).add('year', 0),
        time: moment()
      },
      listArr: [
        {
          type: 'text',
          tags: [],
          content: ''
        }
      ],
      currentContentIndex: '',
      type: 'add',
      dateDisabled: true
    }
  },
  methods: {
    // 更新页面
    updatePage () {
      this.$forceUpdate()
    },
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
        time: this.form.time.format('HH:mm')
      }
      if (this.form.id) {
        form.id = this.form.id
      }
      for (let i = 0; i < this.listArr.length; i++) {
        if (this.listArr[i].type == 'text' && this.listArr[i].content == '') {
          this.$message.warning('消息' + (i + 1) + '请设置发送内容')
          return false
        }
        if (this.listArr[i].type == 'image' && (this.listArr[i].pic == '' || this.listArr[i].pic == undefined)) {
          this.$message.warning('消息' + (i + 1) + '请设置发送内容')
          return false
        }
        if (this.listArr[i].type == 'link') {
          const rep = /^(((ht|f)tps?):\/\/)?[\w-]+(\.[\w-]+)+([\w.,@?^=%&:/~+#-]*[\w@?^=%&/~+#-])?$/
          if (!rep.test(this.listArr[i].url)) {
            this.$message.error('请填写正确的链接地址')
            return false
          }
          if (this.listArr[i].link_title == '' || this.listArr[i].link_title == undefined) {
            this.$message.error('链接标题不能为空')
            return false
          }
          if (this.listArr[i].link_description == '' || this.listArr[i].link_description == undefined) {
            this.$message.error('链接摘要不能为空')
            return false
          }
          if (this.listArr[i].link_cover == '' || this.listArr[i].link_cover == undefined) {
            this.$message.error('请上传链接封面')
            return false
          }
          if (this.listArr[i].tagShow && this.listArr[i].tags.length == 0) {
            this.$message.error('请选择客户标签')
            return false
          }
        }
      }
      this.$emit('change', {
        form,
        list: this.listArr,
        type: this.type
      })
      this.hide()
      this.isModify = true
    },
    show (date = '', dateDisabled = true) {
      this.form.date = date || moment()
      this.modalShow = true
      this.dateDisabled = dateDisabled
      this.type = 'add'
      this.isModify = false
    },
    // 修改
    modifyShow (data) {
      this.modalShow = true
      this.isModify = true
      console.log(data)
      this.form.id = data.id
      this.form.name = data.name
      this.form.date = moment(data.date)
      this.form.time = moment(data.time, 'HH:mm')
      this.listArr = []
      data.pushContent.forEach((item, index) => {
        this.listArr[index] = JSON.parse(JSON.stringify(item))
        if (item.type == 'image') {
          this.$nextTick(() => {
            this.$refs[`sendImg${index}`][0].showImg(item.pic)
          })
          this.listArr[index].pic = item.path
        }
        if (item.type == 'link') {
          this.$nextTick(() => {
            this.$refs[`linkImg${index}`][0].showImg(item.path)
          })
        }
      })
      this.type = 'edit'
    },
    hide () {
      this.modalShow = false
      this.form = {
        name: '',
        date: moment(new Date()).add('year', 0),
        time: moment()
      }
      this.listArr = [
        {
          type: 'text',
          tags: []
        }
      ]
      this.isModify = true
    },
    choiceTagsArr (e) {
      this.listArr[this.currentContentIndex].tags = JSON.parse(JSON.stringify(e))
    },

    tagShow (index) {
      this.$refs.LabelGroup.show()
      this.currentContentIndex = index
    },
    addContent () {
      this.listArr.push({
        type: 'text',
        tags: [],
        content: ''
      })
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

/deep/ .ant-modal-body {
  height: 577px;
  overflow: auto;
}
textarea{
  resize:none;
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
