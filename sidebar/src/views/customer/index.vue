<template>
  <div class="page">
    <div class="header">
      <van-image
        class="img"
        :src="infoDetail.avatar"
      />
      <div class="detail">
        <div class="name-content">
          <span class="name">{{ infoDetail.name }}</span>
          <!-- <span class="status">跟进中</span> -->
        </div>
        <div class="phone">
          手机：暂无
        </div>
        <div class="local">
          地区：暂无
        </div>
      </div>
    </div>
    <!-- 备注 -->
    <div class="remark">
      <van-cell class="cell" is-link to="/customer/remark" >
        <template #title>
          <span class="title">备注</span>
          <span type="name">{{ infoDetail.remark }}</span>
        </template>
      </van-cell>
    </div>

    <div class="group-wrapper cell">
      <div class="title">所在群</div>
      <div type="name">
        {{ roomName }}
      </div>
    </div>
    <div class="tag-wrapper">
      <van-cell class="cell" is-link to="/customer/settingTag" >
        <template #title>
          <div class="title">标签</div>
          <div type="name">
            <van-tag type="primary" class="tag" v-for="(item, index) in infoDetail.tag" :key="index">{{ item.tagName }}</van-tag>
          </div>
        </template>
      </van-cell>
    </div>

    <div class="rate-wrapper" v-if="false">
      <div class="rate-left">
        <div class="rate"></div>
        <div class="text">预计成交率</div>
      </div>
      <div class="rate-right">
        <div class="rate"></div>
        <div class="text">跟进记录</div>
      </div>
    </div>

    <div class="customer">
      <!-- <van-sticky :offset-top="50"> -->
      <van-tabs
        v-model:active="activeName"
        color="#4477bc"
        :sticky="true"
        title-active-color="#000000"
        title-inactive-color="#999999">
        <van-tab v-if="false" title="跟进记录" name="1">
          <van-list
            class="list"
            v-model:loading="recordLoading"
            :finished="recordFinished"
            finished-text="没有更多了"
            @load="recordOnLoad"
          >
            <van-cell
              class="cell list-item"
              v-for="(item, index) in recordList"
              :key="index"
              is-link>
              <template #title>
                <div>
                  <van-icon name="underway-o" />
                  <span class="time">2020-09-8</span>
                </div>
                <div class="name-status"></div>
                <div class="content"></div>
              </template>
            </van-cell>
          </van-list>
        </van-tab>
        <van-tab title="互动轨迹" name="2">
          <van-list
            class="list"
            v-model:loading="trackLoading"
            :finished="trackFinished"
            finished-text="没有更多了"
            @load="trackOnLoad"
          >
            <van-cell
              class="cell list-item"
              v-for="(item, index) in trackList"
              :key="index">
              <template #title>
                <div class="time-wrapper">
                  <van-icon name="underway-o" />
                  <span class="time">{{ item.createdAt }}</span>
                </div>
                <div class="content track-content">
                  {{ item.content }}
                </div>
              </template>
            </van-cell>
          </van-list>
        </van-tab>
        <van-tab
          title="客户画像"
          name="3"
          class="list">
          <van-cell-group class="data-group">
            <div
              v-for="(item, index) in detail"
              :key="index"
            >
              <van-field
                v-if="item.typeText !== '多选' && item.typeText !== '图片' && item.typeText !== '单选'"
                :label="item.name"
                v-model="item.value"
                readonly
                placeholder="暂无" />
              <div v-if="item.typeText == '图片'" class="picture-wrapper">
                <span class="title">{{ item.name }}</span>
                <img
                  v-if="item.pictureFlag"
                  class="picture"
                  :src="item.pictureFlag"
                  alt="">
                <span v-else class="text">暂无</span>
              </div>
              <div v-if="item.typeText == '单选'" class="check-wrapper">
                <span class="title">{{ item.name }}</span>
                <van-radio-group v-if="item.value" v-model="item.value" direction="horizontal" class="check-group">
                  <van-radio
                    :name="item.value"
                    icon-size="15px"
                    class="check-item"
                    disabled
                  >
                    <span class="text">{{ item.value }}</span>
                  </van-radio>
                </van-radio-group>
              </div>
              <div v-if="item.typeText == '多选'" class="check-wrapper">
                <span class="title">{{ item.name }}</span>
                <van-checkbox-group v-model="item.value" class="check-group">
                  <van-checkbox
                    v-for="(val, ind) in item.value"
                    :key="ind + val"
                    :name="val"
                    icon-size="15px"
                    class="check-item"
                    disabled>
                    <span class="text">{{ val }}</span>
                  </van-checkbox>
                </van-checkbox-group>
              </div>
            </div>
          </van-cell-group>
        </van-tab>
      </van-tabs>
      <!-- </van-sticky> -->
    </div>
    <div v-if="activeName == 3" class="edit-detail" @click="editDetail">
      <div>+</div>
      <div>编辑</div>
    </div>
  </div>
</template>

