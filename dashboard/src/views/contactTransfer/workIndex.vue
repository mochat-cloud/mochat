<template>
  <div class="work-index">
    <a-card class="mb16">
      <div class="filter-form">
        <!--        客户昵称-->
        <div class="item">
          <label>客户昵称：</label>
          <div class="input">
            <a-input-search
              placeholder="请输入要搜索的客户"
              v-model="searchData.contactName"
              @search="retrievalTable"
              :allowClear="true"
              @change="emptyNickIpt"
            ></a-input-search>
          </div>
        </div>
        <!--        所属客服-->
        <div class="item">
          <label>所属客服：</label>
          <div class="input belongTo" @click="$refs.selectMember.show()">
            <span class="tips" v-if="showEmployee.length==0">请选择客服</span>
            <a-tag v-for="(item,index) in showEmployee" :key="index">{{ item.name }}</a-tag>
          </div>
        </div>
        <selectMember ref="selectMember" @change="effectStaff"/>
        <!--        添加时间-->
        <div class="item">
          <label>添加时间：</label>
          <div class="input"><a-range-picker @change="retrievalDate" v-model="showTimeSearch"/></div>
        </div>
        <div class="item"><a-button @click="resetBtn">重置</a-button></div>
      </div>
    </a-card>
    <a-card>
      <div class="flex mb20">
        <div class="info-box">
          <span class="f-blod">共{{ table.data.length }}个待分配客户</span>
        </div>
        <div class="btn-box">
          <a-button type="primary" ghost @click="allocation">分配客户</a-button>
          <a-button type="primary" ghost @click="$router.push('/contactTransfer/workAllotRecord')">分配记录</a-button>
        </div>
        <selectStaff ref="choiceStaff" @change="acceptData" />
      </div>
      <div class="table">
        <a-table
          :columns="table.col"
          :data-source="table.data"
          :row-selection="{selectedRowKeys: table.rowSelection, onChange: onSelectChange}"
          :pagination="true"
        >
          <div slot="employeeName" slot-scope="text">
            <a-tag><a-icon type="user" />{{ text }}</a-tag>
          </div>
          <div slot="tags" slot-scope="text">
            <a-tag v-for="(item,index) in text" :key="index">{{ item }}</a-tag>
          </div>
          <div slot="operating" slot-scope="text,record">
            <a
              @click="$router.push({ path: '/workContact/contactFieldPivot?contactId='+record.contactId+'&employeeId='+record.employeeId+'&isContact=2'})"
            >客户详情</a>
          </div>
        </a-table>
      </div>
    </a-card>
  </div>
</template>

<script>
import { infoApi, indexApi } from '@/api/contactTransfer'
import selectMember from '@/components/Select/member'
import selectStaff from '@/components/addlabel/selectStaff'

export default {
  components: { selectMember, selectStaff },
  data () {
    return {
      // 展示客服数据
      showEmployee: [],
      // 展示时间
      showTimeSearch: [],
      searchData: {
        // 客户昵称
        contactName: '',
        // 起始时间
        addTimeStart: '',
        // 结束时间
        addTimeEnd: '',
        // 客服
        employeeId: ''
      },
      // 表格选中的客户
      tableSelectClient: [],
      table: {
        col: [
          {
            key: 'nickName',
            dataIndex: 'nickName',
            title: '客户昵称',
            width: 200
          },
          {
            key: 'employeeName',
            dataIndex: 'employeeName',
            title: '所属客服',
            scopedSlots: { customRender: 'employeeName' }
          },
          {
            key: 'tags',
            dataIndex: 'tags',
            title: '客户标签',
            scopedSlots: { customRender: 'tags' }
          },
          {
            key: 'transferState',
            dataIndex: 'transferState',
            title: '转接状态'
          },
          {
            key: 'addTime',
            dataIndex: 'addTime',
            title: '添加时间'
          },
          {
            key: 'lastMsgTime',
            dataIndex: 'lastMsgTime',
            title: '上次对话时间'
          },
          {
            key: 'addWay',
            dataIndex: 'addWay',
            title: '添加渠道'
          },
          {
            key: 'operating',
            dataIndex: 'operating',
            title: '操作',
            scopedSlots: { customRender: 'operating' }
          }
        ],
        data: [],
        rowSelection: []
      }
    }
  },
  created () {
    this.getTableData()
  },
  methods: {
    // 分配客户
    allocation () {
      if (this.table.rowSelection == '') {
        this.$message.warning('请选择客户')
        return false
      }
      this.$refs.choiceStaff.show(0)
    },
    // 接收子组件传值
    acceptData (e) {
      const params = {
        type: 2,
        list: JSON.stringify(this.tableSelectClient),
        takeoverUserId: e.wxUserId
      }
      indexApi(params).then((res) => {
        let successNum = 0
        let errNum = 0
        res.data.forEach((item, index) => {
          if (item.errcode == 0) {
            successNum++
          } else {
            errNum++
          }
        })
        this.$message.info('已成功分配' + successNum + '位客户，分配失败' + errNum + '位客户')
        this.table.rowSelection = []
      })
    },
    // 获取表格选中值
    onSelectChange (e) {
      this.tableSelectClient = []
      this.table.rowSelection = e
      e.forEach((item) => {
        const askRowData = {
          employeeWxId: '',
          contactWxId: ''
        }
        askRowData.employeeWxId = this.table.data[item].employeeWxId
        askRowData.contactWxId = this.table.data[item].contactWxId
        this.tableSelectClient.push(askRowData)
      })
    },
    // 重置
    resetBtn () {
      this.searchData = {}
      this.showEmployee = []
      this.showTimeSearch = []
      this.getTableData(this.searchData)
    },
    // 接收子组件数据
    effectStaff (e) {
      this.showEmployee = e
      const askServiceData = []
      e.forEach((item, index) => {
        askServiceData[index] = item.id
      })
      this.searchData.employeeId = JSON.stringify(askServiceData)
      this.getTableData(this.searchData)
    },
    // 检索日期
    retrievalDate (date, dateString) {
      this.searchData.addTimeStart = dateString[0]
      this.searchData.addTimeEnd = dateString[1]
      this.getTableData(this.searchData)
    },
    //  搜索客户名称
    retrievalTable () {
      this.getTableData(this.searchData)
    },
    // 清空搜索框
    emptyNickIpt () {
      if (this.searchData.contactName == '') {
        this.getTableData(this.searchData)
      }
    },
    //  获取在职转接表格数据
    getTableData (params) {
      infoApi(params).then((res) => {
        this.table.data = res.data
      })
    }
  }
}
</script>

