<template>
  <div class="wrapper-box">
    <div :split="false" class="lists">
      <span>1、欢迎语又称新好友自动回复，此处可添加文字、图片、图文链接及小程序，客户来了不用担心冷场！</span>
      <span>2、每个企业成员均可以拥有不同的欢迎语。当通用的欢迎语和个人专属的欢迎语并存的情况下，优先自动回复个人专属的欢迎语。</span>
      <span>3、如果企业在企业微信后台为相关成员配置了可用的欢迎语，使用第三方系统配置欢迎语，则均不起效，推送的还是企业微信官方的。</span>
    </div>
    <a-card>
      <div class="btn-box">
        <router-link :to="{path: `/greeting/store?hadGeneral=${hadGeneral}&hadEmployees=${hadEmployees}`}">
          <a-button v-permission="'/greeting/store'" type="primary">+新建欢迎语</a-button>
        </router-link>
      </div>
      <a-table
        :columns="columns"
        :data-source="tableData"
        :rowKey="record => record.greetingId"
        @change="handleTableChange"
        :pagination="pagination">
        <div slot="words" slot-scope="text, record">
          <template>
            <div v-if="record.words !== ''">
              文本：{{ record.words }}
            </div>
            <div v-if="record.mediumContent.appid != undefined">
              小程序：<a-icon type="link" /><span>{{ record.mediumContent.title }}</span>
            </div>
            <div class="img-box" v-if="record.mediumContent.appid == undefined && record.mediumContent.title != undefined">
              <span>链接：</span>
              <div class="text-box">
                <h4>{{ record.mediumContent.title }}</h4>
                <div>
                  <span v-if="record.mediumContent.description">{{ record.mediumContent.description }}</span>
                  <img :src="record.mediumContent.imagePath" alt="">
                </div>
              </div>
            </div>
            <div class="imgs" v-if="Object.keys(record.mediumContent).length == 3">
              <span>图片：</span>
              <img :src="record.mediumContent.imageFullPath" alt="">
            </div>
          </template>
        </div>
        <div slot="employees" slot-scope="text, record">
          <div>{{ record.employees.join(' ') }}</div>
        </div>
        <div slot="action" slot-scope="text, record">
          <template>
            <router-link :to="{path:`/greeting/store?greetingId=${record.greetingId}&hadGeneral=${hadGeneral}&hadEmployees=${hadEmployees}`}">
              <a-button v-permission="'/greeting/index@edit'" type="link">编辑</a-button>
            </router-link>
            <a-button v-permission="'/greeting/index@delete'" type="link" @click="showDeleteConfirm(record.greetingId)">删除</a-button>
          </template>
        </div>
      </a-table>
    </a-card>
  </div>
</template>

<script>
import { greetingList, delGreeting } from '@/api/greeting'
export default {
  data () {
    return {
      columns: [
        {
          align: 'center',
          title: '欢迎语类型',
          dataIndex: 'typeText'
        },
        {
          title: '欢迎语内容',
          dataIndex: 'words',
          scopedSlots: { customRender: 'words' }
        },
        {
          align: 'center',
          title: '适用成员',
          dataIndex: 'employees',
          scopedSlots: { customRender: 'employees' }
        },
        {
          align: 'center',
          title: '创建时间',
          dataIndex: 'createdAt'
        },
        {
          align: 'center',
          title: '操作',
          dataIndex: 'action',
          scopedSlots: { customRender: 'action' }
        }
      ],
      tableData: [],
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      // 是否有一个通用
      hadGeneral: '',
      // 已选择过的成员
      hadEmployees: []
    }
  },
  created () {
    this.getTableData()
  },
  methods: {
    getTableData () {
      const params = {
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      greetingList(params).then(res => {
        this.hadGeneral = res.data.hadGeneral
        this.tableData = res.data.list
        this.pagination.total = res.data.page.total
        this.hadEmployees = res.data.hadEmployees
      })
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    },
    // 删除欢迎语
    showDeleteConfirm (id) {
      const _this = this
      this.$confirm({
        title: '确定删除该欢迎语',
        okText: '确定',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          delGreeting({
            greetingId: id
          }).then(res => {
            _this.getTableData()
          })
        },
        onCancel () {
          console.log('Cancel')
        }
      })
    }
  }
}
</script>

<style lang="less" scoped>
.wrapper-box {
  .lists {
    width: 100%;
    display: flex;
    flex-direction: column;
    background: #FFFBE6;
    span {
      margin: 5px;
    }
    :nth-child(3) {
      color: red;
    }
  }
  .btn-box {
    margin-bottom: 20px;
    display: flex;
    justify-content: flex-end;
  }
  .img-box {
    display: flex;
    margin-top:10px;
    .text-box {
      width: 300px;
      height: 130px;
      display: flex;
      flex-direction: column;
      padding: 10px 15px;
      border:1px solid #ccc;
      border-radius: 4px;
      h4 {
        width: 100%;
        height: 20px;
        line-height: 20px;
        margin: 0;
      }
      div {
        margin-top:5px;
        width: 100%;
        height: 80px;
        display: flex;
        justify-content: space-between;
        img {
          width: 80px;
          height: 80px;
        }
      }
    }
  }
  .imgs {
    img {
      margin-top:10px;
      width: 200px;
      max-height: 100%;
    }
  }
}
</style>
