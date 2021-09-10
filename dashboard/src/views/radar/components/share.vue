<template>
  <div class="share">
    <a-modal v-model="modalShow" :width="850" :footer="false" centered :maskClosable="false">
      <template slot="title">互动雷达</template>
      <div class="block">
        <div class="title">渠道链接管理</div>
        <div class="content">
          <div class="flex mb16">
            <span>添加渠道：</span>
            <div class="input flex">
              <a-input placeholder="请输入渠道名" :allowClear="true" v-model="channelName"/>
              <a-button class="ml16" @click="addChannelName">添加</a-button>
            </div>
          </div>
          <div class="flex mb16">
            <span>选择渠道：</span>
            <div class="input flex">
              <a-select
                style="width: 192px;"
                placeholder="请选择渠道"
                v-model="setlinkChannel.channel_id">
                <a-select-option
                  v-for="(item,index) in listChannel"
                  :key="index"
                  :value="item.id"
                >{{ item.name }}</a-select-option>
              </a-select>
            </div>
          </div>
          <div class="flex">
            <span>选择员工：</span>
            <div class="input">
              <a-select
                placeholder="请选择员工"
                :showSearch="true"
                @change="selectStaff"
                style="width: 200px;"
                v-model="showStaffData.name"
              >
                <a-select-option
                  v-for="(item,index) in staffArray"
                  :key="index"
                  :value="item.name"
                >{{ item.name }}
                </a-select-option>
              </a-select>
            </div>
            <a-button class="ml16" @click="establishlink">创建</a-button>
          </div>
        </div>
      </div>
      <div class="block">
        <div class="title">
          渠道列表
        </div>
        <div class="content">
          <div class="table">
            <a-table
              :columns="table.col"
              :data-source="table.data">
              <div slot="create_user" slot-scope="text">
                <a-tag><a-icon type="user" />{{ text }}</a-tag>
              </div>
              <div slot="operation" slot-scope="text,record">
                <a @click="shareBtn(record)">查看详情</a>
              </div>
            </a-table>
          </div>
          <!--   分享弹出框   -->
          <a-modal v-model="sharePupop" title="查看详情" :maskClosable="false" :footer="null">
            <div class="modeRow">
              <div class="modeOne">
                <div class="modeTitle">方式一：</div>
                <div class="modeTips">渠道二维码</div>
                <div class="qr-code" ref="qrCode"></div>
              </div>
              <div class="separate"></div>
              <div class="modeTwo">
                <div class="modeTitle">方式二：</div>
                <div class="modeTips">渠道链接</div>
                <div class="linkBox">{{ clockInLink }}</div>
              </div>
            </div>
            <div class="footRow">
              <a-button class="footBtn" @click="downQrcode">下载二维码</a-button>
              <a-button class="footBtn" type="primary" @click="copyLink">复制链接</a-button>
            </div>
          </a-modal>
        </div>
      </div>
    </a-modal>
    <input type="text" class="copy-input" ref="copyInput">
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import {
  storeChannelApi,
  indexChannelApi,
  department,
  storeChannelLinkApi,
  indexChannelLinkApi
} from '@/api/radar'
import QRCode from 'qrcodejs2'
export default {
  data () {
    return {
      // 二维码弹窗
      sharePupop: false,
      // 链接
      clockInLink: '',
      modalShow: false,
      // 添加渠道名称
      channelName: '',
      // 渠道列表
      listChannel: [],
      // 员工列表
      staffArray: [],
      // 展示员工信息
      showStaffData: {},
      // 生成渠道链接请求数据
      setlinkChannel: {},
      table: {
        col: [
          {
            dataIndex: 'createdAt',
            title: '时间'
          },
          {
            dataIndex: 'name',
            title: '渠道'
          },
          {
            dataIndex: 'create_user',
            title: '创建人',
            scopedSlots: { customRender: 'create_user' }
          },
          {
            dataIndex: 'clickPersonNum',
            title: '点击人数'
          },
          {
            dataIndex: 'clickNum',
            title: '点击次数'
          },
          {
            dataIndex: 'operation',
            title: '操作',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        data: []
      },
      id: ''
    }
  },
  methods: {
    // 下载二维码
    downQrcode () {
      const img = this.$refs.qrCode.childNodes[1]
      const a = document.createElement('a')
      const event = new MouseEvent('click')
      a.download = 'qrcode'
      a.href = img.src
      a.dispatchEvent(event)
    },
    // 赋值群打卡链接
    copyLink () {
      const inputElement = this.$refs.copyInput
      inputElement.value = this.clockInLink
      inputElement.select()
      document.execCommand('Copy')
      this.$message.success('复制成功')
    },
    // 分享事件
    shareBtn (record) {
      this.sharePupop = true
      this.clockInLink = record.link
      setTimeout(() => {
        this.initQrcode(record.link)
      }, 200)
    },
    // 加载二维码
    initQrcode (link) {
      this.$refs.qrCode.innerHTML = ''
      // eslint-disable-next-line no-new
      new QRCode(this.$refs.qrCode, {
        text: link,
        width: 122,
        height: 122
      })
    },
    // 获取员工数据
    getNumberData () {
      department().then((res) => {
        this.staffArray = res.data.employee
      })
    },
    // 选择员工
    selectStaff (e) {
      this.staffArray.forEach((item) => {
        if (item.name == e) {
          this.setlinkChannel.employeeId = item.id
        }
      })
    },
    // 创建链接
    establishlink () {
      if (this.setlinkChannel.channel_id == undefined) {
        this.$message.error('请选择渠道')
        return false
      }
      this.setlinkChannel.radar_id = this.id
      this.setlinkChannel.type = this.type
      storeChannelLinkApi(this.setlinkChannel).then((res) => {
        this.$message.success('生成链接成功')
        this.getChannelListlink()
        this.setlinkChannel = {}
        this.showStaffData = {}
      })
    },
    // 添加渠道
    addChannelName () {
      if (this.channelName == '') {
        this.$message.error('渠道名称不能为空')
        return false
      }
      storeChannelApi({ name: this.channelName }).then((res) => {
        this.$message.success('创建成功')
        this.channelName = ''
        this.getChannelNamelist({ radar_id: this.id })
      })
    },
    // 获取渠道列表
    getChannelNamelist (params) {
      indexChannelApi(params).then((res) => {
        this.listChannel = res.data.list
      })
    },
    // 获取渠道链接列表
    getChannelListlink () {
      indexChannelLinkApi({ radar_id: this.id }).then((res) => {
        this.table.data = res.data.list
        // console.log(this.table.data)
      })
    },
    show (type, data) {
      this.modalShow = true
      this.getNumberData()
      this.type = type + 1
      this.id = data.id
      this.getChannelNamelist({ radar_id: this.id })
      this.getChannelListlink()
    },
    hide () {
      this.modalShow = false
    }
  }
}
</script>

<style lang="less" scoped>
.block {
  margin-bottom: 23px;

  .title {
    font-size: 14px;
    font-weight: 600;
    color: rgba(0, 0, 0, .85);
    position: relative;
    margin-bottom: 10px;

    &:before {
      content: "";
      width: 3px;
      height: 12px;
      background: #1890ff;
      display: inline-block;
      margin-right: 6px;
      vertical-align: baseline;
    }
  }
}

.table {
  max-height: 550px;
  overflow: auto;
}

/deep/ .ant-modal-title {
  text-align: center;
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 1px;
}
.modeRow{
  display: flex;
}
.modeTitle{
  font-size: 14px;
  font-weight: 600;
  color: rgba(0,0,0,.85);
  line-height: 20px;
  border-left: 2px solid #1890ff;
  padding-left: 7px;
}
.modeTips{
  font-size: 14px;
  margin-top: 8px;
  color: rgba(0,0,0,.45);
}
.qr-code {
  text-align: center;
  margin-top: 10px;

  img {
    width: 114px;
    height: 114px;
    display: inline-block;
  }
}
.separate{
  width: 1px;
  height: 180px;
  background: #E8E8E8;
  margin-left: 40px;
  margin-right: 40px;
}
.linkBox{
  width: 253px;
  height: 111px;
  overflow-x: hidden;
  padding: 8px 14px 8px 16px;
  border-radius: 2px;
  border: 1px solid #dcdfe6;
  margin-top: 8px;
}
.footRow{
  margin-top: 10px;
  display: flex;
  justify-content:flex-end;
}
.footBtn{
  margin-left: 15px;
  margin-right: 16px;
}
.footBtn:first-child{
  margin-left: 0;
}
.copy-input {
  position: absolute;
  top: 0;
  left: 0;
  opacity: 0;
  z-index: -10;
}
</style>