<style lang="less">
.filter-form {
  display: flex;
  align-items: center;
  flex-wrap: wrap;

  .item {
    min-width: 266px;
    margin-bottom: 13px;
    padding-right: 33px;
    box-sizing: content-box;
    display: flex;
    justify-content: flex-start;
    align-items: center;

    .input {
      width: 208px;
      padding-left: 12px;
    }
    .belongTo{
      padding-left: 7px;
      border: 1px solid #D9D9D9;
      height: 32px;
      line-height: 32px;
      cursor: pointer;
      .tips{
        color: #BFBFBF;
      }
    }
    .ant-select {
      width: 100%;
    }
  }
}

.info-box {
  flex: 1;
}

.btn-box {
  .ant-btn {
    margin-right: 10px;
  }
}

.client-info {
  display: flex;
  align-items: center;

  .avatar img {
    width: 36px;
    height: 36px;
    border-radius: 2px;
    margin-right: 6px;
  }

  .info {
    .name {
      font-size: 13px;
    }

    .company {
      font-size: 12px;
      color: #5ec75d;
    }

    .nickname {
      font-size: 12px;
      color: rgba(0, 0, 0, .45);
    }
  }
}
.message-wrapper {
  .message-content {
    margin: 0;
    padding: 0;
    max-height: 540px;
    overflow-y: auto;
    .message-item {
      margin: 20px 0 20px 10px;
      .people-self {
        flex-direction: row-reverse;
        margin-right: 10px
      }
      .people-wrapper {
        display: flex;
        .people-avatar {
          flex: 0 0 50px;
          .img {
            width: 45px;
            height: 45px;
          }
        }
        .people-info {
          margin-left: 10px;
          // flex: 1;
          max-width: 50%;
          .name-wrapper {
            .name {
              margin-right: 20px;
            }
          }
          .self-name-wrapper {
            display: flex;
            flex-direction: row-reverse;
            .name {
              margin-left: 20px;
            }
          }
          .info {
            padding: 15px;
            word-break: break-word;
            background: rgba(0,0,0,.1);
            border-radius: 10px;
          }
          .self-info {
            margin-right: 10px;
            background: #1890ff;
            color: #fff;
            justify-content: flex-end;
          }
          .info-item {
            padding: 10px;
          }
          .white {
            background: none;
            display: flex;
            .img {
              width: 200px
            }
            .video {
              width: 50%
            }
            .little {
              width: 80%;
              border: 1px solid rgba(0,0,0,.3);
              border-radius: 10px;
              color: black;
              display: flex;
              .wrpper {
                width: 60px;
                height:60px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                margin: 5px;
                background: rgba(0,0,0,0.01);
                border: 1px solid rgba(0,0,0,.3);
              }
              .title-wrapper {
                flex: 1;
                .title,.name,.description {
                  word-break: break-word;
                  margin-bottom: 10px;
                  padding: 5px
                }
                .title {
                  text-align: center;
                }
              }
            }
            .other {
              display: flex;
              align-items: center;
              .file {
                font-size: 40px;
              }
              .file-name {
                color: black;
              }
            }
          }
        }
      }
    }
  }
}
</style>
