<template>
  <div class="box">
    <div class="switch_account">
      公众号：<a-select style="width: 150px" v-model="officialAccount">
        <a-select-option :value="item.nickname" v-for="(item,index) in publiclist" :key="index">
          {{ item.nickname }}
        </a-select-option>
      </a-select>
    </div>
    <a-card>
      <a-tabs type="card" @change="analysis" v-model="currentTab">
        <a-tab-pane key="1" tab="任务列表">
          <!--        搜索-->
          <div class="search">
            <div class="create mt8">
              <a-button
                type="primary"
                @click="$router.push('/lottery/create')">
                创建抽奖活动
              </a-button>
            </div>
          </div>
          <div class="screen mt16 mb14">
            <span>
              筛选：
            </span>
            <a-radio-group name="radioGroup" :default-value="0" @change="listChange">
              <a-radio :value="0">
                全部
              </a-radio>
              <a-radio :value="1">
                进行中
              </a-radio>
              <a-radio :value="2">
                未开始
              </a-radio>
              <a-radio :value="3">
                已结束
              </a-radio>
            </a-radio-group>
          </div>
          <div class="task-list">
            <a-table :columns="listData.table.col" :data-source="listData.table.data">
              <div slot="contact_clock_tags" slot-scope="row">
                <a-tag
                  v-for="tag in row.contact_clock_tags"
                  :key="tag.tagid"
                >
                  {{ tag[0] }}
                </a-tag>
              </div>
              <div slot="nickname" slot-scope="row">
                <a-tag>
                  <a-icon type="user"/>
                  {{ row.nickname }}
                </a-tag>
              </div>
              <div slot="operation" slot-scope="row">
                <a @click="details(row)">详情</a>
                <a-divider type="vertical"/>
                <a @click="$refs.share.show(row.id)">分享</a>
                <a-divider type="vertical"/>
                <a-dropdown>
                  <a class="ant-dropdown-link" @click="e => e.preventDefault()">
                    编辑
                    <a-icon type="down"/>
                  </a>
                  <a-menu slot="overlay">
                    <a-menu-item>
                      <a @click="modifyData(row.id)">修改</a>
                    </a-menu-item>
                    <a-menu-item>
                      <a @click="delData(row.id)">
                        删除
                      </a>
                    </a-menu-item>
                  </a-menu>
                </a-dropdown>
              </div>
            </a-table>
          </div>

        </a-tab-pane>
        <a-tab-pane key="2" tab="数据分析">
          <div class="current">
            <span>
              当前活动：
            </span>
            <div>
              <a-input-group compact>
                <a-select style="width: 140px" @change="detailsDataChange" v-model="dataAnalysis.lotteryCurrent">
                  <a-select-option v-for="v in listData.table.data" :key="v.id">
                    {{ v.name }}
                  </a-select-option>
                </a-select>
              </a-input-group>
            </div>
            <span class="activity-status">
              活动进行中
            </span>
          </div>
        </a-tab-pane>
      </a-tabs>
    </a-card>
    <!--    抽奖活动详情-->
    <div class="big-content" v-show="dataShow">
      <div class="content-top mt16">
        <a-card>
          <div class="details-box">
            <div class="title">
              抽奖活动详情
            </div>
            <div class="details-content">
              <div class="details-left">
                <div class="preview elastic">
                  <span>
                    预览：
                  </span>
                  <div class="preview-box">
                    <span>
                      {{ data.lottery.name }}
                    </span>
                    <div class="preview-bottom mt16">
                      <div style="font-size: 10px;color: #8d8d8d">
                        {{ data.lottery.description }}
                      </div>
                      <img
                        src="../../assets/lottery-default-cover.png"
                        style="position: absolute;width: 40px;height: 40px;right: 10px;bottom: 10px">
                    </div>
                  </div>
                </div>
                <div class="activity mt20 elastic">
                  <span>
                    活动名称：
                  </span>
                  <div class="activity-name">
                    <span>
                      {{ data.lottery.name }}
                    </span>
                    <span class="activity-status">
                      {{ data.lottery.status }}
                    </span>
                  </div>
                </div>
                <div class="founder mt16 elastic">
                  <span>
                    创建人：
                  </span>
                  <a-tag>
                    <a-icon type="user"/>
                    {{ data.lottery.nickname }}
                  </a-tag>
                </div>
                <div class="creation-time mt16 elastic">
                  <span>
                    创建时间:
                  </span>
                  <div class="time ml8">
                    <span>
                      {{ data.lottery.createdAt }}
                    </span>
                  </div>
                </div>
                <div class="end-date mt16 elastic">
                  <span>
                    截止时间：
                  </span>
                  <div class="end">
                    {{ data.lottery.time }}
                  </div>
                </div>
              </div>
              <div class="details-right">
                <div class="rule elastic">
                  <span>
                    抽奖规则：
                  </span>
                  <div class="rule-box">
                    <div class="lottery-restrictions">
                      <span class="small-title">
                        抽奖限制
                      </span>
                      <div class="text">
                        <span v-if="data.lottery.draw_set.max_total_num">
                          每人总抽奖次数：
                          <span>
                            {{ data.lottery.draw_set.max_total_num }}
                          </span>
                          次
                        </span>
                        <span v-if="data.lottery.draw_set.max_every_day_num">
                          每人每日抽奖次数：
                          <span>
                            {{ data.lottery.draw_set.max_every_day_num }}
                          </span>
                          次
                        </span>
                        <span v-if="data.lottery.draw_set.point_deplete">
                          每人每次抽奖消耗积分：
                          <span>
                            {{ data.lottery.draw_set.point_deplete }}
                          </span>
                          分
                        </span>
                      </div>
                    </div>
                    <div class="lottery-restrictions mt12">
                      <span class="small-title">
                        中奖限制
                      </span>
                      <div class="text">
                        <span v-if="data.lottery.win_set.max_total_num">
                          每人总中奖次数：
                          <span>
                            {{ data.lottery.win_set.max_total_num }}
                          </span>
                          次
                        </span>
                        <span v-if="data.lottery.win_set.max_every_day_num">
                          每人每日中奖次数：
                          <span>
                            {{ data.lottery.win_set.max_every_day_num }}
                          </span>
                          次
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </a-card>
      </div>
      <div class="content-middle">
        <div class="middle-left">
          <a-card style="height: 390px">
            <div class="title">
              数据总览
            </div>
            <div class="data-box mb20">
              <div class="data">
                <div class="item">
                  <div class="count">
                    {{ data.data_statistics.total_browse_user }}
                  </div>
                  <div class="desc">
                    总浏览人数
                  </div>
                </div>
                <div class="item">
                  <div class="count">
                    {{ data.data_statistics.total_draw_user }}
                  </div>
                  <div class="desc">
                    总参与人数
                  </div>
                </div>
                <div class="item">
                  <div class="count">
                    {{ data.data_statistics.total_win_user }}
                  </div>
                  <div class="desc">
                    总获奖人数
                  </div>
                </div>
              </div>
              <div class="data mt15">
                <div class="item">
                  <div class="count">
                    {{ data.data_statistics.today_browse_user }}
                  </div>
                  <div class="desc">
                    今日浏览人数
                  </div>
                </div>
                <div class="item">
                  <div class="count">
                    {{ data.data_statistics.today_draw_user }}
                  </div>
                  <div class="desc">
                    今日参与人数
                  </div>
                </div>
                <div class="item">
                  <div class="count">
                    {{ data.data_statistics.today_win_user }}
                  </div>
                  <div class="desc">
                    今日获奖人数
                  </div>
                </div>
              </div>
            </div>
          </a-card>
        </div>
        <div class="middle-right">
          <a-card style="height: 390px">
            <div class="title">
              奖励明细
            </div>
            <div class="award-details">
              <a-table
                :columns="dataAnalysis.detailed.col"
                :data-source="dataAnalysis.detailed.data"
                :scroll="{ y: 240 }"
                :pagination="false"
              />
            </div>
          </a-card>
        </div>
      </div>
      <div class="content-bottom mt16">
        <a-card>
          <div class="title">
            客户数据详情
          </div>
          <div class="details-select">
            <div class="screen mt16 mb14">
              <div class="screen-box" style="flex: 1">
                <span>
                  筛选：
                </span>
                <a-radio-group name="radioGroup" :default-value="0" @change="detailsChange">
                  <a-radio :value="0">
                    全部
                  </a-radio>
                  <a-radio :value="1">
                    已获奖
                  </a-radio>
                  <a-radio :value="2">
                    已参与
                  </a-radio>
                  <a-radio :value="3">
                    已浏览未参与
                  </a-radio>
                </a-radio-group>
              </div>
              <div class="button">
                <a-button type="primary" ghost @click="batchTag">批量打标签</a-button>
              </div>
              <!--   弹窗           -->
              <addlableIndex @choiceTagsArr="acceptArray" ref="childRef"/>
            </div>
            <div class="common">
              <span style="font-weight: bold">
                共{{ dataAnalysis.details.data.length }}个客户
              </span>
            </div>
            <div class="search mt18 mb16">
              <span>
                搜索用户：
              </span>
              <a-input-search placeholder="搜索用户名称" style="width: 200px" @search="detailsSearchChange"/>
            </div>
            <div class="table-details">
              <a-table
                :columns="dataAnalysis.details.col"
                :data-source="dataAnalysis.details.data"
                :rowSelection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }">
                <div slot="nickname" slot-scope="row">
                  <img :src="data.lottery.avatar" style="max-width: 30px;">
                  {{ row.nickname }}
                </div>
                <div slot="employee" slot-scope="text">
                  <a-tag v-for="(item,index) in text" :key="index"><a-icon type="user"/>
                    {{ item.name }}</a-tag>
                </div>
                <div slot="grade" slot-scope="row">
                  {{ row.grade }}
                </div>
                <div slot="contact_tags" slot-scope="row">
                  <a-tag
                    v-for="tag in row.contact_tags"
                    :key="tag.tagid">
                    {{ tag.tagname }}
                  </a-tag>
                </div>
                <div slot="win_record" slot-scope="row">
                  <a-tag
                    v-for="(item,index) in row.win_record"
                    :key="index">
                    {{ item.prizeName }}
                  </a-tag>
                </div>
                <div slot="write_off" slot-scope="text,record">
                  <a v-if="text==0" @click="writeOff(record)">核销</a>
                  <span v-else style="color: gray;">已核销</span>
                </div>
              </a-table>
            </div>
          </div>
        </a-card>
      </div>
    </div>
    <share ref="share"/>
    <!--    授权提示-->
    <warrantTip ref="warrantTip" />
  </div>
