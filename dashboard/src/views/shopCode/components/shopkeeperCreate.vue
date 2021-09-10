<template>
  <div class="details">
    <a-modal
      v-model="modalShow"
      :width="767"
      :footer="false"
      centered
      :maskClosable="false"
    >
      <template slot="title">添加门店信息</template>
      <!--      门店店主-->
      <div class="shopkeeper elastic">
        <span v-if="addAskData.type==1">门店店主：</span>
        <span v-else>拉群活码：</span>
        <div v-if="addAskData.type==1">
          <a-select
            placeholder="请选择门店门主"
            :showSearch="true"
            @change="selectMember"
            style="width: 200px;"
            v-model="showMember.name"
          >
            <a-select-option
              v-for="(item,index) in memberArray"
              :key="index"
              :value="item.name"
            >{{ item.name }}
            </a-select-option>
          </a-select>
        </div>
        <div v-else>
          <a-select
            placeholder="请选择拉群活码"
            :showSearch="true"
            @change="selectMember"
            style="width: 200px;"
            v-model="showMember.name"
          >
            <a-select-option
              v-for="(item,index) in workRoomArray"
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
      </div>
      <!--      门店名称-->
      <div class="shop-name elastic">
        <span>
          门店名称：
        </span>
        <div class="name-input">
          <a-input placeholder="请输入门店名称" v-model="addAskData.name" :allowClear="true" style="width: 300px;" />
        </div>
      </div>
      <!--      门店地址-->
      <div class="shop-address">
        <span>门店地址：</span>
        <div class="maps">
          <div class="maps-top mb10">{{ mapCurrent.address }}</div>
          <div class="map-box">
            <div class="maps-bottom" ref="map"></div>
            <div class="search">
              <a-input-search
                placeholder="请输入地址"
                style="width: 330px;"
                v-model="searchAddress"
                @search="searchAddressChange"
              />
            </div>
            <div class="search-list" v-if="addressList.length">
              <div
                class="item mb6"
                v-for="(v,i) in addressList"
                :key="i"
                @click="selectClick(v)">
                {{ v.address }}
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--      状态-->
      <div class="state mb20 elastic">
        <span>开启状态：</span>
        <div class="switch-stat">
          <a-switch
            size="small"
            v-model="salesroomState"
            class="mr6"
          />
          <span v-if="salesroomState">开启</span>
          <span v-else>关闭</span>
        </div>
      </div>
      <div class="footer">
        <a-button class="mr16" @click="hide">取消</a-button>
        <a-button type="primary" @click="determine">确定</a-button>
      </div>
    </a-modal>
  </div>
</template>
<script>
import { location, addressKeyWordList, department, workRoomIndexApi } from '@/api/shopCode'
// eslint-disable-next-line no-redeclare,no-unused-vars

