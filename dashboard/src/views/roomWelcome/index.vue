<template>
  <div class="room-welcome">
    <a-alert message="因企业微信接口限制，在【企业微信后台】群欢迎语素材库进行编辑或删除操作，MoChat不会同步更新，建议在MoChat入群欢迎语处进行管理" type="info"/>
    <div class="add-row">
      <div class="btn">
        <router-link :to="{path: '/roomWelcome/create'}">
          <a-button type="primary">新增入群欢迎语</a-button>
        </router-link>
        <span @click="goHelp">什么是入群欢迎语？</span>
      </div>
      <div class="search">
        <a-input-search placeholder="请输入要搜索的欢迎语" v-model="search"/>
      </div>
    </div>
    <a-card :title="`共${count}条素材`" :bordered="false">
      <a-table :columns="table.columns" :data-source="table.data">
        <div class="btn-group" slot="operating" slot-scope="row">
          <a @click="detailsShow(row)">详情</a>
          <a-divider type="vertical"/>
          <a @click="$router.push({ path: '/roomWelcome/create?update=true&id='+row.id })">修改</a>
          <a-divider type="vertical"/>
          <a @click="del(row)">删除</a>
        </div>
        <div slot="create_user" slot-scope="row">
          <a-tag>
            <a-icon type="user" two-tone-color="#7da3d1" :style="{ color: '#7da3d1' }"/>
            {{ row.create_user }}
          </a-tag>
        </div>
        <div slot="msg" slot-scope="row">
          <div v-if="row.complex_type === 'link'">
            <div v-if="row.msg_text">
              消息1：{{ row.msg_text }}
            </div>
            <div>
              <span class="type-text">[链接]</span>
              消息2：{{ row.msg_complex.title }}
            </div>
          </div>
          <div v-if="row.complex_type === 'image'">
            <div v-if="row.msg_text">
              消息1：{{ row.msg_text }}
            </div>
            <div>
              消息2：
              <img class="table-pic" :src="row.msg_complex.pic">
            </div>
          </div>
          <div v-if="row.complex_type === 'miniprogram'">
            <div v-if="row.msg_text">
              消息1：{{ row.msg_text }}
            </div>
            <div class="flex">
              消息2：
              <div class="applets">
                <div class="title">
                  {{ row.msg_complex.title }}
                </div>
                <div class="image">
                  <img :src="row.msg_complex.pic">
                </div>
                <div class="applets-logo">
                  <img src="../../assets/link.jpg">
                  小程序
                </div>
              </div>
            </div>
          </div>
          <div v-if="!row.complex_type">
            消息1：{{ row.msg_text }}
          </div>
        </div>
      </a-table>
    </a-card>

    <Details ref="details"/>
  </div>
</template>

<script>
import Details from './components/Details'
import { getList, del } from '@/api/roomWelcome'

export default {
  data () {
    return {
      count: '',
      table: {
        columns: [
          {
            title: '入群欢迎语',
            scopedSlots: { customRender: 'msg' }
          },
          {
            title: '类型',
            dataIndex: 'type'
          },
          {
            title: '创建人',
            scopedSlots: { customRender: 'create_user' }
          },
          {
            title: '创建时间',
            dataIndex: 'create_time'
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'operating' }
          }
        ],
        data: []
      },
      search: ''
    }
  },
  watch: {
    /**
     * 搜索
     */
    search () {
      if (this.timer) {
        clearTimeout(this.timer)
      }
      this.timer = setTimeout(() => {
        console.log(this.search)
      }, 500)
    }
  },
  mounted () {
    this.getData()
  },
  methods: {
    getData () {
      getList().then(res => {
        const typeMap = {
          image: '图片',
          link: '链接',
          miniprogram: '小程序'
        }

        for (const v of res.data.list) {
          if (v.msg_complex) v.msg_complex = JSON.parse(v.msg_complex)

          if (v.msg_text) {
            if (v.complex_type) v.type = `文字+${typeMap[v.complex_type]}`
            if (!v.complex_type) v.type = '文字'
          } else {
            v.type = typeMap[v.complex_type]
          }
        }

        this.count = res.data.page.total
        this.table.data = res.data.list
      })
    },

    /**
     * 打开详情
     */
    detailsShow (data) {
      this.$refs.details.show(data)
    },
    /**
     * 删除
     */
    del (data) {
      const _this = this
      this.$confirm({
        title: '提示?',
        content: '删除后不会影响已使用该入群欢迎语的群聊，确认删除该入群欢迎语吗？',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          del({ id: data.id }).then(res => {
            if (res.code === 200) {
              _this.$message.success('删除成功')
              _this.getData()
            }
          })
        }
      })
    },

    /**
     * 跳转"什么是入群欢迎语"
     */
    goHelp () {
      window.open('/')
    }
  },
  components: { Details }
}
</script>

<style lang="less" scoped>
.ant-alert {
  margin-bottom: 16px;
}

.add-row {
  display: flex;
  margin-bottom: 16px;

  .btn {
    flex: 1;

    .ant-btn {
      margin-right: 16px;
    }
  }

  span {
    cursor: pointer;
  }
}

.btn-group {
  font-size: 13px;
}

.type-text {
  color: #139a32d9;
}

.table-pic {
  max-width: 130px;
}

.applets {
  max-width: 183px;
  background: #fff;
  border-radius: 2px;
  padding: 7px 11px;
  font-size: 12px;
  border: 1px solid #f2f2f2;

  .title {
    font-size: 12px !important;
    font-weight: 500;
    margin-bottom: 5px !important;
  }

  .image img {
    max-width: 128px;
    max-height: 128px;
    border-radius: 2px;
  }

  .applets-logo {
    width: 100%;
    border-top: 1px solid #E7E7E7;
    margin-top: 9px;
    padding-top: 2px;
    font-size: 11px;
    display: flex;
    align-items: center;

    img {
      width: 17px;
    }
  }
}

/deep/ .ant-card-body {
  padding: 0 !important;
}

/deep/ .ant-table-pagination.ant-pagination {
  margin-right: 20px;
}
</style>