</template>

<script>
import share from '@/views/lottery/components/share'
import addlableIndex from '@/components/addlabel/index'
import warrantTip from '@/components/warrantTip/warrantTip'
// eslint-disable-next-line no-unused-vars  warrantTip
import { getList, dataDetails, getDetails, del, writeOffApi, batchContactTagsApi, publicIndexApi } from '@/api/lottery'

export default {
  components: {
    share,
    addlableIndex,
    warrantTip
  },
  data () {
    return {
      // 任务列表
      listData: {
        table: {
          col: [
            {
              title: '抽奖活动名称',
              dataIndex: 'name'
            },
            {
              title: '创建人',
              scopedSlots: { customRender: 'nickname' }
            },
            {
              title: '总参与人数',
              dataIndex: 'total_user'
            },
            {
              title: '创建时间',
              dataIndex: 'created_at'
            },
            {
              title: '活动时间',
              dataIndex: 'time'
            },
            {
              title: '状态',
              dataIndex: 'status'
            },
            {
              title: '操作',
              scopedSlots: { customRender: 'operation' }
            }
          ],
          data: []
        }
      },
      // 数据分析
      dataAnalysis: {
        detailed: {
          col: [
            {
              title: '奖项名称',
              dataIndex: 'name'
            },
            {
              title: '奖项数量',
              dataIndex: 'num'
            },
            {
              title: '抽中数量',
              dataIndex: 'win_num'
            },
            {
              title: '剩余数量',
              dataIndex: 'differ_num'
            },
            {
              title: '兑换方式',
              dataIndex: 'type'
            }
          ],
          data: []
        },
        // 客户数据详情
        details: {
          col: [
            {
              title: '全部客户',
              scopedSlots: { customRender: 'nickname' }
            },
            {
              title: '客户类型',
              dataIndex: 'type'
            },
            {
              title: '来源',
              dataIndex: 'source'
            },
            {
              title: '城市',
              dataIndex: 'city'
            },
            {
              title: '所属员工',
              dataIndex: 'employee',
              scopedSlots: { customRender: 'employee' }
            },
            {
              title: '客户评分',
              scopedSlots: { customRender: 'grade' }
            },
            {
              title: '标签',
              scopedSlots: { customRender: 'contact_tags' }
            },
            {
              title: '已抽奖次数',
              dataIndex: 'draw_num'
            },
            {
              title: '参与程度',
              dataIndex: 'status'
            },
            {
              title: '获奖记录',
              scopedSlots: { customRender: 'win_record' }
            },
            {
              title: '操作',
              dataIndex: 'write_off',
              scopedSlots: { customRender: 'write_off' }
            }
          ],
          data: []
        },
        fileterCurrent: 0,
        lotteryCurrent: ''

      },
      dataShow: false,
      data: {
        lottery: {
          name: '',
          draw_set: {
            max_total_num: '',
            max_every_day_num: '',
            point_deplete: ''

          },
          contactTags: [{
            tags: [],
            grade: 0
          }],
          win_set: {
            max_total_num: '',
            max_every_day_num: ''
          }
        },
        data_statistics: {
          total_browse_user: '',
          total_draw_user: '',
          total_win_user: '',
          today_browse_user: '',
          today_draw_user: '',
          today_win_user: ''
        }

      },
      form: {
        id: ''
      },
      currentTab: '1',
      //  选中的客户数据表格
      selectedRowKeys: [],
      // 打标签请求id
      askClientIds: [],
      // 公众号列表
      publiclist: [],
      // 公众号
      officialAccount: ''
    }
  },
  mounted () {
    // 任务列表
    this.getData()
  },
  created () {
    this.setUpPublicName()
    this.getPublicList()
  },
  methods: {
    // 设置公众号
    setUpPublicName () {
      publicIndexApi({ type: 2 }).then((res) => {
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
    // 获取选中 的客户表格数据
    onSelectChange (e) {
      this.selectedRowKeys = e
    },
    // 子组件传值
    acceptArray (e) {
      if (e.length == 0) {
        this.$message.error('请至少选择一个标签')
        return false
      }
      const askTags = []
      e.forEach((item, index) => {
        const tags = {}
        tags.tagid = item.id
        tags.tagname = item.name
        askTags.push(tags)
      })
      batchContactTagsApi({
        ids: this.askClientIds,
        tags: askTags
      }).then((res) => {
        this.detailsData(this.dataAnalysis.lotteryCurrent, this.dataAnalysis.fileterCurrent, this.dataAnalysis.nickname)
        this.selectedRowKeys = []
        this.$message.success('操作成功')
      })
    },
    // 批量打标签
    batchTag () {
      if (this.selectedRowKeys.length == 0) {
        this.$message.warning('请先选中一个客户')
        return false
      }
      this.askClientIds = []
      this.selectedRowKeys.forEach((item, index) => {
        this.askClientIds[index] = this.dataAnalysis.details.data[item].id
      })
      this.$refs.childRef.show()
    },
    writeOff (record) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否核销',
        okText: '核销',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          writeOffApi({
            id: record.id
          }).then((res) => {
            that.detailsData(that.dataAnalysis.lotteryCurrent, that.dataAnalysis.fileterCurrent, that.dataAnalysis.nickname)
            that.$message.success('核销成功')
          })
        }
      })
    },
    // 删除
    delData (id) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          del({
            id
          }).then(res => {
            that.$message.success('删除成功')
            that.getData()
          })
        }
      })
    },
    // 详情
    details (row) {
      this.dataAnalysis.lotteryCurrent = row.name
      this.dataShow = true
      this.currentTab = '2'
      this.getPrize(row.id)
      this.detailsData(row.id)
    },

    // 修改数据
    modifyData (id) {
      this.$router.push({
        path: '/lottery/modify',
        query: {
          id
        }
      })
    },
    // 数据分析 - 搜索用户回调
    detailsSearchChange (e) {
      this.dataAnalysis.nickname = e
      this.detailsData(this.dataAnalysis.lotteryCurrent, this.dataAnalysis.fileterCurrent, e)
    },

    // 数据分析 - 筛选回调
    detailsChange (e) {
      this.dataAnalysis.fileterCurrent = e.target.value
      this.detailsData(this.dataAnalysis.lotteryCurrent, e.target.value)
    },

    // 任务列表-筛选回调
    listChange (e) {
      this.getData(e.target.value)
    },

    // 获取数据分析数据
    analysis (e) {
      if (e === '1') {
        this.dataShow = false
      } else {
        this.dataShow = true
      }
    },
    // 获取任务列表
    getData (status = 0) {
      getList({
        status
      }).then(res => {
        this.listData.table.data = res.data.list
        if (res.data.list.length) {
          this.detailsData(res.data.list[0].id)
          this.getPrize(res.data.list[0].id)
          this.dataAnalysis.lotteryCurrent = res.data.list[0].id
        }
      })
    },

    // 客户数据详情切换活动
    detailsDataChange (e) {
      this.detailsData(e)
      this.getPrize(e)
    },

    // 客户数据详情
    detailsData (id = '', status = 0, nickname = '') {
      dataDetails({
        status,
        nickname,
        id
      }).then(res => {
        this.dataAnalysis.details.data = res.data.list
      })
    },

    // 奖品明细列表
    getPrize (id = '') {
      getDetails({
        id
      }).then(res => {
        this.data = res.data
        this.dataAnalysis.detailed.data = res.data.lottery.prize_set
      })
    }
  }
}
</script>

