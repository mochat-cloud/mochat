<template>
  <div class="big-box">
    <a-card>
      <div>
        <div class="name">
          <span>
            日历名称：
          </span>
          <a-input v-model="data.calendar.name" disabled style="width: 200px"/>
        </div>
        <div class="title">
          该日历下{{ rooms.length }}个群聊
          <a @click="$refs.group.show(rooms,data.calendar.id)">查看全部</a>
        </div>
        <div class="switch">
          <a-tabs v-model="switchTab">
            <a-tab-pane :key="1" tab="日历">
              <div>
                <Calendar mode="month" v-model="setUpDate">
                  <div slot="dateCellRender" slot-scope="value">
                    <div
                      v-for="(v,i) in getListData(value)"
                      :key="i"
                      class="calendar-item"
                    >
                      <div class="text">
                        <div class="text show_data" @click="contentCalendar">
                          【{{ v.time }}】
                          {{ v.name }}
                        </div>
                      </div>
                    </div>
                  </div>
                </Calendar>
              </div>
            </a-tab-pane>
            <a-tab-pane :key="2" tab="时间轴" force-render>
              <div class="timeline-box">
                <div class="time-list" v-for="(v,i) in data.push" :key="i">
                  <p class="date">
                    {{ v.date }}
                  </p>
                  <div class="date-time">
                    <span>
                      {{ v.time }}
                    </span>
                    <a-icon
                      type="bell"
                      theme="filled"
                      style="color: #8d8d8d;
                      margin-left: 4px"/>
                    <span class="date-title">
                      {{ v.name }}
                    </span>
                    <a-icon
                      type="edit"
                      style="font-size: 17px;margin-left: 15px;cursor: pointer;"
                      @click="$refs.setCalnder.modifyShow(v)"
                    />
                  </div>
                  <div class="content flex">
                    <div class="warp">
                      <div
                        class="text-content"
                        v-for="(content,contentIndex) in v.pushContent"
                        :key="contentIndex"
                      >
                        【{{ getContent(content).type }}】
                        {{ getContent(content).content }}
                      </div>
                      <div class="count">
                        共{{ v.pushContent.length }}条
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </a-tab-pane>
          </a-tabs>
        </div>
      </div>
    </a-card>

    <group ref="group" @change="getData"/>
    <!--    设置日历-->
    <setCalnder ref="setCalnder" @change="modifyData" />
  </div>
</template>

<script>
import { Calendar } from 'ant-design-vue'
// eslint-disable-next-line no-unused-vars
import { getInfo, updateApi } from '@/api/roomCalendar'
import group from './components/group'
import moment from 'moment'
import setCalnder from '@/views/roomCalendar/components/setCalnder'
export default {
  components: {
    Calendar, group, setCalnder
  },
  data () {
    return {
      // 切换面板
      switchTab: 1,
      data: {
        calendar: {
          name: ''
        },
        push: []
      },
      rooms: [],
      setUpDate: ''
    }
  },
  mounted () {
    this.getData()
  },
  methods: {
    // 修改日历
    modifyData (e) {
      const push = []
      const pushArr = {
        id: e.form.id,
        name: e.form.name,
        day: `${e.form.date} ${e.form.time}`,
        push_content: e.list
      }
      push[0] = pushArr
      console.log(push)
      updateApi({
        id: this.$route.query.id,
        push
      }).then((res) => {
        this.$message.success('修改成功')
        this.getData()
      })
    },
    // 查看日历内容
    contentCalendar () {
      this.switchTab = 2
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
    getListData (date) {
      const data = []
      this.data.push.forEach(v => {
        if (this.deDate(v.day).date === date.format('YYYY-MM-DD')) {
          data.push({
            ...v
          })
        }
      })
      if (data) {
        return data
      }
    },
    getData () {
      getInfo({
        id: this.$route.query.id
      }).then(res => {
        this.data = res.data
        this.rooms = this.data.calendar.rooms
        this.data.push.forEach(v => {
          v.time = this.deDate(v.day).time
          v.date = this.deDate(v.day).date
        })
        this.setUpDate = moment(this.data.push[0].date)
      })
    },
    deDate (date) {
      const match = date.match(/(.+) (.+)/i)
      return {
        date: match[1],
        time: match[2]
      }
    }
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
  left: 3px;
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
  .show_data{
    width: 100%;
    height: 50px;
  }
}
</style>
