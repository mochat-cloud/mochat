<template>
  <div class="room-welcome-create">
    <a-card title="设置入群欢迎语">
      <div class="content-wrap">
        <div class="content-box">
          <div class="form">
            <a-alert
              message="因企业微信限制，入群欢迎语创建上限为100条，在企业微信后台创建的也将计入其中"
              type="warning"
              show-icon
            />
            <div class="input-msg">
              <div class="title">
                欢迎语1：
              </div>
              <div class="content">
                <div class="input">
                  <div class="insert-btn-group">
                    <span @click="$refs.enterText.addUserName('[用户昵称]')">[插入客户名称]</span>
                  </div>
                  <m-enter-text ref="enterText" v-model="form.text"/>
                </div>
              </div>
            </div>
            <div class="input-msg input-type">
              <div class="title">
                欢迎语2：
              </div>
              <div class="content">
                <div class="select-type">
                  选择消息类型：
                  <a-radio-group :options="typeRadio" v-model="form.type.select" @change="switchRadio" />
                </div>
                <div class="operating">
                  <div class="image" v-show="form.type.select === 'image'">
                    <m-upload :def="false" text="请上传图片" @change="uploadImageChange" ref="uploadImg"></m-upload>
                  </div>
                  <div class="link" v-show="form.type.select === 'link'">
                    <div class="url">
                      <a-input placeholder="链接地址请以http 或https开头" v-model="form.type.link.input.url"/>
                    </div>
                    <div class="customize">
                      <div class="link-form">
                        <div class="item">
                          <span>链接标题：</span>
                          <div class="input">
                            <a-input v-model="form.type.link.input.title"/>
                          </div>
                        </div>
                        <div class="item">
                          <span>链接摘要：</span>
                          <div class="input">
                            <a-input v-model="form.type.link.input.desc"/>
                          </div>
                        </div>
                        <div class="item">
                          <span>链接封面：</span>
                          <div class="input">
                            <m-upload :def="false" text="请上传图片" @change="uploadLinkImageChange" ref="linkOverImg" ></m-upload>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="applets" v-show="form.type.select === 'miniprogram'">
                    <a-alert
                      type="info"
                      show-icon
                    >
                      <span slot="message">
                        只有在企业微信后台绑定的小程序才可在此添加哦 查看如何绑定
                      </span>
                    </a-alert>
                    <a-button @click="$refs.addApplets.show()" v-if="!form.type.applets.appid">添加小程序</a-button>
                    <div v-else>
                      小程序：{{ form.type.applets.title }}
                      <a-button @click="resetApplets">重新添加</a-button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="input-msg">
              <div class="title">
                消息提醒：
              </div>
              <div class="tips-content">
                <a-switch size="small" @change="linkCustomizeSwitchChange"/>
                <span>
                  开启后，新建该条欢迎语会通过「客户群」群发通知企业全部员工：“管理员创建了新的入群欢迎语”
                </span>
              </div>
            </div>
            <div class="add">
              <a-button type="primary" size="large" @click="addClick">{{ update.flag ? '修改' : '添加' }}</a-button>
            </div>
          </div>
          <div class="phone">
            <m-preview ref="preview"/>
          </div>
        </div>
      </div>
    </a-card>

    <AddApplets ref="addApplets" @change="addAppletsChange"/>
  </div>
</template>

<script>
import AddApplets from '../../components/Select/applets'
// eslint-disable-next-line no-unused-vars
import { add, update, getDetail } from '@/api/roomWelcome'

