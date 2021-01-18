<template>
  <div class="edit-detail">
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
        <span class="title">客户画像编辑</span>
      </template>
      <template #right>
        <span class="right">确定</span>
      </template>
    </van-nav-bar>
    <van-cell-group class="data-group">
      <div
        v-for="(item, index) in detail"
        :key="index"
      >
        <div v-if="item.typeText == '图片'" class="picture-wrapper">
          <span class="title">{{ item.name }}</span>
          <van-uploader
            multiple
            v-model="item.fileList"
            @click="upload(index)"
            @delete="imgChange($event, item)"
            :after-read="afterRead"
            :before-read="beforeRead"
            result-type="file"
            :max-count="1">
          </van-uploader>
        </div>
        <div v-else-if="item.typeText == '单选'" class="check-wrapper">
          <span class="title">{{ item.name }}</span>
          <van-radio-group v-model="item.value" class="check-group">
            <van-radio
              v-for="(val, ind) in item.options"
              :key="ind + val"
              :name="val"
              icon-size="15px"
              class="check-item"
            >
              <span class="check-text">{{ val }}</span>
            </van-radio>
          </van-radio-group>
        </div>
        <div v-else-if="item.typeText == '多选'" class="check-wrapper">
          <span class="title">{{ item.name }}</span>
          <van-checkbox-group v-model="item.value" class="check-group">
            <van-checkbox
              v-for="(val, ind) in item.options"
              :key="ind + val"
              :name="val"
              icon-size="15px"
              class="check-item"
            >
              <span class="check-text">{{ val }}</span>
            </van-checkbox>
          </van-checkbox-group>
        </div>
        <div v-else-if="item.typeText == '下拉'" class="check-wrapper">
          <span class="title">{{ item.name }}</span>
          <van-dropdown-menu active-color="#1989fa" class="dropdown-menu">
            <van-dropdown-item
              v-model="item.value"
              :options="item.selectOptions"
            />
          </van-dropdown-menu>
        </div>
        <van-field
          v-else
          :label="item.name"
          :placeholder="`请输入${item.name}`"
          v-model="item.value" />
      </div>
    </van-cell-group></div>
</template>

<script>
import { getUserPortrait, updateUserPortrait, upload } from '@/api/customer'
import { mapGetters } from 'vuex'

export default {
  data () {
    return {
      detail: [],
      imgIndex: ''
    }
  },
  created () {
    if (!this.contactId) {
      this.$toast({ position: 'top', message: '请重新进入应用' })
      return
    }
    this.getDetail()
  },
  computed: {
    ...mapGetters([
      'contactId'
    ])
  },
  methods: {
    getDetail () {
      getUserPortrait({ contactId: this.contactId }).then(res => {
        this.detail = res.data.map(item => {
          if (item.typeText == '图片') {
            if (item.pictureFlag) {
              item.fileList = [{
                url: item.pictureFlag
              }]
            }
          } else if (item.typeText == '下拉') {
            item.selectOptions = item.options.map(item => {
              return {
                value: item,
                text: item
              }
            })
          }
          return item
        })
      })
    },
    upload (index) {
      this.imgIndex = index
    },
    imgChange (data, item) {
      item.value = ''
    },
    beforeRead (data) {
      if (data instanceof Array) {
        this.$toast({ position: 'top', message: '最多选择一张图片' })
        return false
      } else {
        return true
      }
    },
    afterRead (data) {
      const params = new FormData()
      params.append('file', data.file)
      upload(params).then(res => {
        this.detail[this.imgIndex].fileList = [{ url: res.data.fullPath }]
        this.detail[this.imgIndex].value = res.data.path
      })
    },
    cancel () {
      this.$router.go(-1)
    },
    submit () {
      this.detail.map(item => {
        if (item.typeText == '多选') {
          item.value = item.value.filter(inner => {
            return inner
          })
        }
      })
      updateUserPortrait({
        contactId: this.contactId,
        userPortrait: JSON.stringify(this.detail)
      }).then(res => {
        this.$router.go(-1)
      })
    }
  }
}
</script>

<style lang="less" scoped>
.data-group {
  padding: 45px 0 30px 0
}
.picture-wrapper,.check-wrapper {
  align-items: center;
  display: flex;
  padding: 20px 0 20px 32px;
  .title {
    width: 200px;
    font-size: 26px;
    font-weight: 400;
    display: inline-block;
  }
  .check-text {
    font-size: 26px;
    font-weight: 400;
  }
  .picture {
    width: 128px;
    height: 128px;
    margin-left: 20px;
  }
  .check-group {
    .check-item {
      margin: 10px 0;
    }
  }
  .dropdown-menu {
    display: flex;
    flex: 1;
    align-items: center;
    margin-left: -20px;
    height: 80px;
  }
}
.upload {
  color: #1989fa;
}
</style>
