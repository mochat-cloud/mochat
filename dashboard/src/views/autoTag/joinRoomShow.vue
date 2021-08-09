<template>
  <div class="rules-details">
    <a-card>
      <div class="title">基本信息</div>
      <div class="information">
        <div class="ifm-left">
          <div class="name">
            <span>规则名称：</span>
            <span>{{ auto_tag.name }}</span>
          </div>
          <div class="member mt16">
            <span>打标签方式：</span>
            <span>根据客户入群行为</span>
          </div>
          <div class="founder mt16">
            <span>创建者：</span>
            <a-tag><a-icon type="user" />
              {{ auto_tag.nickname }}</a-tag>
          </div>
          <div class="create-time mt16">
            <span>创建时间：</span>
            <span>{{ auto_tag.createdAt }}</span>
          </div>
          <div class="state mt16">
            <span>规则状态：</span>
            <a-switch size="small" :checked="auto_tag.onOff==1" class="mr4"/>
            <span v-if="auto_tag.onOff">已开启</span>
            <span v-else>已关闭</span>
          </div>
        </div>
        <div class="ifm-right">
          <div class="title-set">
            <span>规则设置：</span>
            <span class="black">共{{ tagRule.length }}条规则</span>
          </div>
          <div class="set-content mt18 ml84" v-for="(item,index) in tagRule" :key="index">
            <span class="title-small black">规则{{ index+1 }}</span>
            <p class="keys ml4">
              用户加入群聊
              <a-tag v-for="(obj,idx) in item.rooms" :key="idx">{{ obj.name }}</a-tag>
              时，打上标签
              <a-tag v-for="(tag,order) in item.tags" :key="order">{{ tag.tagname }}</a-tag>
            </p>
          </div>
        </div>
      </div>
    </a-card>
    <a-card class="mt16">
      <div class="title">数据统计</div>
      <div class="data-num">
        <div class="data-box mb20">
          <div class="data">
            <div class="item">
              <div class="count">{{ statistics.total_count }}</div>
              <div class="desc">打标签总数</div>
            </div>
            <div class="item">
              <div class="count">{{ statistics.today_count }}</div>
              <div class="desc">今日打标签数</div>
            </div>
          </div>
        </div>
      </div>
    </a-card>
    <a-card class="mt16">
      <div class="search">
        <div class="search-box">
          <!--          搜索用户-->
          <div class="customer">
            <span>搜索用户：</span>
            <a-input-search
              placeholder="请输入要搜索的客户"
              style="width: 200px"
              v-model="paramsTable.contact_name"
              @search="searchUser"
              allow-clear
              @change="emptyInput(0)"
            />
          </div>
          <!--          所属客服-->
          <div class="customer-service ml20">
            <span>所属客服：</span>
            <a-select style="width: 160px" default-value="请选择客服">
              <a-select-option value="Home">刘波</a-select-option>
              <a-select-option value="Company">小子</a-select-option>
            </a-select>
          </div>
          <!--          群聊名称-->
          <div class="room-name ml20">
            <span>群聊名称：</span>
            <a-input-search
              placeholder="请输入群聊名称"
              style="width: 200px"
              v-model="paramsTable.room_name"
              @search="searchGroup"
              allow-clear
              @change="emptyInput(1)"
            />
          </div>
          <!--          加入群聊时间-->
          <div class="add-time ml20">
            <span>加入群聊时间：</span>
            <a-range-picker
              style="width: 220px"
              @change="searchTime"
              :allowClear="true"
              v-model="selectDate"
            />
          </div>
          <!--          入群方式-->
          <div class="join-mode">
            <span class="ml20">入群方式：</span>
            <a-select style="width: 210px" placeholder="请选择入群方式" @change="joinGroupMode" v-model="paramsTable.join_scene">
              <a-select-option :value="0">全部方式</a-select-option>
              <a-select-option :value="1">群成员直接邀请入群</a-select-option>
              <a-select-option :value="2">群成员通过链接邀请入群</a-select-option>
              <a-select-option :value="3">通过扫描二维码入群</a-select-option>
            </a-select>

          </div>
        </div>
        <div class="reset"><a-button @click="resetTable">重置</a-button></div>
      </div>
      <div class="table-box mt36">
        <div class="store-box mb20">
          <span class="customers-title">共{{ table.data.length }}个用户</span>
          <a-divider type="vertical" />
          <span style="cursor: pointer;" @click="updateTable"><a-icon type="redo" />更新数据</span>
        </div>
        <div class="table">
          <a-table :columns="table.col" :data-source="table.data">
            <div slot="employeeName" slot-scope="text">
              <a-tag><a-icon type="user" />{{ text }}</a-tag>
            </div>
            <div slot="roomName" slot-scope="text">
              <a-tag>{{ text }}</a-tag>
            </div>
            <div slot="joinScene" slot-scope="text">
              <span v-if="text==1">成员邀请入群</span>
              <span v-if="text==2">邀请链接入群</span>
              <span v-if="text==3">扫描群二维码入群</span>
            </div>
            <div slot="operate">
              <div>
                <a>客户详情</a>
              </div>
            </div>
          </a-table>
        </div>
      </div>
    </a-card>
  </div>