<script>
import { getWorkContactInfo, getUserPortrait, track } from '@/api/customer'
import { mapGetters } from 'vuex'

export default {
  data () {
    return {
      activeName: '1',
      roomName: '',
      infoDetail: {},
      recordLoading: false,
      recordFinished: true,
      recordList: [],
      trackLoading: false,
      trackFinished: true,
      trackList: [],
      detail: [],
      editPivotDis: false
    }
  },
  created () {
    if (!this.contactId) {
      this.$toast({ position: 'top', message: '请重新进入应用' })
      return
    }
    this.getInfo()
    this.getTrackData()
    this.getDetail()
  },
  computed: {
    ...mapGetters([
      'contactId',
      'userInfo'
    ])
  },
  methods: {
    getInfo () {
      const params = {
        contactId: this.contactId,
        employeeId: this.userInfo.employeeId
      }
      getWorkContactInfo(params).then(res => {
        this.infoDetail = res.data
        this.roomName = res.data.roomName && res.data.roomName.join(', ')
      })
    },
    getDetail () {
      getUserPortrait({ contactId: this.contactId }).then(res => {
        this.detail = res.data
      })
    },
    getTrackData () {
      track({ contactId: this.contactId }).then(res => {
        this.trackList = res.data
      })
    },
    editDetail () {
      this.$router.push({ path: '/customer/editDetail' })
    },
    addRemark () {

    },
    recordOnLoad () {

    },
    trackOnLoad () {

    }

  }
}
</script>

<style lang="less" scoped>
.header {
  padding-top: 24px;
  display: flex;
  .img{
    width:128px;
    height:128px;
    border-radius: 8px;
    overflow: hidden;
  }
  .detail {
    padding-left: 28px;
    .name-content {
      padding-bottom: 10px;
      .name {
        font-size: 36px;
        font-weight: 500;
        line-height: 50px;
      }
      .status {
        display: inline-block;
        padding-left: 11px;
        font-size: 26px;
        font-weight: 400;
        color: #5981b4;
        line-height: 37px;
      }
    }
    .phone,.local {
      font-size: 26px;
      font-weight: 400;
      color: #666666;
      line-height: 37px;
    }
  }
}

.remark {
  line-height: 110px;
  display: flex;
  align-items: center;
  .title {
    display: inline-block;
    width: 116px;
    margin-right: 35px;
  }
}

.group-wrapper {
  .title {
    flex: 0 0 116px
  }
  .name {
    flex: 1;
    line-height: 60px;
  }
}
.tag {
  height: 34px;
  margin-right: 20px;
  font-size: 26px;
  font-weight: 400;
  text-align: left;
  color: #5981b4;
  background: #eff5fe;
  border-radius: 17px;
  line-height: 37px;
}
.cell {
  min-height: 110px;
  padding: 0;
  font-size: 32px;
  display: flex;
  align-items: center;
}
.rate-wrapper {
  height: 140px;
  margin: 13px 0;
  display: flex;
  align-items: center;
  .rate-left,.rate-right {
    flex: 1;
  }
  .rate-left {
    flex: 0 0 40%;
    border-right: 3px solid #999999;
    border-radius: 0 10px 10px 0;
  }
  .rate-right {
    padding-left: 54px;
  }
  .rate,.text {
    text-align: left;
    color: #000000;
    line-height: 37px;
  }
  .rate {
    font-size: 32px;
    margin-bottom: 4px;
  }
  .text {
    font-size: 26px;
    font-weight: 400;
  }
}
.customer {
  .list-item {
    padding: 20px 0;
    display: flex;
    align-items: center;
    .name-status,.content {
      padding-left: 32px;
      font-size: 28px;
      font-weight: 400;
      line-height: 40px;
    }
    .time-wrapper {
      display: flex;
      align-items: center;
    }
    .time {
      font-size: 30px;
      font-weight: 400;
      color: #000000;
      line-height: 42px;
    }
    .name-status {
      color: #333333;
    }
    .content {
      color: #999999;
      word-break: break-word;
    }
    .track-content {
      font-size: 28px;
      background: #f2f3f7;
      line-height: 40px;
      margin-top: 10px;
    }
  }
}
.data-group {
  padding: 45px 0 30px 0;
}
.picture-wrapper,.check-wrapper {
  align-items: center;
  display: flex;
  padding: 20px 0 20px 32px;
  color: #646566;
  .title {
    width: 200px;
    font-size: 28px;
    display: inline-block;
  }
  .picture {
    width: 128px;
    height: 128px;
    margin-left: 20px;
  }
  .text {
    color:#cac8c8;
    font-size: 27px;
  }
  .check-group {
    .check-item {
      margin: 10px 0;
    }
  }
}
.edit-detail {
  position: fixed;
  bottom: 100px;
  right: 20px;
  border-radius: 50%;
  width: 90px;
  height: 90px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  background:  #1989fa;
  color: #fff;
  cursor: pointer;
}
</style>
