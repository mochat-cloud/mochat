<template>
  <div class="group-index" style="position: relative;">
    <a-button
      type="primary"
      class="mb14"
      @click="$router.push('/roomClockIn/create')">
      创建打卡活动
    </a-button>
    <div class="switch_account">
      公众号：<a-select style="width: 150px" v-model="officialAccount">
        <a-select-option :value="item.nickname" v-for="(item,index) in publiclist" :key="index">
          {{ item.nickname }}
        </a-select-option>
      </a-select>
    </div>
    <a-card>
      <div class="state">
        <a-button
          v-for="(v,index) in state.list"
          :key="index"
          @click="stateSwitchClick(v,index)"
          :type="state.current === v?'primary':'' "
        >
          {{ v }}
        </a-button>
      </div>
      <a-table :columns="table.col" :data-source="table.data">
        <div slot="contact_clock_tags" slot-scope="text" style="min-width: 200px">
          <div v-for="(item,index) in text" :key="index" class="clientTags">
            <a-tag class="label" v-for="(obj,idx) in item" :key="idx">{{ obj.tagname }}</a-tag>
          </div>
        </div>
        <div slot="nickname" slot-scope="text">
          <a-tag>
            <a-icon type="user"/>
            {{ text }}
          </a-tag>
        </div>
        <div slot="operate" slot-scope="text, record">
          <a @click="detailsTableRow(record)">详情</a>
          <a-divider type="vertical"/>
          <a href="#" @click="shareBtn(record)">分享</a>
          <a-divider type="vertical"/>
          <a-dropdown>
            <a class="ant-dropdown-link">
              编辑<a-icon type="down" />
            </a>
            <a-menu slot="overlay">
              <a-menu-item>
                <a @click="modifyBtn(record)">修改</a>
              </a-menu-item>
              <a-menu-item>
                <a @click="delTableRow(record)">删除</a>
              </a-menu-item>
            </a-menu>
          </a-dropdown>
        </div>
      </a-table>
      <!--   分享弹出框   -->
      <a-modal v-model="visible" title="分享" :maskClosable="false" :footer="null">
        <div class="modeRow">
          <div class="modeOne">
            <div class="modeTitle">方式一：</div>
            <div class="modeTips">群打卡二维码</div>
            <div class="qr-code" ref="qrCode"></div>
          </div>
          <div class="separate"></div>
          <div class="modeTwo">
            <div class="modeTitle">方式二：</div>
            <div class="modeTips">群打卡链接</div>
            <div class="linkBox">{{ clockInLink }}</div>
          </div>
        </div>
        <div class="footRow">
          <a-button class="footBtn" @click="downQrcode">下载二维码</a-button>
          <a-button class="footBtn" type="primary" @click="copyLink">复制打卡链接</a-button>
        </div>
      </a-modal>
    </a-card>
    <input type="text" class="copy-input" ref="copyInput">
    <!--    授权提示-->
    <warrantTip ref="warrantTip" />
  </div>
</template>

<script>
import { getList, delList, publicIndexApi } from '@/api/roomClockIn'
import QRCode from 'qrcodejs2'
import warrantTip from '@/components/warrantTip/warrantTip'
export default {
  components: { warrantTip },
  data () {
    return {
      // 公众号列表
      publiclist: [],
      // 公众号
      officialAccount: '',
      // 群打卡链接
      clockInLink: '',
      visible: false,
      table: {
        col: [
          {
            key: 'name',
            dataIndex: 'name',
            title: '打卡活动名称'
          },
          {
            key: 'contact_clock_tags',
            dataIndex: 'contact_clock_tags',
            title: '客户标签',
            scopedSlots: { customRender: 'contact_clock_tags' }
          },
          {
            key: 'nickname',
            dataIndex: 'nickname',
            title: '创建人',
            scopedSlots: { customRender: 'nickname' }
          },
          {
            key: 'type',
            dataIndex: 'type',
            title: '类型'
          },
          {
            key: 'average_day',
            dataIndex: 'average_day',
            title: '平均打卡天数'
          },
          {
            key: 'total_user',
            dataIndex: 'total_user',
            title: '总打卡人数'
          },
          {
            key: 'created_at',
            dataIndex: 'created_at',
            title: '创建时间'
          },
          {
            key: 'time',
            dataIndex: 'time',
            title: '活动时间'
          },
          {
            key: 'status',
            dataIndex: 'status',
            title: '状态'
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'operate' }
          }
        ],
        data: []
      },
      state: {
        list: ['全部', '进行中', '未开始', '已结束'],
        current: '全部'
      },
      params: {
        status: 0
      }
    }
  },
  created () {
    this.getListData()
    this.setUpPublicName()
    this.getPublicList()
  },
  methods: {
    // 设置公众号
    setUpPublicName () {
      publicIndexApi({ type: 1 }).then((res) => {
        this.officialAccount = res.data.nickname
      })
    },
    // 获取公众号列表
    getPublicList () {
      publicIndexApi().then((res) => {
        this.publiclist = res.data
        if (this.publiclist.length == 0) {
          this.$refs.warrantTip.show()
        }
      })
    },
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
      this.visible = true
      this.clockInLink = record.share_link
      setTimeout(() => {
        this.initQrcode(record.share_link)
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
    stateSwitchClick (state, index) {
      this.state.current = state
      this.params.status = index
      this.getListData()
    },
    // 表格行详情
    detailsTableRow (item) {
      this.$router.push('/roomClockIn/show?activityId=' + item.id)
    },
    // 修改
    modifyBtn (obj) {
      this.$router.push('/roomClockIn/edit?activityId=' + obj.id)
    },
    // 删除表格行
    delTableRow (item) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除？',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          const indexRow = that.table.data.indexOf(item)
          that.table.data.splice(indexRow, 1)
          delList({
            id: item.id
          }).then((res) => {
            that.$message.success('删除成功')
          })
        }

      })
    },
    //  获取列表数据
    getListData () {
      getList(this.params).then((data) => {
        this.table.data = data.data.list
      })
    }
  }
}
</script>
<style lang="less">
.switch_account{
  position: absolute;
  z-index: 10;
  right: 0;
  top: 0;
}
.copy-input {
  position: absolute;
  top: 0;
  left: 0;
  opacity: 0;
  z-index: -10;
}
/deep/ .ant-card-body {
  padding: 0;
}
.clientTags{
  margin-top: 10px;
}
.clientTags:first-child{
  margin-top: 0;
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
  margin-right: 30px;
}
.ant-modal-title{
  text-align: center;
  font-size: 17px;
  font-weight: 600;
}
.state {
  padding: 10px;

  button {
    margin-right: 8px;
  }
}
</style>
