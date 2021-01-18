<template>
  <div class="setting-tag-wrapper">
    <van-nav-bar
      fixed
      :border="true"
      left-arrow
      safe-area-inset-top
      @click-left="cancel"
      @click-right="submit"
      placeholder >
      <template #left>
        <div class="left">
          <van-icon name="arrow-left" />
          取消
        </div>
      </template>
      <template #title>
        <span class="title">设置标签</span>
      </template>
      <template #right>
        <span class="right">确定</span>
      </template>
    </van-nav-bar>
    <div class="wrapper">
      <van-dropdown-menu active-color="#1989fa" class="dropdown-menu">
        <van-dropdown-item v-model="groupId" :options="groupOptions" @change="groupChange"/>
      </van-dropdown-menu>
    </div>
    <div class="select-wrapper">
      <div class="tag-content">
        <div class="tag choose" v-for="(item, index) in selectedTag" :key="index">
          {{ item.name }}
          <van-icon name="cross" v-if="!oldSelected.includes(item.id)" @click="delTag(item.id, index)" />
        </div>
      </div>
    </div>
    <div class="tags-wrapper">
      <div class="tag-group">所有标签</div>
      <div class="tag-content">
        <div :class="['tag', selectedId.indexOf(item.id) > -1 ? 'choose' : '']" v-for="(item, index) in tags" :key="index" @click="chooseTag(item)">
          {{ item.name }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { editWorkContactInfo, allTag, workContactTagGroup, getWorkContactInfo } from '@/api/customer'
import { mapGetters } from 'vuex'

export default {
  data () {
    return {
      groupId: '',
      groupOptions: [],
      tags: [{
        id: '1', // 标签id
        name: '优质客户' // 标签名称
      }, {
        id: '2', // 标签id
        name: '优youzhi湛江老街拉开距离框架质客户' // 标签名称
      }, {
        id: '3', // 标签id
        name: '优质额额特然客户' // 标签名称
      }, {
        id: '4', // 标签id
        name: '优质儿童热客户' // 标签名称
      }, {
        id: '5', // 标签id
        name: '优质儿童热客户' // 标签名称
      }],
      selectedTag: [],
      selectedId: [],
      oldSelected: []
    }
  },
  computed: {
    ...mapGetters([
      'contactId',
      'userInfo'
    ])
  },
  created () {
    if (!this.contactId) {
      this.$toast({ position: 'top', message: '请重新进入应用' })
      return
    }
    this.getAllGroup()
    this.getAllTag()
    this.getInfo()
  },
  methods: {
    getInfo () {
      const employeeId = this.userInfo.employeeId
      getWorkContactInfo({ contactId: this.contactId, employeeId }).then(res => {
        this.selectedTag = res.data.tag && res.data.tag.map(item => {
          this.selectedId.push(item.tagId)
          return {
            name: item.tagName,
            id: item.tagId
          }
        })
        this.oldSelected = [...this.selectedId]
      })
    },
    cancel () {
      this.$router.go(-1)
    },
    submit () {
      const employeeId = this.userInfo.employeeId
      editWorkContactInfo({
        contactId: this.contactId,
        tag: this.selectedId,
        employeeId
      }).then(res => {
        this.$router.go(-1)
      })
    },
    getAllGroup () {
      workContactTagGroup().then(res => {
        this.groupOptions = res.data.map(item => {
          return {
            text: item.groupName,
            value: item.groupId
          }
        })
        this.groupOptions.unshift({ text: '所有分组', value: '' })
      })
    },
    getAllTag () {
      allTag({ groupId: this.groupId }).then(res => {
        this.tags = res.data
      })
    },
    chooseTag (item) {
      if (this.selectedId.includes(item.id)) {
        return
      }
      this.selectedTag.push(item)
      this.selectedId.push(item.id)
    },
    delTag (id, index) {
      this.selectedTag.splice(index, 1)
      this.selectedId.splice(index, 1)
    },
    groupChange (value) {
      this.getAllTag()
    }
  }
}
</script>

<style lang="less" scoped>
.setting-tag-wrapper {
  font-size: 28px;
}
.left,.title,.right {
  font-size: 34px;
  font-family: PingFangSC, PingFangSC-Medium;
  font-weight: 400;
  text-align: center;
  line-height: 48px;
  display: flex;
  align-items: center;
  color: #000000;
}
.title {
  font-weight: 500;
}
.input {
  border: 1px solid #fff;
  width: 100%;
  height: 88px;
  padding-left: 30px;
}
.remark-title {
  height: 88px;
  font-size: 28px;
  font-family: PingFangSC, PingFangSC-Regular;
  font-weight: 400;
  text-align: left;
  color: #b2b2b2;
  line-height: 88px;
  padding-left: 30px;
}
.wrapper {
  display: flex;
  justify-content: space-between;
  .dropdown-menu {

  }
}
.select-wrapper {
  background: #fff;
  min-height: 100px;
}
.tags-wrapper {
   padding-left: 25px;
   margin: 20px 0 20px 0;
  .tag-group {
    font-size: 28px;
    font-weight: 400;
    color: #b2b2b2;
    line-height: 40px;

  }
}
.tag-content {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  margin-top: 20px;
  .tag {
    display: flex;
    // flex: 0 0 21%;
    // height: 60px;
    padding: 10px;
    background: #e4e4e7;
    border-radius: 33px;
    justify-content: center;
    align-items: center;
    text-align: center;
    margin-right: 15px;
    margin-bottom: 23px;
    color: #666666;
    cursor: pointer;
  }
  .choose {
    background: #dfe7f5;
    color: #5981b4;
  }
}

</style>
