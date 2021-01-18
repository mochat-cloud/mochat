<template>
  <div>
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
        <span class="title">修改备注名</span>
      </template>
      <template #right>
        <span class="right">确定</span>
      </template>
    </van-nav-bar>
    <div class="wrapper">
      <div class="remark-title">
        备注名
      </div>
      <input class="input" maxlength="10" v-model.trim="remark" type="text" placeholder="添加备注">
    </div>
  </div>
</template>

<script>
import { editWorkContactInfo } from '@/api/customer'
import { mapGetters } from 'vuex'

export default {
  data () {
    return {
      remark: ''
    }
  },
  computed: {
    ...mapGetters([
      'contactId',
      'userInfo'
    ])
  },
  created () {

  },
  methods: {
    cancel () {
      this.$router.go(-1)
    },
    submit () {
      if (!this.remark) {
        this.$toast({ position: 'top', message: '请添加备注' })
        return
      }
      const employeeId = this.userInfo.employeeId
      editWorkContactInfo({
        contactId: this.contactId,
        remark: this.remark,
        employeeId
      }).then(res => {
        this.$router.go(-1)
      })
    }
  }
}
</script>

<style lang="less" scoped>
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
  // padding: 0 30px
  font-size: 34px;
  // color: #b2b2b2;
}
input::-webkit-input-placeholder,textarea::-webkit-input-placeholder{
color:#b2b2b2;
font-size:34px;
}

</style>
