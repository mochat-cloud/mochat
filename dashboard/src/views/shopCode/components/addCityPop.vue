<template>
  <div>
    <a-modal v-model="addToCityPopup" title="添加城市" :maskClosable="false" :footer="null" width="670px">
      <div class="add_city_tips">配置过程较为复杂，可<a href="">联系客服</a>提供人工配置服务</div>
      <div class="add_city_row">
        <div class="title_name">城市：</div>
        <!--        -->
        <VDistpicker
          ref="VDistpicker"
          hide-area
          @province="selectProvince"
          :province="province"
          :city="city"
          @city="selectCity"/>
      </div>
      <div class="add_city_row">
        <div class="title_name">拉群活码：</div>
        <a-select
          placeholder="请选择拉群活码"
          :showSearch="true"
          @change="selectWorkRoom"
          style="width: 200px;"
          v-model="showRooms.workRoomAutoPullId"
        >
          <a-select-option
            v-for="(item,index) in workRoomsArray"
            :key="index"
            :label="item.qrcodeName"
            :value="item.workRoomAutoPullId"
          >
            <div class="work_room">
              <img :src="item.qrcodeUrl" alt="">
              <div>{{ item.qrcodeName }}</div>
            </div>
          </a-select-option>
        </a-select>
      </div>
      <div class="add_city_row">
        <div class="title_name">开启状态：</div>
        <div style="margin-top: 6px;">
          <a-switch default-checked v-model="cityStatus" size="small" style="margin-right: 10px;" />
          <span v-if="cityStatus">开启</span>
          <span v-else>关闭</span>
        </div>
      </div>
      <div class="add_city_foot">
        <a-button class="foot_btn" @click="closeBtn">取消</a-button>
        <a-button class="foot_btn" type="primary" @click="submitBtn">确定</a-button>
      </div>
    </a-modal>
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { workRoomIndexApi } from '@/api/shopCode'
import VDistpicker from '@/components/VDistpicker'
export default {
  components: { VDistpicker },
  data () {
    return {
      province: '',
      city: '',
      // 添加城市弹窗
      addToCityPopup: false,
      // 拉群活码
      workRoomsArray: [],
      showRooms: {},
      cityStatus: true,
      // 请求数据
      askCityData: {
        type: 3,
        province: '',
        city: '',
        qwCode: {},
        status: 0
      }

    }
  },
  watch: {
  },
  methods: {
    show (record) {
      this.askCityData = {}
      this.addToCityPopup = true
      this.askCityData.qwCode = {}
      this.askCityData.type = 3
      workRoomIndexApi({
        page: 1,
        perPage: 10000
      }).then((res) => {
        this.workRoomsArray = res.data.list
        if (record) {
          // this.$nextTick(() => {
          this.$refs.VDistpicker.setCityData(record.province, record.city)
          // })
          this.province = record.province
          this.city = record.city
          this.askCityData.qwCode = record.qwCode
          this.askCityData.id = record.id
          this.showRooms = JSON.parse(JSON.stringify(record.qwCode))
          if (record.status == 1) {
            this.cityStatus = true
          } else {
            this.cityStatus = false
          }
        }
      })
    },
    // 确定
    submitBtn () {
      if (this.askCityData.province == '' || this.askCityData.province == '省' || this.askCityData.province == undefined) {
        this.$message.warning('请选择省份')
        return false
      }
      // 市
      if (this.askCityData.city == '' || this.askCityData.city == '市' || this.askCityData.city == undefined) {
        this.$message.warning('请选择城市')
        return false
      }
      //  qwCode
      if (JSON.stringify(this.askCityData.qwCode) == '{}') {
        this.$message.warning('拉群活码不能为空')
        return false
      }
      if (this.cityStatus) {
        this.askCityData.status = 1
      } else {
        this.askCityData.status = 0
      }
      console.log(this.askCityData)
      // 清空弹窗数据
      this.closeBtn()
      // this.$refs.VDistpicker.resetCityData()
      this.$emit('change', this.askCityData)
    },
    // 取消
    closeBtn () {
      this.addToCityPopup = false
      this.$refs.VDistpicker.resetCityData()
      // this.province = '省'
      // this.city = '市'
      this.cityStatus = true
      this.showRooms = {}
    },
    // 选择省
    selectProvince (e) {
      if (e.value != '省') {
        this.askCityData.province = e.value
      }
    },
    selectCity (e) {
      if (e.value != '市') {
        this.askCityData.city = e.value
      }
    },
    selectWorkRoom (e) {
      const shopkeeperData = {}
      this.workRoomsArray.forEach((item) => {
        if (item.workRoomAutoPullId == e) {
          shopkeeperData.workRoomAutoPullId = item.workRoomAutoPullId
          shopkeeperData.qrcodeUrl = item.qrcodeUrl
        }
      })
      this.askCityData.qwCode = shopkeeperData
    }
  }
}
</script>
<style lang="less">
.add_city_tips{
  margin-bottom: 16px;
  width: 100%;
  padding: 7px 12px;
  background: #effaff;
  border-radius: 2px;
}
.add_city_row{
  display: flex;
  margin-top: 15px;
  .title_name{
    width: 110px;
    text-align: right;
    line-height: 34px;
  }
}
.add_city_row .distpicker-address-wrapper select{
  height: 34px;
  padding: 1px 7px;
  width: 150px !important;
  font-size: 14px;
  color: #595959;
}
.add_city_row .distpicker-address-wrapper select:focus-visible{
  border: 1px solid rgba(0,0,0,0.15) !important;
}
.add_city_foot{
  display: flex;
  margin-top: 5px;
  margin-bottom: 5px;
  justify-content:flex-end;
  .foot_btn{
    margin-left: 35px;
  }
}
</style>
