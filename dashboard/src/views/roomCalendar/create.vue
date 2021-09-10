<template>
  <div class="big-box">
    <a-card>
      <div>
        <div class="name">
          <span>
            日历名称：
          </span>
          <a-input v-model="name" placeholder="请填写日历名称" style="width: 200px"/>
        </div>
        <div class="title">
          选择日期添加推送文案
        </div>
        <div class="switch">
          <a-tabs default-active-key="1">
            <a-tab-pane key="1" tab="日历">
              <div>
                <Calendar mode="month" @change="selectChange">
                  <div slot="dateCellRender" slot-scope="value">
                    <div
                      v-for="(v,i) in getListData(value)"
                      :key="i"
                      class="calendar-item"
                      @click="edit(v)"
                    >
                      <div class="text">
                        【{{ v.form.time }}】
                        {{ v.form.name }}
                      </div>
                      <a-icon type="close" @click.stop="del(v)"/>
                    </div>
                  </div>
                </Calendar>
              </div>
            </a-tab-pane>
            <a-tab-pane key="2" tab="时间轴" force-render>
              <div class="timeline-box">
                <div class="add-time">
                  <span class="today-text">
                    今天
                  </span>
                  <div class="dot"></div>
                  <a>
                    <div class="add-btn" @click="$refs.setCalnder.show('',false)">
                      <a-icon type="plus"/>
                      添加
                    </div>
                  </a>
                </div>
                <div class="time-list" v-for="(v,i) in list" :key="i">
                  <p class="date">
                    {{ v.date }}
                  </p>
                  <div v-for="(time,timeIndex) in v.data" :key="timeIndex">
                    <div class="date-time">
                      <span></span>
                      <a-icon
                        type="bell"
                        theme="filled"
                        style="color: #8d8d8d;
                      margin-left: 4px"/>
                      <span class="date-title">
                        【{{ time.form.time }}】{{ time.form.name }}
                      </span>
                    </div>
                    <div class="content flex">
                      <div class="warp">
                        <div
                          class="text-content"
                          v-for="(content,contentIndex) in time.list"
                          :key="contentIndex"
                        >
                          【{{ getContent(content).type }}】
                          {{ getContent(content).content }}
                        </div>
                        <div class="count">
                          共{{ time.list.length }}条
                        </div>
                      </div>
                      <div class="btn-box mr10 ml16" @click="del(time)">
                        <a-icon type="delete"/>
                      </div>
                      <div class="btn-box" @click="edit(time)">
                        <a-icon type="edit"/>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </a-tab-pane>
          </a-tabs>
        </div>
        <div class="create">
          <a-button @click="addClick" type="primary" style="width: 110px;height: 38px">
            创建
          </a-button>
        </div>
      </div>
    </a-card>

    <setCalnder ref="setCalnder" @change="calnderChange"/>
  </div>
</template>

<script>
import { Calendar } from 'ant-design-vue'
import setCalnder from '@/views/roomCalendar/components/setCalnder'
import { add } from '@/api/roomCalendar'
import moment from 'moment'

export default {
  data () {
    return {
      list: [],
      name: ''
    }
  },
  methods: {
    addClick () {
      if (!this.name) {
        this.$message.error('日历名称未填写')
        return false
      }

      for (const v of this.list) {
        for (const time of v.data) {
          time.day = `${time.form.date} ${time.form.time}`
          time.name = time.form.name
          time.push_content = time.list
        }
      }

      const push = this.list.map(v => {
        return v.data
      })
      const pushData = []
      push.forEach(v => {
        v.forEach(v2 => pushData.push(v2))
      })

      add({
        name: this.name,
        push: pushData
      }).then(res => {
        this.$message.success('添加成功')

        this.$router.push('/roomCalendar/index')
      })
    },

    getContent (data) {
      const map = {
        'text': {
          type: '文本',
          content: data.content
        },
        'image': {
          type: '图片',
          content: ''
        },
        'link': {
          type: '链接',
          content: data.link_title
        }
      }

      return map[data.type]
    },

    edit (e) {
      this.$refs.setCalnder.editShow(e)
    },

    del (e) {
      const dateFind = this.list.find((v, i) => {
        return v.date === e.form.date
      })

      dateFind.data.forEach((v, i) => {
        if (v.form.time === e.form.time) {
          dateFind.data.splice(i, 1)
        }
      })
    },

    calnderChange (e) {
      if (e.type === 'edit') {
        let findIndex = -1
        const find = this.list.find((v, i) => {
          if (v.date === e.form.date) {
            findIndex = i
            return true
          }
        })
        find.data.forEach((v, i) => {
          if (v.form.time === e.form.time) {
            this.list[findIndex].data[i] = e
          }
        })
        return false
      }
      this.list.forEach(v => {
        if (v.date === e.form.date) {
          v.data.push(e)

          return false
        }
      })
      this.list.push({
        date: e.form.date,
        data: [{ ...e }]
      })
    },
    getListData (date) {
      const find = this.list.find(v => {
        return v.date === date.format('YYYY-MM-DD')
      })
      if (find) return find.data
    },

    selectChange (e) {
      if (e.valueOf() < moment().valueOf()) {
        this.$message.error('不可选择今天之前的日期哦')

        return false
      }

      this.$refs.setCalnder.show(e)
    }
  },
  components: {
    Calendar, setCalnder
  }
}
</script>