<style lang="less" scoped>
.box{
  position: relative;
}
.switch_account{
  position: absolute;
  z-index: 10;
  right: 25px;
  top: 20px;
}
.search, .screen {
  display: flex;
  align-items: center;
}

.create {
  flex: 1;
}

.title {
  font-size: 15px;
  line-height: 21px;
  color: rgba(0, 0, 0, .85);
  border-bottom: 1px solid #e9ebf3;
  padding-bottom: 12px;
  margin-bottom: 16px;
  position: relative;
}

.current {
  display: flex;
  align-items: center;
}

.activity-status {
  background-color: #e8fcdc;
  border: 1px solid #a1ec9f;
  padding: 4px 8px;
  border-radius: 2px;
  font-size: 12px;
  margin-left: 10px;
}

.details-content {
  display: flex;
}

.elastic {
  display: flex;
}

.preview-box {
  width: 200px;
  height: 90px;
  border: 1px solid #e7e7e7;
  padding: 6px;
  position: relative;
}

.preview-bottom {
  display: flex;

  div{
    overflow: auto;
    width: 132px;
    height: 36px;
  }
}

.details-left,
.details-right {
  flex: 1;
}

.rule-box {
  width: 700px;
  background-color: #f5f5f5;
  padding: 10px;
}

.tag-box {
  width: 700px;
  background-color: #f5f5f5;
  padding-left: 10px;
  padding-top: 8px;
}

.tag {
  margin-top: 20px;
}

.small-title {
  font-weight: bold;
}

.lottery-restrictions, .customer-rule {
  .text {
    margin-top: 4px;
  }
}

.customer-rule {
  margin-bottom: 12px;
}

.content-middle {
  display: flex;
  margin-top: 16px;
}

.middle-left {
  flex: 1;
  margin-right: 16px;
}

.middle-right {
  flex: 1;
}

.data-box {
  display: flex;
  justify-content: center;
  flex-direction: column;
  margin-top: 15px;
  height: 296px;

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
</style>