export default {
  data () {
    return {
      modalShow: false,
      map: false,
      markFlag: false,
      multiMarker: {},
      mapCurrent: {},
      searchAddress: '',
      addressList: [],
      //  员工数组
      memberArray: [],
      // 拉群活码
      workRoomArray: [],
      //  显示被选中的成员
      // 门店状态
      salesroomState: true,
      showMember: {},
      // 添加请求数据
      addAskData: {
        type: 1,
        // 名称
        name: '',
        //  店主
        employee: '',
        // 地址
        address: '',
        //  省
        province: '',
        // 城市
        city: '',
        // 拉群活码
        qwCode: {},
        //  地区
        district: '',
        // 纬度
        lat: '',
        // 经度
        lng: '',
        //  搜索地址
        shop_address: ''
      }
    }
  },
  methods: {
    show (type) {
      this.addAskData = {}
      this.modalShow = true
      this.addAskData.type = type
      this.addNumberData(type)
      setTimeout(() => {
        this.initMap()
      }, 100)
    },
    hide () {
      this.modalShow = false
      this.addAskData = {}
      this.addAskData.type = 1
      this.showMember = {}
      this.salesroomState = true
      this.searchAddress = ''
      this.searchAddressChange()
    },
    // 确定按钮
    determine () {
      // abc
      this.addAskData.province = this.mapCurrent.province
      this.addAskData.city = this.mapCurrent.city
      this.addAskData.district = this.mapCurrent.district
      this.addAskData.address = this.mapCurrent.address
      this.addAskData.lat = this.mapCurrent.location.lat
      this.addAskData.lng = this.mapCurrent.location.lng
      if (this.addAskData.type == 1 && (this.addAskData.employee == '' || this.addAskData.employee == undefined)) {
        this.$message.error('请选择门店店主')
        return false
      }
      console.log(this.addAskData.qwCode)
      if (this.addAskData.type != 1 && (this.addAskData.qwCode == '' || this.addAskData.qwCode == undefined)) {
        this.$message.error('请选择拉群活码')
        return false
      }
      if (this.addAskData.name == '' || this.addAskData.name == undefined) {
        this.$message.error('门店名称不能为空')
        return false
      }
      if (this.salesroomState) {
        this.addAskData.status = 1
      } else {
        this.addAskData.status = 0
      }
      this.$emit('change', this.addAskData)
      // 重置
      this.hide()
    },
    addNumberData (type) {
      // 获取员工数据
      if (type == 1) {
        department().then((res) => {
          this.memberArray = res.data.employee
        })
      } else {
        //  获取拉群活码
        workRoomIndexApi({
          page: 1,
          perPage: 10000
        }).then((res) => {
          this.workRoomArray = res.data.list
        })
      }
    },
    // 选中成员
    selectMember (e) {
      if (this.addAskData.type == 1) {
        this.memberArray.forEach((item) => {
          if (item.name == e) {
            this.addAskData.employee = item
          }
        })
      } else {
        const shopkeeperData = {}
        this.workRoomArray.forEach((item) => {
          if (item.workRoomAutoPullId == e) {
            shopkeeperData.workRoomAutoPullId = item.workRoomAutoPullId
            shopkeeperData.qrcodeUrl = item.qrcodeUrl
          }
        })
        this.addAskData.qwCode = shopkeeperData
      }
    },
    /**
     * 选择地址
     * @param data
     */
    selectClick (data) {
      this.mapCurrent = data
      this.move(data.location.lat, data.location.lng)
      this.addressList = []
    },

    /**
     * 搜索地址
     * @returns {boolean}
     */
    searchAddressChange () {
      if (!this.searchAddress) {
        const initLat = 39.984120
        const initLng = 116.307484
        this.move(initLat, initLng)
        this.mark(initLat, initLng)
        this.sendAnalysis(initLat, initLng)
        return false
      }
      this.addAskData.searchKeyword = this.searchAddress
      addressKeyWordList({
        keyword: this.searchAddress
      }).then(res => {
        this.addressList = res.data.data
      })
    },

    /**
     * 移动地图当前坐标
     * @param lat
     * @param lng
     */
    move (lat, lng) {
      // eslint-disable-next-line no-undef
      this.map.setCenter(new TMap.LatLng(lat, lng))
      this.mark(lat, lng)
    },

    /**
     * 地图打标点图标
     * @param lat
     * @param lng
     */
    mark (lat, lng) {
      if (this.markFlag) {
        this.multiMarker.setGeometries([])

        this.markFlag = false
      }

      // eslint-disable-next-line no-undef
      this.multiMarker = new TMap.MultiMarker({
        map: this.map,
        geometries: [
          {
            'id': '1',
            'styleId': 'marker',
            // eslint-disable-next-line no-undef
            'position': new TMap.LatLng(lat, lng),
            'properties': {
              'title': 'marker2'
            }
          }
        ]
      })

      this.markFlag = -true
    },

    /**
     * 初始化地图
     */
    initMap () {
      if (!this.map) {
        const initLat = 39.984120
        const initLng = 116.307484

        setTimeout(() => {
          // eslint-disable-next-line no-undef
          const center = new TMap.LatLng(initLat, initLng)

          // eslint-disable-next-line no-undef
          this.map = new TMap.Map(this.$refs.map, {
            center: center,
            zoom: 17.2,
            pitch: 43.5,
            rotation: 45
          })

          this.mark(initLat, initLng)
          this.sendAnalysis(initLat, initLng)

          this.map.on('click', evt => {
            const lat = evt.latLng.getLat().toFixed(6)
            const lng = evt.latLng.getLng().toFixed(6)

            this.sendAnalysis(lat, lng)
            this.mark(lat, lng)
          })
        }, 100)
      }
    },

    /**
     * 地址解析
     * @param lat
     * @param lng
     */
    sendAnalysis (lat, lng) {
      location({
        lat,
        lng
      }).then(res => {
        this.mapCurrent = {
          ...res.data.result.ad_info,
          address: res.data.result.address
        }
      })
    }
  }
}
</script>

<style lang="less" scoped>
/deep/ .ant-modal-title {
  text-align: center;
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 1px;
}

/deep/ .ant-modal-body {
  height: 577px;
  overflow: auto;
}

.map-box {
  position: relative;

  .search {
    position: absolute;
    top: 15px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 100000;
  }

  .search-list {
    width: 330px;
    position: absolute;
    top: 48px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 100000;
    border: 1px solid #d1d1d1;
    background: #fff;
    font-size: 13px;
    padding: 5px 0;

    .item {
      padding: 0 8px;
      cursor: pointer;

      &:hover {
        background: #ccc;
      }
    }
  }
}

.elastic {
  margin-top: 14px;
  display: flex;
  align-items: center;
}

.shop-address {
  display: flex;
  margin-top: 14px;
}

.maps-bottom {
  width: 540px;
  height: 260px;
  background-color: #8d8d8d;
}
</style>
