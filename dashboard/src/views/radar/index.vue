<template>
  <div class="interactive_radar_index">
    <div class="header">
      <div
        :class="{'item':true,'active':type.current === v}"
        v-for="(v,i) in type.list"
        :key="i"
        @click="switchTable(v,i)"
      >
        {{ v }}
      </div>
      <div class="switch_account">
        公众号：<a-select style="width: 150px" v-model="officialAccount">
          <a-select-option :value="item.nickname" v-for="(item,index) in publiclist" :key="index">
            {{ item.nickname }}
          </a-select-option>
        </a-select>
      </div>
    </div>
    <div class="add mt16 btnRow">
      <a-button type="primary" @click="goCreatePage">
        新建雷达{{ type.current }}
      </a-button>
      <a-input-search
        placeholder="请输入要搜索的名称"
        style="width: 200px;"
        v-model="titleName"
        @search="retrievalTable"
        :allowClear="true"
        @change="emptyNickIpt"
      />
    </div>
    <a-card class="mt16">
      <a-table
        :columns="type.index != 2 ? table.col : table.colTitle"
        :data-source="table.data">
        <div slot="link" slot-scope="row">
          <div class="link-text">
            {{ row.link }}
          </div>
        </div>
        <div slot="case" slot-scope="row">
          <div class="card">
            <div v-if="type.index==0">
              <div class="title">{{ row.title }}</div>
              <div class="flex">
                <div class="desc">{{ row.link_description }}</div>
                <div class="img"><img :src="row.link_cover"></div>
              </div>
            </div>
            <div v-else>
              <div class="title">{{ row.pdf_name }}</div>
              <div class="flex">
                <div class="desc">{{ row.link_description }}</div>
                <div class="img"><img src="../../assets/quick-reply-pdf-cover.png"></div>
              </div>
            </div>
          </div>
        </div>
        <div slot="article" slot-scope="row">
          <div class="card">
            <div class="title">{{ row.article.title }}</div>
            <div class="flex">
              <div class="desc">{{ row.article.desc }}</div>
              <div class="img"><img :src="row.article.cover_url"></div>
            </div>
          </div>
        </div>
        <div slot="contact_tags" slot-scope="row">
          <a-tag v-for="(tag,i) in row.contact_tags" :key="i">
            {{ tag }}
          </a-tag>
        </div>
        <div slot="operation" slot-scope="row">
          <a @click="$refs.share.show(type.index,row)">分享</a>
          <a-divider type="vertical"/>
          <a @click="detailsBtn(row.id)">详情</a>
          <a-divider type="vertical"/>

          <a-popover trigger="click" placement="bottomRight">
            <template slot="content">
              <a @click="modifyBtn(row.id)">修改</a>
              <a-divider type="vertical" />
              <a @click="delTableRow(row.id)">删除</a>
            </template>
            <a>编辑</a>
            <a-icon type="caret-down" :style="{ color: '#1890ff' }"/>
          </a-popover>
        </div>
      </a-table>
    </a-card>

    <share ref="share"/>
    <!--    授权提示-->
    <warrantTip ref="warrantTip" />
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { indexApi, destroyApi, publicIndexApi } from '@/api/radar'
import share from '@/views/radar/components/share'
import warrantTip from '@/components/warrantTip/warrantTip'
export default {
  components: { share, warrantTip },
  data () {
    return {
      // 公众号列表
      publiclist: [],
      // 公众号
      officialAccount: '',
      // 请求数据
      // 搜索的名称
      titleName: '',
      type: {
        list: ['链接', 'PDF', '文章'],
        current: '链接',
        index: 0
      },
      table: {
        col: [
          {
            title: '雷达标题',
            dataIndex: 'title'
          },
          {
            title: '链接',
            scopedSlots: { customRender: 'link' }
          },
          {
            title: '示例',
            scopedSlots: { customRender: 'case' }
          },
          {
            title: '客户标签',
            scopedSlots: { customRender: 'contact_tags' }
          },
          {
            title: '创建人',
            dataIndex: 'nickname'
          },
          {
            title: '点击人数',
            dataIndex: 'click_num'
          },
          {
            title: '创建时间',
            dataIndex: 'created_at'
          },
          {
            title: '类型',
            dataIndex: 'type'
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        colTitle: [
          {
            title: '雷达标题',
            dataIndex: 'title'
          },
          {
            title: '链接',
            scopedSlots: { customRender: 'link' }
          },
          {
            title: '示例',
            scopedSlots: { customRender: 'article' }
          },
          {
            title: '客户标签',
            scopedSlots: { customRender: 'contact_tags' }
          },
          {
            title: '创建人',
            dataIndex: 'nickname'
          },
          {
            title: '点击人数',
            dataIndex: 'click_num'
          },
          {
            title: '创建时间',
            dataIndex: 'created_at'
          },
          {
            title: '类型',
            dataIndex: 'type'
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        data: []
      }
    }
  },
  created () {
    this.getTableData({ type: 1 })
    this.setUpPublicName()
    this.getPublicList()
  },
  methods: {
    // 设置公众号
    setUpPublicName () {
      publicIndexApi({ type: 6 }).then((res) => {
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
    // 详情
    detailsBtn (id) {
      if (this.type.index == 0) {
        this.$router.push({ path: '/radar/detail?id=' + id })
      } else if (this.type.index == 1) {
        this.$router.push({ path: '/radar/pdfDetail?id=' + id })
      } else {
        this.$router.push({ path: '/radar/articleDetail?id=' + id })
      }
    },
    // 修改
    modifyBtn (id) {
      if (this.type.index == 0) {
        this.$router.push({ path: '/radar/edit?id=' + id })
      } else if (this.type.index == 1) {
        this.$router.push({ path: '/radar/editPdf?id=' + id })
      } else {
        this.$router.push({ path: '/radar/editArticle?id=' + id })
      }
    },
    // 删除
    delTableRow (id) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          destroyApi({ id }).then((res) => {
            that.$message.success('删除成功')
            that.getTableData({
              type: that.type.index + 1,
              title: that.titleName
            })
          })
        }
      })
    },
    // 搜索输入框
    retrievalTable () {
      this.getTableData({
        type: this.type.index + 1,
        title: this.titleName
      })
    },
    // 清空搜索框
    emptyNickIpt () {
      if (this.titleName == '') {
        this.getTableData({
          type: this.type.index + 1,
          title: this.titleName
        })
      }
    },
    // 切换面板
    switchTable (item, index) {
      this.type.current = item
      this.type.index = index
      this.getTableData({ type: this.type.index + 1 })
    },
    // 获取表格数据
    getTableData (params) {
      indexApi(params).then((res) => {
        this.table.data = res.data.list
      })
    },
    goCreatePage () {
      const routerMap = {
        '链接': 'createLink',
        'PDF': 'createPdf',
        '文章': 'createArticle'
      }
      this.$router.push(routerMap[this.type.current])
    }
  }
}
</script>

<style lang="less" scoped>
/deep/ .ant-card-body {
  padding: 0;
}
.switch_account{
  position: absolute;
  z-index: 10;
  right: 4px;
  top: 9px;
}
.header {
  height: 50px;
  background: #fff;
  border-bottom: 1px solid #e8e8e8;
  display: flex;
  align-items: center;
  position: relative;

  .item {
    padding: 13px 25px;
    color: #595959;
    font-size: 16px;
    cursor: pointer;
    position: relative;
  }

  .active {
    color: #1890ff;

    &:after {
      position: absolute;
      content: "";
      background: #1890ff;
      width: 40px;
      height: 3px;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1;
    }
  }
}

.link-text {
  width: 170px;
  max-height: 60px;
  overflow: hidden;
  display: inline-block;
  word-break: break-all;
}
.btnRow{
  display: flex;
  justify-content:space-between;
}
.card {
  width: 229px;
  background: #fff;
  border: 1px solid #f0f0f0;
  padding: 10px 12px;
  .title {
    font-size: 14px;
    color: #191919;
  }
  .desc {
    width: 160px;
    font-size: 12px;
  }
  img {
    width: 47px;
    height: 47px;
    object-fit: cover;
  }
}
</style>
