<template>
  <div class="automatic-pulling">
    <!-- <a-row>
      <a-col :span="5">
        <div class="left">
          <a-select style="width: 100%"></a-select>
        </div>
      </a-col>
      <a-col :span="19"> -->
    <div :split="false" class="lists">
      <a-alert :show-icon="false" message="自动拉群，不仅可以完全实现不需要员工手工拉客户进群，而且完全打破企业微信官方只能选择5个群聊的局限，流程如下：" banner />
      <a-alert :show-icon="false" message="1、在本系统添加多个群聊的二维码（群聊数量足够），添加完成后会生成一个渠道活码，一个渠道活码对应一个或多个使用成员（相当于客服）。" banner />
      <a-alert :show-icon="false" message="2、当客户扫该渠道活码之后就会先添加使用成员，同时客户会收到管理员提前设置的入群引导语，引导语中包括入群二维码，然后扫码进群。" banner />
      <a-alert :show-icon="false" message="3、当第一个群达到人数上限（最多200人）后，客户扫码添加使用成员后，推送第二群的入群二维码，客户扫码进入第二个群聊，依次往后进行。" banner />
      <a-alert :show-icon="false" message="在整个流程中，即使在客户不入群或当客户退群时，该客户其实就已添加过客服，可以保持单独联系，完善客户画像，提供一对一的精准服务。" banner />
    </div>
    <a-card>
      <a-row>
        <a-col :span="6">
          <a-input v-model="qrcodeName" placeholder="搜素群活码名称"></a-input>
        </a-col>
        <a-col :span="6" :offset="1">
          <a-button v-permission="'/workRoomAutoPull/index@search'" type="primary" style="marginRight:10px" @click="getTableData">查找</a-button>
          <a-button @click="reset">重置</a-button>
        </a-col>
      </a-row>
      <div class="table-wrapper">
        <div class="top-btn">
          <!-- <a-button class="batch" type="primary" @click="batchDownload">批量下载</a-button> -->
          <a-button v-permission="'/workRoomAutoPull/store'" class="batch" type="primary" @click="createWorkRoom">新建拉群</a-button>
        </div>
        <a-table
          bordered
          rowKey="workRoomAutoPullId"
          :columns="columns"
          :data-source="tableData"
          :pagination="pagination"
          :row-selection="{ selectedRowKeys, onChange: onSelectChange }"
          @change="handleTableChange"
        >
          <div slot="qrcodeUrl" slot-scope="text">
            <template>
              <img :src="text" class="img">
            </template>
          </div>
          <div slot="employees" slot-scope="text">
            <template>
              <div v-for="(item, index) in text" :key="index">
                {{ item }}
              </div>
            </template>
          </div>
          <div slot="tags" slot-scope="text">
            <template>
              <div v-for="(item, index) in text" :key="index">
                {{ item }}
              </div>
            </template>
          </div>
          <div slot="rooms" slot-scope="text">
            <template>
              <div class="table-room" v-for="(item, index) in text" :key="index">
                <span>{{ item.roomName }}</span>
                <span class="status">{{ item.stateText }}</span>
              </div>
            </template>
          </div>
          <div slot="action" slot-scope="text, record">
            <template>
              <a-button v-permission="'/workRoomAutoPull/index@detail'" type="link" @click="detail(record.workRoomAutoPullId)">详情</a-button>
              <a-button v-permission="'/workRoomAutoPull/index@edit'" type="link" @click="edit(record.workRoomAutoPullId)">编辑</a-button>
              <div v-permission="'/workRoomAutoPull/index@download'" class="download">
                <a :href="record.qrcodeUrl" download>下载</a>
              </div>
              <!-- <a-button type="link" @click="move(record.workRoomAutoPullId)">移动</a-button> -->
            </template>
          </div>
        </a-table>
      </div>
    </a-card>
    <!-- </a-col>
    </a-row> -->
    <a-modal
      :title="detailTitle"
      :maskClosable="false"
      :width="800"
      :visible="detailShow"
      @cancel="detailShow = false"
    >
      <div class="detail-wrapper">
        <div class="detail-left">
          <div class="top">
            <img class="detail-img" :src="detailData.qrcodeUrl" alt="">
            <div class="text">
              <div>
                创建日期：
                {{ detailData.createdAt }}
              </div>
              <div>{{ detailData.qrcodeName }}</div>
              <a-button class="top-btn" type="primary">
                <a :href="detailData.qrcodeUrl" download>下载活码</a>
              </a-button>
              <a-button class="top-btn" @click="edit(workRoomAutoPullId)">修改</a-button>
            </div>
          </div>
          <div class="tags">
            <p>入群标签</p>
            <ul class="tags-wrapper">
              <li class="tag-item" v-for="(item, index) in detailData.tags" :key="index">
                <a-button class="group-name">{{ item.groupName }}</a-button>
                <div class="tag-content">
                  <a-tag class="tag" color="green" v-for="(inner, i) in item.list" :key="i">{{ inner.tagName }}</a-tag>
                </div>

              </li>
            </ul>
          </div>
          <div class="bottom">
            <div class="bottom-left">
              共 {{ detailData.roomNum }} 个群聊
            </div>
            <ul class="bottom-right">
              <li class="room-item" v-for="(item, index) in detailData.rooms" :key="index">
                <div class="room-name">{{ `${item.roomName}(${item.num})` }}</div>
                <a-tag color="green" class="room-status">{{ item.state == 0 ? '未开始' : '拉人中' }}</a-tag>
              </li>
            </ul>
          </div>
        </div>
        <div class="detail-right">
          <div class="member-wrapper">
            <div class="member-text">使用成员</div>
            <div class="member-tags">
              <a-tag class="member-item" v-for="(item, index) in detailData.employees" :key="index">{{ item.employeeName }}</a-tag>
            </div>
          </div>
        </div>
      </div>
      <div slot="footer">
        <template>
          <a-button @click="detailShow = false">关闭</a-button>
        </template>
      </div>
    </a-modal>
  </div>