</template>

<script>
import { showApi, showContactRoomApi } from '@/api/autoTag'
export default {
  data () {
    return {
      // 选择时间
      selectDate: [],
      // 表格请求数据
      paramsTable: {
        // 搜索客户名称
        contact_name: '',
        // 客服
        employee: '',
        // 群聊名称
        room_name: '',
        // 开始时间
        start_time: '',
        // 结束时间
        end_time: '',
        // 入群方式
        page: 1,
        perPage: 15
      },
      // 详情-基本信息数据
      auto_tag: {},
      // 规则
      tagRule: [],
      // 详情-数据统计
      statistics: {},
      table: {
        col: [
          {
            key: 'contactName',
            dataIndex: 'contactName',
            title: '客户',
            scopedSlots: { customRender: 'contactName' }
          },
          {
            key: 'employeeName',
            dataIndex: 'employeeName',
            title: '所属客服',
            scopedSlots: { customRender: 'employeeName' }
          },
          {
            key: 'roomName',
            dataIndex: 'roomName',
            title: '用户添加群聊',
            scopedSlots: { customRender: 'roomName' }
          },
          {
            key: 'joinScene',
            dataIndex: 'joinScene',
            title: '入群方式',
            scopedSlots: { customRender: 'joinScene' }
          },
          {
            key: 'joinTime',
            dataIndex: 'joinTime',
            title: '加入群聊时间'
          },
          {
            key: 'operate',
            dataIndex: 'operate',
            title: '操作',
            scopedSlots: { customRender: 'operate' }
          }
        ],
        data: []
      }
    }
  },
  created () {
    // 获取表格传来的id
    this.idRow = this.$route.query.idRow
    this.paramsTable.id = this.idRow
    this.getDetailsData(this.idRow)
    this.getTableData(this.paramsTable)
  },
  methods: {
    // 更新数据
    updateTable () {
      this.table.data = []
      this.getTableData(this.paramsTable)
    },
    // 选择入群方式
    joinGroupMode (e) {
      this.getTableData(this.paramsTable)
    },
    // 搜索用户
    searchUser () {
      this.getTableData(this.paramsTable)
    },
    // 搜索群聊
    searchGroup () {
      this.getTableData(this.paramsTable)
    },
    // 清空输入框
    emptyInput (type) {
      if (type == 0) {
        if (this.paramsTable.contact_name == '') {
          this.getTableData(this.paramsTable)
        }
      } else {
        if (this.paramsTable.room_name == '') {
          this.getTableData(this.paramsTable)
        }
      }
    },
    // 搜索时间
    searchTime (date, dateString) {
      this.paramsTable.start_time = dateString[0]
      this.paramsTable.end_time = dateString[1]
      this.getTableData(this.paramsTable)
    },
    // 重置事件
    resetTable () {
      var resetParams = {
        id: this.idRow,
        page: 1,
        perPage: 15
      }
      this.selectDate = []
      this.paramsTable = resetParams
      this.getTableData(this.paramsTable)
    },
    // 获取表格数据
    getTableData (data) {
      showContactRoomApi(data).then((res) => {
        this.table.data = res.data.list
      })
    },
    // 获取数据详情
    getDetailsData (id) {
      showApi({ id }).then((res) => {
        console.log(res)
        // 后台基本信息
        this.auto_tag = res.data.auto_tag
        this.tagRule = this.auto_tag.tagRule
        // 后台数据统计
        this.statistics = res.data.statistics
      })
    }
  }
}
</script>

<style lang="less" scoped>
.title {
  font-size: 15px;
  line-height: 21px;
  color: rgba(0, 0, 0, .85);
  border-bottom: 1px solid #e9ebf3;
  padding-bottom: 16px;
  margin-bottom: 16px;
  position: relative;
}

.information{
  display: flex;

  .ifm-left{
    flex: 0.4;
  }

  .ifm-right{
    flex: 1.6;
    background-color: #f6f6f6;
    border: 1px solid #e7e7e7;
    min-height: 260px;
  }

  .title-set{
    padding-top: 14px;
    padding-left: 12px;
  }
}
.black{
  color: #000;
}
.title-small{
  display: block;
  padding-left: 4px;
  border-left: 3px solid #8d8d8d;
  margin-bottom: 4px;
}
.keys{
  span{
    font-weight: bold;
    margin-left: 1px;
    margin-right: 1px;
  }
}
.data-box {
  display: flex;
  justify-content: center;
  flex-direction: column;
  margin-top: 25px;
  width: 500px;
  height: 125px;

  .data {
    flex: 1;
    height: 120px;
    background: #fbfdff;
    border: 1px solid #daedff;
    display: flex;
    align-items: center;

    .item {
      flex: 1;
      border-right: 1px solid #e9e9e9;

      .count {
        font-size: 24px;
        font-weight: 500;
        text-align: center;
      }

      .desc {
        font-size: 13px;
        text-align: center;
      }

      &:last-child {
        border-right: 0;
      }
    }

    &:last-child {
      margin-right: 0;
    }
  }
}
.search{
  display: flex;

  .search-box{
    display: flex;
    flex: 1;
  }
}
.state{
  display: flex;
  align-items: center;
}
</style>
