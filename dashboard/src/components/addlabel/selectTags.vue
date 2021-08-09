<template>
  <div class="tagselectPopup">
    <a-modal
      v-model="showTagsPopup"
      title="选择标签"
      :maskClosable="false"
      :footer="null"
    >
      <a-input-search
        placeholder="请输入企业标签"
        allowClear
        v-model="seekClientTags"
        @search="seekTags"
        @change="emptyInput"
      />
      <div class="tagsGroup" v-for="(item,index) in groupsData" :key="index" v-if="groupsData.length!=0">
        <div class="groupTitle">{{ item.groupName }}</div>
        <div class="groupRow">
          <a-tag
            @click="addTagsContent(obj)"
            v-for="(obj,idx) in item.tags"
            :key="idx"
            :class="getActiveClass(obj)"
          >
            {{ obj.name }}
          </a-tag>
        </div>
      </div>
      <div class="emptyTips" v-show="groupsData.length==0">
        <Empty />
      </div>
      <div class="footRowBtns">
        <a-button class="footRow_btn" @click="closePopup">取消</a-button>
        <a-button type="primary" class="footRow_btn" @click="castArray">保存</a-button>
      </div>
    </a-modal>
  </div>
</template>
<script>
import { Empty } from 'ant-design-vue'
import { clientTagsReceive } from '@/api/roomClockIn'
export default {
  components: { Empty },
  beforeCreate () {
    this.simpleImage = Empty.PRESENTED_IMAGE_SIMPLE
  },
  // eslint-disable-next-line vue/require-prop-types
  props: ['controlPopup'],
  data () {
    return {
      //  显示弹窗
      showTagsPopup: false,
      seekClientTags: '',
      //  标签组
      groupsData: [],
      //  选中的标签数组
      selectTagsArray: []
    }
  },
  watch: {
  },
  created () {
    this.getTagsData()
  },
  methods: {
    // 添加标签
    addTagsContent (obj) {
      const tagId = []
      this.selectTagsArray.forEach((item, index) => {
        tagId[index] = item.id
      })
      const lableIndex = tagId.indexOf(obj.id)
      if (lableIndex == -1) {
        this.selectTagsArray.push(obj)
      } else {
        this.selectTagsArray.splice(lableIndex, 1)
      }
    },
    getActiveClass (data) {
      for (const tag of this.selectTagsArray) {
        if (tag.id === data.id) {
          return {
            selectTagsStyle: true
          }
        }
      }
      return {
        selectTagsStyle: false
      }
    },
    // 显示弹窗
    show (data) {
      this.showTagsPopup = true
      this.selectTagsArray = []
      data.forEach((item, index) => {
        this.selectTagsArray[index] = item
      })
    },
    // 关闭弹窗
    closePopup () {
      this.showTagsPopup = false
    },
    // 获取后台标签
    getTagsData (params) {
      clientTagsReceive(params).then((res) => {
        this.groupsData = res.data
      })
    },
    // 搜索
    seekTags () {
      this.getTagsData({
        name: this.seekClientTags
      })
    },
    // 清空
    emptyInput () {
      if (this.seekClientTags == '') {
        this.getTagsData()
      }
    },
    // 抛出选中的内容
    castArray () {
      this.showTagsPopup = false
      this.$emit('onChange', this.selectTagsArray)
    }
  }
}
</script>

<style>
.ant-modal-header{
  border: 0;
}
.ant-modal-title{
  text-align: center;
  font-size: 17px;
  font-weight: 600;
}
.ant-modal-body{
  padding: 2px 24px 24px 24px;
}
.footRowBtns{
  margin-top: 10px;
  display: flex;
  justify-content:flex-end;
}
.tagsGroup{
  margin-top: 10px;
}
.groupTitle{
  font-size: 13px;
  color: #999;
  white-space: nowrap;
}
.groupRow span{
  margin-top: 10px;
  height: 28px;
  line-height: 15px;
  padding: 5px 14px;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
}
.selectTagsStyle{
  color: #1890ff;
  background: #e7f7ff;
  border: 1px solid #1890ff;
}
.emptyTips{
  margin-top: 20px;
}
.footRow_btn{
  margin-left: 15px;
}
.footRow_btn:first-child{
  margin-left: 0;
}
</style>