</template>

<script>
import { workRoomAutoPullList, autoPullShow } from '@/api/workRoom'
export default {
  data () {
    return {
      columns: [
        {
          title: '二维码',
          dataIndex: 'qrcodeUrl',
          scopedSlots: { customRender: 'qrcodeUrl' }
        },
        {
          title: '名称',
          dataIndex: 'qrcodeName'
        },
        {
          title: '使用成员',
          dataIndex: 'employees',
          scopedSlots: { customRender: 'employees' }
        },
        {
          title: '标签',
          dataIndex: 'tags',
          scopedSlots: { customRender: 'tags' }
        },
        {
          title: '客户数',
          dataIndex: 'contactNum'
        },
        {
          title: '群聊',
          dataIndex: 'rooms',
          scopedSlots: { customRender: 'rooms' }
        },
        {
          title: '创建时间',
          dataIndex: 'createdAt'
        },
        {
          title: '操作',
          dataIndex: 'action',
          width: '150px',
          scopedSlots: { customRender: 'action' }
        }
      ],
      tableData: [],
      qrcodeName: '',
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      selectedRowKeys: [],
      detailData: {},
      detailShow: false,
      workRoomAutoPullId: ''
    }
  },
  computed: {
    detailTitle () {
      return `【${this.detailData.qrcodeName || ''}】详情`
    }
  },
  created () {
    this.getTableData()
  },
  methods: {
    async getTableData () {
      const params = {
        qrcodeName: this.qrcodeName,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      try {
        const { data: { page: { total }, list } } = await workRoomAutoPullList(params)
        this.tableData = list
        this.pagination.total = total
      } catch (e) {
        console.log(e)
      }
    },
    reset () {
      this.qrcodeName = ''
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      // this.getTableData()
    },
    onSelectChange (selectedRowKeys, selectedRows) {
      this.selectedRowKeys = selectedRowKeys
    },
    batchDownload () {

    },
    createWorkRoom () {
      this.$router.push({ path: '/workRoomAutoPull/store' })
    },
    async detail (id) {
      this.detailShow = true
      this.workRoomAutoPullId = id
      try {
        const { data } = await autoPullShow({ workRoomAutoPullId: id })
        this.detailData = data
      } catch (e) {
        console.log(e)
      }
    },
    edit (workRoomAutoPullId) {
      this.$router.push({
        path: '/workRoomAutoPull/store',
        query: {
          id: workRoomAutoPullId
        }
      })
    },
    move () {

    }
  }
}
</script>

<style lang="less" scoped>
.automatic-pulling {
    height: 100%;
    .left {
      background: #fff;
      padding: 15px 15px;
    }
    .lists {
      margin-bottom: 20px;
    }
    .table-wrapper {
      margin-top: 20px;
      .img {
        width: 50px;
        height: 50px
      }
      .table-room {
        margin-bottom: 5px;
      }
      .status {
        width: 60px;
        display: inline-block;
        margin-left: 10px;
        background: #1890ff;
        text-align: center;
        color: #fff
      }
      .top-btn{
        display: flex;
        justify-content: flex-end;
        margin-bottom: 10px;
        .batch{
          margin-right: 20px;
        }
      }
    }
    .download {
      padding: 0 15px;
    }
  }
  .detail-wrapper {
    display: flex;
    border: 1px solid rgba(0, 0, 0, 0.1);
    .detail-left {
      flex:  0 60%;
      border-right: 1px solid rgba(0, 0, 0, 0.1);
      .top {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        .text {
          margin-top: 10px;
          display: flex;
          flex-direction: column;
          align-items: center;
          .top-btn {
            display: block;
            width: 100px;
            margin-bottom: 10px;
          }
        }
        .detail-img {
          width: 100px;
          height: 100px;
        }
      }
      .tags {
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        padding: 10px 0;
        .tag {
          margin-left: 10px;
          margin-bottom: 10px;
        }
      }
      .bottom {
        margin: 10px 0;
        display: flex;
        .bottom-left {
          flex: 0 0 20%
        }
        .bottom-right {
          flex: 1;
          .room-item {
            display: flex;
            flex-direction: row;
            margin-bottom: 5px;
            .room-name {
              width: 60%;
              border: 1px solid rgba(0, 0, 0, 0.1);
              margin-right: 10px;
            }
            .room-status {
              width: 35%;
            }
          }
        }
      }
    }
    .detail-right {
      flex: 0 0 40%;
      .member-wrapper {
        display: flex;
        flex-direction: row;
        .member-text {
          flex: 0 0 80px;
        }
        .member-tags {
          flex: 1;
          .member-item {
            margin-bottom: 10px;
          }
        }
      }
    }
    .tags-wrapper {
      padding: 0;
      .tag-item {
        padding-left: 10px;
        margin-bottom: 5px;
        display: flex;
        flex-wrap: wrap;
        .group-name {
          margin-right: 10px;
          margin-bottom: 5px;
          flex: 0 0 100px;
        }
        .tag-content {
          flex: 1;
          display: flex;
          align-items: center;
          flex-wrap: wrap;
        }
        .tag {
          margin-bottom: 5px;
        }
      }
    }
  }

</style>