export default {
  data () {
    return {
      form: {
        text: '',
        type: {
          select: 'image',
          image: '',
          link: {
            customizeFlag: true,
            input: {
              url: '',
              title: '',
              desc: '',
              image: ''
            }
          },
          applets: {
            title: '',
            appid: '',
            path: '',
            image: ''
          }
        }
      },
      typeRadio: [
        { label: '图片', value: 'image' },
        { label: '链接', value: 'link' },
        { label: '小程序', value: 'miniprogram' }
      ],
      update: {
        flag: false,
        id: ''
      }
    }
  },
  watch: {
    /**
     * 欢迎语字数限制
     */
    'form.text' () {
      this.$refs.preview.setText(this.form.text)
      if (this.form.text.length > 1500) {
        this.$message.warning('最多输入1500个字')
        this.form.text = this.form.text.substring(0, 1500)
      }
    },
    /**
     * 获取链接站点信息
     */
    'form.type.link.input.url' () {
      if (this.timer) {
        clearTimeout(this.timer)
      }
      this.timer = setTimeout(() => {
        const rep = /^(((ht|f)tps?):\/\/)?[\w-]+(\.[\w-]+)+([\w.,@?^=%&:/~+#-]*[\w@?^=%&/~+#-])?$/
        const test = rep.test(this.form.type.link.input.url)
        if (!test) {
          this.$message.error('输入的不是一个链接')
          return false
        }
      }, 500)
    },
    /**
     * 欢迎语2链接表单内容监听
     */
    'form.type.link.input': {
      deep: true,
      handler: function () {
        const data = this.form.type.link.input
        this.$nextTick(() => {
          this.$refs.preview.setLink(data.title, data.desc, data.image)
        })
      }
    }
  },
  created () {
    if (this.$route.query.update === 'true') {
      this.update.flag = true
      this.update.id = this.$route.query.id
      getDetail({ id: this.update.id }).then(res => {
        const data = res.data
        data.msgComplex = JSON.parse(data.msgComplex)
        // 欢迎语1
        this.$nextTick(() => {
          this.$refs.enterText.addUserName(data.msgText)
        })
        // 欢迎语2
        if (data.complexType) {
          this.form.type.select = data.complexType
          if (data.complexType == 'image') {
            if (data.msgComplex.pic_url) {
              this.$nextTick(() => {
                this.$refs.uploadImg.setUrl(data.msgComplex.pic)
              })
            }
          } else if (data.complexType == 'link') {
            const linkInput = {
              title: data.msgComplex.title,
              desc: data.msgComplex.desc,
              url: data.msgComplex.url
            }
            this.form.type.link.input = linkInput
            this.$nextTick(() => {
              this.$refs.linkOverImg.setUrl(data.msgComplex.pic)
            })
          } else if (data.complexType == 'miniprogram') {
            const appletsData = {
              title: data.msgComplex.title,
              appid: data.msgComplex.appid,
              path: data.msgComplex.page,
              image: data.msgComplex.pic
            }
            this.$nextTick(() => {
              this.$refs.preview.setApplets(appletsData.title, appletsData.image)
            })
            this.form.type.applets = appletsData
          }
        }
      })
    }
  },
  methods: {
    // 切换按钮组
    switchRadio () {
      if (this.form.type.select == 'image') {
        this.$refs.preview.setImage(this.form.type.image)
        this.$refs.preview.setLink()
        this.$refs.preview.setApplets()
      } else if (this.form.type.select == 'link') {
        this.$refs.preview.setImage()
        const linkOverData = this.form.type.link.input
        this.$refs.preview.setLink(linkOverData.title, linkOverData.desc, linkOverData.image)
        this.$refs.preview.setApplets()
      } else if (this.form.type.select == 'miniprogram') {
        this.$refs.preview.setImage()
        this.$refs.preview.setLink()
        const appletsOverData = this.form.type.applets
        this.$refs.preview.setApplets(appletsOverData.title, appletsOverData.image)
      }
    },
    /**
     * 添加按钮
     */
    addClick () {
      const form = this.form
      const msg = this.$message.loading(this.update.flag ? '修改中' : '添加中', 1.5)
      const params = {
        msg_text: form.text,
        msg_complex: {
          type: form.type.select,
          image: {
            pic: form.type.image
          },
          link: {
            title: form.type.link.input.title,
            pic: form.type.link.input.image,
            desc: form.type.link.input.desc,
            url: form.type.link.input.url
          },
          miniprogram: {
            title: form.type.applets.title,
            pic: form.type.applets.image,
            appid: form.type.applets.appid,
            page: form.type.applets.path
          }
        },
        notice: this.form.type.link.customizeFlag
      }
      if (this.update.flag) {
        params.id = this.update.id
        update(params).then(res => {
          if (res.code === 200) {
            msg.then(() => {
              this.$message.success('修改成功', 2.5)
            }).then(() => {
              this.$router.push('/roomWelcome/index')
            })
          }
        })
        return false
      }
      add(params).then(res => {
        if (res.code === 200) {
          msg.then(() => {
            this.$message.success('添加成功', 2.5)
          }).then(() => {
            this.$router.push('/roomWelcome/index')
          })
        }
      })
    },
    /**
     * [欢迎语2]添加小程序
     */
    addAppletsChange (e) {
      this.form.type.applets = e
      this.$refs.preview.setApplets(e.title, e.image)
    },
    /**
     * [欢迎语2]重新添加小程序
     */
    resetApplets () {
      this.form.type.applets.title = ''
      this.form.type.applets.appid = ''
      this.form.type.applets.path = ''
      this.form.type.applets.image = ''
    },
    /**
     * [欢迎语2]链接高级设置开关回调
     */
    linkCustomizeSwitchChange (e) {
      this.form.type.link.customizeFlag = e
    },
    /**
     * [欢迎语2]链接上传图片回调
     * @param e
     */
    uploadLinkImageChange (e) {
      this.form.type.link.input.image = e
      const data = this.form.type.link.input
      this.$refs.preview.setLink(data.title, data.desc, data.image)
    },
    /**
     * [欢迎语2]上传图片回调
     * @param e
     */
    uploadImageChange (e) {
      this.form.type.image = e
      this.$refs.preview.setImage(e)
    }
  },
  components: { AddApplets }
}
</script>

<style lang="less" scoped>
.ant-alert {
  margin-bottom: 16px;
}

.content-box {
  max-width: 1230px;
  display: flex;
}

.content-wrap {
  /*height: 100vh;*/
}

.form {
  max-width: 900px;
  min-width: 500px;
  flex: 1;
  margin-right: 40px;
  height: 600px;

  .input-msg {
    width: 100%;
    display: flex;
    -webkit-box-pack: justify;
    justify-content: space-between;
    margin-bottom: 24px;

    .title {
      min-width: 70px;
    }

    .content {
      width: 100%;
      border: 1px solid #eee;
      background: #fbfbfb;
      border-radius: 2px;

      .insert-btn-group {
        width: 100%;
        flex: 1;
        border-bottom: 1px dashed #e9e9e9;
        padding: 6px 15px;
        color: #e8971d;
        cursor: pointer;
      }

      .textarea {
        overflow-y: auto;
        overflow-x: hidden;
        white-space: pre-wrap;
        word-break: break-all;

        textarea {
          width: 100%;
          height: 110px;
          padding: 6px 13px;
          border: none;
          background: #fbfbfb;
          outline: none;
          resize: none;
        }

        .word-count {
          font-size: 13px;
          color: rgba(0, 0, 0, .25);
          margin-left: 10px;
        }
      }
    }
  }

  .tips-content {
    flex: 1;
    font-size: 13px;

    .ant-switch {
      margin-right: 7px;
    }

    .example {
      color: #1890ff;
      cursor: pointer;
      margin-left: 5px;
    }
  }

  .input-type {
    .content {
      padding: 16px;
    }
  }

  .select-type {
    margin-bottom: 16px;
  }

  .link {
    .url {
      margin-bottom: 13px;

      input {
        max-width: 418px;
      }
    }

    .customize {
      span {
        font-weight: bold;
      }

      .switch {
        display: flex;
        align-items: center;

        .tips {
          margin-left: 8px;
        }
      }
    }

    .link-form {
      margin-top: 16px;

      .item {
        display: flex;
        align-items: center;
        margin-bottom: 14px;

        .ant-input {
          width: 348px;
        }
      }
    }
  }
}
</style>
