<template>
  <a-card>
    <div class="tag-box">
      <div class="page_top">
        <a-button type="primary" @click="$router.push('/autoTag/joinRoomCreate')">添加规则</a-button>
        <div class="search_term">
          <div class="choice_tags">
            <span>客户标签：</span>
            <div class="showTagsBox" @click="showModel">
              <span class="showTagTips" v-if="selectTags.length==0">请选择标签</span>
              <div v-else>
                <a-tag v-for="(item,index) in selectTags" :key="index">{{ item.name }}</a-tag>
              </div>
            </div>
          </div>
          <selectTags @onChange="acceptData" ref="popupRef" />
          <div class="">
            <span>搜索规则：</span>
            <a-input-search
              placeholder="请输入规则名称搜索"
              style="width: 200px"
              allow-clear
              v-model="searchRuleData"
              @search="searchRuleName"
              @change="emptyRule"
            />
          </div>
        </div>
      </div>
      <div class="rule-list">
        <a-table :columns="table.col" :data-source="table.data">
          <div slot="tags" slot-scope="text">
            <a-tag v-for="(item,index) in text" :key="index">{{ item }}</a-tag>
          </div>
          <div slot="on_off" slot-scope="text,record">
            <a-switch
              size="small"
              :defaultChecked="text==1"
              @change="openSwitch($event,record)"
            />
            <span v-if="text==1">已开启</span>
            <span v-else>已关闭</span>
          </div>
          <div slot="operate" slot-scope="text,record">
            <span>
              <a @click="$router.push('/autoTag/joinRoomShow?idRow='+record.id)">详情</a>
              <a-divider type="vertical" />
              <a @click="deltableRow(record)">删除</a>
            </span>
          </div>
        </a-table>
      </div>
    </div>
  </a-card>
</template>

<script>
import { indexApi, destroyApi, onOffApi } from '@/api/autoTag'
import selectTags from '@/components/addlabel/selectTags'
export default {
  components: { selectTags },
  data () {
    return {
      // 选择的标签
      selectTags: [],
      //  选中标签请求数组
      tagsParamArr: [],
      //  表格类型
      typeTable: 2,
      //  搜索规则
      searchRuleData: '',
      table: {
        col: [
          {
            key: 'name',
            dataIndex: 'name',
            title: '规则名称'
          },
          {
            key: 'mark_tag_count',
            dataIndex: 'mark_tag_count',
            title: '已打标签数'
          },

          {
            key: 'tags',
            dataIndex: 'tags',
            title: '添加的标签',
            scopedSlots: { customRender: 'tags' }
          },
          {
            key: 'created_at',
            dataIndex: 'created_at',
            title: '创建时间'
          },
          {
            key: 'on_off',
            dataIndex: 'on_off',
            title: '规则状态',
            scopedSlots: { customRender: 'on_off' }
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
    this.getTableData({
      type: this.typeTable
    })
  },
  methods: {
    // 更改规则开关状态
    openSwitch (e, record) {
      if (e) {
        record.on_off = 1
      } else {
        record.on_off = 2
      }
      onOffApi({
        id: record.id,
        on_off: record.on_off
      }).then((res) => {})
    },
    //  显示弹窗
    showModel () {
      this.$refs.popupRef.show(this.selectTags)
    },
    //  接收子组件传来的数据
    acceptData (e) {
      this.tagsParamArr = []
      e.forEach((item, index) => {
        this.tagsParamArr[index] = item.id
      })
      this.selectTags = e
      this.getTableData({
        type: this.typeTable,
        name: this.searchRuleData,
        tags: this.tagsParamArr
      })
    },
    //  删除表格数据行
    deltableRow (record) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          const indexRow = that.table.data.indexOf(record)
          destroyApi({
            id: record.id
          }).then((res) => {
            that.$message.success('删除成功')
            that.table.data.splice(indexRow, 1)
          })
        }
      })
    },
    //  搜索框事件
    searchRuleName () {
      this.getTableData({
        type: this.typeTable,
        name: this.searchRuleData,
        tags: this.tagsParamArr
      })
    },
    //  清空输入框
    emptyRule (e) {
      if (this.searchRuleData == '') {
        this.getTableData({
          type: this.typeTable
        })
      }
    },
    //  获取表格数据
    getTableData (paramData) {
      indexApi(paramData).then((res) => {
        this.table.data = res.data.list
      })
    }
  }
}
</script>

<style lang="less" scoped>
.showTagsBox{
  border: 1px solid #D9D9D9;
  width: 200px;
  cursor: pointer;
  border-radius: 2px;
  padding-left: 5px;
}
.showTagTips{
  color: #BFBFBF;
}
.page_top{
  display: flex;
  justify-content:space-between;
}
.rule-list{
  margin-top: 15px;
}
.search_term{
  display: flex;
}
.choice_tags{
  display: flex;
  line-height: 32px;
  margin-right: 15px;
}
</style>
