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
            <span>根据客户成为企业微信客户时间段</span>
          </div>
          <div class="founder mt16">
            <span>创建者：</span>
            <a-tag><a-icon type="user" />
              {{ auto_tag.nickname }}</a-tag>
          </div>
          <div class="add-tag mt16">
            <span>生效成员：</span>
            <span><a-tag v-for="(item,index) in auto_tag.employees" :key="index"><a-icon type="user" />{{ item.name }}</a-tag>
            </span>
          </div>
          <div class="create-time mt16">
            <span>创建时间：</span>
            <span>{{ auto_tag.createdAt }}</span>
          </div>
          <div class="auto mt16">
            <span>自动添加标签：</span>
            <a-tag v-for="(item,index) in auto_tag.tags" :key="index">{{ item }}</a-tag>
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
            <span class="black">
              共{{ tagRule.length }}条规则
            </span>
          </div>
          <div class="set-content mt18 ml84" v-for="(item,index) in tagRule" :key="index">
            <span class="title-small black">规则{{ index+1 }}</span>
            <p class="keys ml4">客户在 每 <span v-if="item.time_type==1">天</span><span v-if="item.time_type==2">周</span><span v-if="item.time_type==3">月</span> 的
              <template v-if="item.time_type==2">
                <span v-for="obj in item.schedule" :key="obj">{{ weekData[obj].value }}</span>
              </template>
              <template v-if="item.time_type==3">
                <span v-for="obj in item.schedule" :key="obj">{{ monthData[obj].value }}</span>
              </template>
              【{{ item.start_time }}-{{ item.end_time }}】
              内添加生效成员时， 将会被自动打上
              <a-tag v-for="(obj,idx) in item.tags" :key="idx">{{ obj.tagname }}</a-tag>
              标签</p>
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
              @change="emptyInput"
            />
          </div>
          <!--          所属客户-->
          <div class="customer-service ml20">
            <span>所属客服：</span>
            <a-select style="width: 160px" default-value="请选择客服">
              <a-select-option value="Home">刘波</a-select-option>
              <a-select-option value="Company">小子</a-select-option>
            </a-select>
          </div>
          <!--          规则筛选-->
          <div class="join-mode">
            <span class="ml20">规则筛选：</span>
            <a-select style="width: 180px" default-value="请选择规则">
              <a-select-option value="1">
                规则一
              </a-select-option>
              <a-select-option value="2">
                规则二
              </a-select-option>
            </a-select>
          </div>
          <!--          添加好友时间-->
          <div class="add-time ml20">
            <span>添加好友时间：</span>
            <a-range-picker style="width: 220px" @change="searchTime" :allowClear="true" v-model="selectDate"/>
          </div>
        </div>
        <!--        重置-->
        <div class="reset"><a-button @click="resetTable">重置</a-button></div>
      </div>
      <div class="table-box mt36">
        <div class="store-box mb20">
          <span class="customers-title">共{{ table.data.length }}个客户</span>
          <a-divider type="vertical" />
          <span style="cursor: pointer;" @click="updateTable"><a-icon type="redo" />更新数据</span>
        </div>
        <div class="table">
          <a-table :columns="table.col" :data-source="table.data">
            <div slot="employeeName" slot-scope="text">
              <a-tag><a-icon type="user" />{{ text }}</a-tag>
            </div>
            <div slot="tagRuleId" slot-scope="text">
              <a-tag>规则{{ text }}</a-tag>
            </div>
            <div slot="operate" slot-scope="text,record">
              <div>
                <a @click="clientDetails(record)">客户详情</a>
              </div>
            </div>
          </a-table>
        </div>
      </div>
    </a-card>
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { showApi, showContactTimeApi } from '@/api/autoTag'
export default {
  data () {
    return {
      // 周
      weekData: [
        {
          key: '0',
          value: '周日'
        },
        {
          key: '1',
          value: '周一'
        },
        {
          key: '2',
          value: '周二'
        }, {
          key: '3',
          value: '周三'
        }, {
          key: '4',
          value: '周四'
        }, {
          key: '5',
          value: '周五'
        }, {
          key: '6',
          value: '周六'
        }
      ],
      // 月
      monthData: [
        {
          key: '1',
          value: '1号'
        }, {
          key: '2',
          value: '2号'
        }, {
          key: '3',
          value: '3号'
        }, {
          key: '4',
          value: '4号'
        }, {
          key: '5',
          value: '5号'
        }, {
          key: '6',
          value: '6号'
        }, {
          key: '7',
          value: '7号'
        }, {
          key: '8',
          value: '8号'
        }, {
          key: '9',
          value: '9号'
        }, {
          key: '10',
          value: '10号'
        }, {
          key: '11',
          value: '11号'
        }, {
          key: '12',
          value: '12号'
        }, {
          key: '13',
          value: '13号'
        }, {
          key: '14',
          value: '14号'
        }, {
          key: '15',
          value: '15号'
        }, {
          key: '16',
          value: '16号'
        }, {
          key: '17',
          value: '17号'
        }, {
          key: '18',
          value: '18号'
        }, {
          key: '19',
          value: '19号'
        }, {
          key: '20',
          value: '20号'
        }, {
          key: '21',
          value: '21号'
        }, {
          key: '22',
          value: '22号'
        }, {
          key: '23',
          value: '23号'
        }, {
          key: '24',
          value: '24号'
        }, {
          key: '25',
          value: '25号'
        }, {
          key: '26',
          value: '26号'
        }, {
          key: '27',
          value: '27号'
        }, {
          key: '28',
          value: '28号'
        }, {
          key: '29',
          value: '29号'
        }, {
          key: '30',
          value: '30号'
        }, {
          key: '31',
          value: '31号'
        }, {
          key: '32',
          value: '32号'
        }
      ],
      // 后台基本信息
      auto_tag: [],
      tagRule: [],
      // 后台数据统计
      statistics: [],
      selectDate: [],
      // 表格请求数据
      paramsTable: {
        // 搜索客户名称
        contact_name: '',
        // 客服
        employee: '',
        // 开始时间
        start_time: '',
        // 结束时间
        end_time: '',
        page: 1,
        perPage: 15
      },
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
            key: 'tagRuleId',
            dataIndex: 'tagRuleId',
            title: '生效规则',
            scopedSlots: { customRender: 'tagRuleId' }
          },

          {
            key: 'createdAt',
            dataIndex: 'createdAt',
            title: '添加好友时间'
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
    // 清空输入框
    emptyInput () {
      if (this.paramsTable.contact_name == '') {
        this.getTableData(this.paramsTable)
      }
    },
    // 搜索用户
    searchUser () {
      this.getTableData(this.paramsTable)
    },
    // 更新数据
    updateTable () {
      this.table.data = []
      this.getTableData(this.paramsTable)
    },
    // 客户详情
    clientDetails (record) {},
    // 获取表格数据
    getTableData (data) {
      showContactTimeApi(data).then((res) => {
        this.table.data = res.data.list
      })
    },
    // 获取数据详情
    getDetailsData (id) {
      showApi({ id }).then((res) => {
        // 后台基本信息
        console.log(res)
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
