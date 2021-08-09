<template>
  <div class="box">
    <div class="create">
      <div class="create-btn">
        <a-button type="primary" @click="$router.push('/roomInfinitePull/create')">
          新建拉群
        </a-button>
      </div>
    </div>
    <a-card class="mt18">
      <div class="table">
        <a-table
          :columns="table.col"
          :data-source="table.data">
          <div slot="group" slot-scope="row">
            <div class="chat-li" v-for="(v,i) in row.qwCode" :key="i">
              <div class="chat-info">
                <img :src="v.qrcode" height="32" width="32"/>
                <span class="chat-name ml8">群活码{{ i + 1 }}</span></div>
              <div class="ml8">
                <a-tag v-if="v.status === 0">未开始</a-tag>
                <a-tag color="green" v-if="v.status === 1">拉人中</a-tag>
                <a-tag color="red" v-if="v.status === 2">已停用</a-tag>
              </div>
            </div>
          </div>
          <div slot="operate" slot-scope="row">
            <a @click="$refs.details.show(row)">详情</a>
            <a-divider type="vertical"/>
            <a @click="createQrcode(row.link)">下载</a>
            <a-divider type="vertical"/>
            <a-dropdown>
              <a class="ant-dropdown-link" @click="e => e.preventDefault()">
                编辑
                <a-icon type="down"/>
              </a>
              <a-menu slot="overlay">
                <a-menu-item>
                  <a @click="$router.push({path:'/roomInfinitePull/show',query:{id:row.id}})">修改</a>
                </a-menu-item>
                <a-menu-item>
                  <a @click="delClick(row.id)">删除</a>
                </a-menu-item>
              </a-menu>
            </a-dropdown>
          </div>
        </a-table>
      </div>
    </a-card>

    <div class="qr-code" ref="qrCode"></div>
    <Details ref="details"/>
  </div>
</template>

<script>
import { getList, del } from '@/api/roomInfinitePull'
import Details from '@/views/roomInfinitePull/components/details'
import QRCode from 'qrcodejs2'

export default {
  data () {
    return {
      table: {
        col: [
          {
            title: '名称',
            dataIndex: 'name'
          },
          {
            title: '创建时间',
            dataIndex: 'created_at'
          },
          {
            title: '扫码人数',
            dataIndex: 'total_num'
          },
          {
            title: '群聊',
            scopedSlots: { customRender: 'group' }
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'operate' }
          }
        ],
        data: []
      }
    }
  },
  mounted () {
    this.getData()
  },
  methods: {
    delClick (id) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          del({ id }).then(res => {
            that.$message.success('删除成功')
            that.getData()
          })
        }
      })
    },
    createQrcode (link) {
      setTimeout(() => {
        this.$refs.qrCode.innerHTML = ''

        // eslint-disable-next-line no-new
        new QRCode(this.$refs.qrCode, {
          text: link,
          width: 122,
          height: 122
        })
      }, 100)

      setTimeout(() => {
        const img = this.$refs.qrCode.childNodes[1]

        const a = document.createElement('a')

        const event = new MouseEvent('click')

        a.download = 'qrcode'

        a.href = img.src
        a.dispatchEvent(event)
      }, 200)
    },

    getData () {
      getList().then(res => {
        this.table.data = res.data.list
      })
    }
  },
  components: { Details }
}
</script>

<style lang="less" scoped>
.create {
  display: flex;

  .create-btn {
    flex: 1;
  }
}

.chat-li {
  margin-bottom: 8px;
  display: -webkit-box;
  display: flex;
  -webkit-box-align: center;
  align-items: center;
  box-sizing: border-box;
}

.chat-li .chat-status.green {
  background: #e8fcdc;
  border: 1px solid #a1ec9f;
}

.chat-li .chat-status {
  width: 58px;
  height: 22px;
  font-size: 12px;
  line-height: 17px;
  color: rgba(0, 0, 0, .65);
  text-align: center;
  border-radius: 2px;
  margin-left: 16px;
  word-break: keep-all;
  white-space: nowrap;
}

.chat-li .chat-info {
  width: 160px;
  height: 38px;
  padding: 12px 13px;
  background: #fff;
  border: 1px solid #e6e6e6;
  box-sizing: border-box;
  border-radius: 2px;
  display: -webkit-box;
  display: flex;
  -webkit-box-align: center;
  align-items: center;
}

.qr-code {
  opacity: 0;
}
</style>