<style lang="less" scoped>
.name {
  display: flex;
  align-items: center;
}

.title {
  font-size: 15px;
  width: 200px;
  height: 21px;
  line-height: 21px;
  color: rgba(0, 0, 0, .85);
  border-left: 3px solid #868686;
  margin-top: 26px;
  padding-left: 6px;
  margin-bottom: 12px;
}

.add-time {
  display: flex;
  position: relative;
  padding-top: 20px;
  padding-bottom: 20px;
}

.timeline-box {
  padding-left: 60px;
  min-height: 600px;
}

.add {
  background-color: #ffffff;
  color: #007aff;
}

.add-time:before {
  position: absolute;
  content: "";
  display: block;
  top: 0;
  width: 1px;
  height: 32px;
  border-left: 1px dashed #d8dfe4;
}

.add-time:after {
  position: absolute;
  content: "";
  display: block;
  left: 0;
  top: 50%;
  width: 1px;
  height: 32px;
  border-left: 1px dashed #d8dfe4;
}

.today-text {
  position: absolute;
  font-size: 16px;
  font-weight: 500;
  color: rgba(0, 0, 0, .85);
  line-height: 22px;
  left: -40px;
  top: 50%;
  -webkit-transform: translateY(-50%);
  transform: translateY(-50%);
}

.dot {
  position: absolute;
  z-index: 10;
  top: 50%;
  -webkit-transform: translateY(-50%);
  transform: translateY(-50%);
  width: 8px;
  height: 8px;
  content: "";
  display: block;
  background: #b4b9c3;
  border-radius: 50%;
  left: -3px;
}

.add-btn {
  margin-left: 21px;
  display: -webkit-box;
  display: flex;
  -webkit-box-pack: center;
  justify-content: center;
  -webkit-box-align: center;
  align-items: center;
  width: 94px;
  height: 32px;
  background: #fff;
  border-radius: 2px;
  border: 1px solid #1890ff;
  font-size: 14px;
  font-weight: 400;
  color: #1890ff;
  line-height: 20px;
}

.date {
  font-weight: bold;
  font-size: 16px;
  position: relative;
  left: -39px;
  top: 7px;
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

.reminder-content {
  width: 500px;
  padding: 14px 16px;
  background-color: #fafafa;
  border: 1px solid #dedede;
  position: relative;
}

.content-text {
  display: block;
  background-color: #ffffff;
  padding: 5px 8px;
  border-radius: 50px;
  box-shadow: 0 0 2px #d7d7d7;
}

.census {
  padding-top: 8px;
  font-size: 8px;
}

.operate {
  position: absolute;
  right: -60px;
  bottom: -2px;
}

.icon {
  width: 18px;
  height: 18px;
  margin-right: 10px;
  -webkit-transform: translateX(-40%);
  transform: translateX(-40%);
  color: #b4b9c3;
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
  }
}

.calendar-item {
  padding: 3px 7px;
  background: #ddedff;
  margin-top: 4px;
  font-size: 12px;
  font-weight: 400;
  color: rgba(0, 0, 0, .8);
  line-height: 17px;
  display: flex;
  align-items: center;

  .text {
    flex: 1;
  }
}
</style>
