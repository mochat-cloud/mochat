<template>
  <div class="box" style="position: relative;">
    <a-spin size="large" :spinning="loadShow">
      <div class="switch_account">
        公众号：<a-select style="width: 150px" v-model="officialAccount">
          <a-select-option :value="item.nickname" v-for="(item,index) in publiclist" :key="index">
            {{ item.nickname }}
          </a-select-option>
        </a-select>
      </div>
      <a-tabs v-model="pageType" @change="selectPageType">
        <a-tab-pane :key="1" tab="扫码添加店主"></a-tab-pane>
        <a-tab-pane :key="2" tab="扫码加入门店群"></a-tab-pane>
        <a-tab-pane :key="3" tab="扫码加入城市群"></a-tab-pane>
      </a-tabs>
      <a-card>
        <a-tabs type="card" v-model="showpanel" @change="cutPageType">
          <!--          门店列表-->
          <a-tab-pane :key="1" tab="门店列表">
            <!--          搜索-->
            <div class="search">
              <!--            门店名称-->
              <div class="shop-name mb16" v-if="pageType!=3">
                门店名称：
                <a-input-search
                  placeholder="请输入门店名称"
                  style="width: 200px"
                  v-model="askStoreData.name"
                  @search="retrievalTable"
                  :allowClear="true"
                  @change="emptyNickIpt"
                />
              </div>
              <!--      城市      -->
              <div class="city ml30">
                城市：
                <a-select style="width: 120px" @change="handleProvinceChange" placeholder="请选择省份" v-model="selectProvince">
                  <a-select-option v-for="(item,index) in cityArray" :key="index" :value="index"> {{ item.province }}</a-select-option>
                </a-select>
                <a-select
                  style="width: 120px;margin-left: 20px;"
                  placeholder="请选择城市"
                  v-model="selectCity"
                  @change="choiceCity"
                >
                  <a-select-option v-for="(item,index) in citySelect" :key="index" :value="index">
                    {{ item.city }}
                  </a-select-option>
                </a-select>
              </div>
              <!--            门店店长-->
              <div class="shopkeeper ml30" v-if="pageType==1">
                门店店长：
                <a-select
                  placeholder="请输入活码名称"
                  :showSearch="true"
                  @change="selectStore"
                  style="width: 200px;"
                  v-model="optCityData.shop"
                >
                  <a-select-option
                    v-for="(item,index) in staffArray"
                    :key="index"
                    :value="item.name"
                  >{{ item.name }}
                  </a-select-option>
                </a-select>
              </div>
              <!--              门店活码-->
              <div class="shop-name mb16" v-if="pageType!=1" style="margin-left: 30px;">
                门店活码：
                <a-input-search
                  placeholder="请输入活码名称"
                  style="width: 200px"
                  v-model="askStoreData.qw_code"
                  @search="searchQwCode"
                  :allowClear="true"
                  @change="emptyQwCode"
                />
              </div>
              <!--            开启状态-->
              <div class="on-state ml30">
                开启状态：
                <a-select
                  style="width: 200px"
                  placeholder="请选择开启状态"
                  v-model="askStoreData.status"
                  @change="screenSwitchState"
                >
                  <a-select-option :value="0">未开启</a-select-option>
                  <a-select-option :value="1">已开启</a-select-option>
                </a-select>
              </div>
              <div class="reset">
                <a-button @click="resetBtn">重置</a-button>
              </div>
            </div>
            <!--          表格-->
            <div class="shop-table mt20">
              <div class="title mb20">
                <span class="in-shop">
                  共{{ storeList.data.length }}个门店
                </span>
                <div class="button">
                  <a-button type="primary" ghost @click="$refs.shopkeeperCreate.show(pageType)" v-if="pageType!=3"><a-icon type="plus" />添加门店</a-button>
                  <a-button type="primary" ghost @click="$refs.addCityPop.show()" v-else><a-icon type="plus" />添加城市</a-button>
                  <a-button type="primary" ghost class="ml10" @click="$refs.setPopup.show(pageType)"><a-icon type="setting" />设置</a-button>
                  <a-button type="primary" ghost class="ml10" @click="shareBtn"><a-icon type="share-alt" />分享</a-button>
                </div>
              </div>
              <addCityPop ref="addCityPop" @change="receiveCityData" />
              <!--              设置弹窗-->
              <shopkeeperSetup ref="setPopup" />
              <!--  分享弹窗-->
              <a-modal v-model="visible" title="分享" :maskClosable="false" :footer="null">
                <div class="modeRow">
                  <div class="modeOne">
                    <div class="modeTitle">方式一：</div>
                    <div class="modeTips">页面二维码</div>
                    <div class="qr-code" ref="qrCode"></div>
                  </div>
                  <div class="separate"></div>
                  <div class="modeTwo">
                    <div class="modeTitle">方式二：</div>
                    <div class="modeTips">页面链接</div>
                    <div class="linkBox">{{ clockInLink }}</div>
                  </div>
                </div>
                <div class="footRow">
                  <a-button class="footBtn" @click="downQrcode">下载二维码</a-button>
                  <a-button class="footBtn" type="primary" @click="copyLink">复制页面链接</a-button>
                </div>
              </a-modal>
              <!--        表格-->
              <div class="table-box">
                <a-table :columns="storeList.col[pageType-1]" :data-source="storeList.data">
                  <div slot="employee" slot-scope="text,record">
                    <a-select
                      :showSearch="true"
                      @change="selectStaff($event,record)"
                      style="width: 200px;"
                      :value="text.name"
                    >
                      <a-select-option
                        v-for="(item,index) in staffArray"
                        :key="index"
                        :value="item.name"
                      >{{ item.name }}
                      </a-select-option>
                    </a-select>
                  </div>
                  <div slot="qwCode" slot-scope="text,record">
                    <a-select
                      :showSearch="true"
                      style="width: 200px;"
                      option-label-prop="label"
                      :value="text.workRoomAutoPullId"
                      @change="modifyCode($event,record)"
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
                  <div slot="status" slot-scope="text,record">
                    <a-switch :checked="text == 1" size="small" @change="openShopState(record)" />
                    <span class="ml4" v-if="text==1">已开启</span>
                    <span class="ml4" v-else>已关闭</span>
                  </div>
                  <div slot="operate" style="width: 80px;" slot-scope="text,record">
                    <a href="#" @click="$refs.shopkeeperUpdate.show(record,pageType)" v-if="pageType!=3">修改</a>
                    <a href="#" @click="$refs.addCityPop.show(record)" v-else>修改</a>
                    <a-divider type="vertical" />
                    <a @click="delStoreTableRow(record)">删除</a>
                  </div>
                </a-table>
              </div>
            </div>
          </a-tab-pane>
          <!--          数据分析-->
          <a-tab-pane :key="2" tab="数据分析">
            <!--            标题-->
            <div class="overview">
              <span class="overview-title mr10">数据总览</span>
              <span>统计说明</span>
              <a-popover>
                <template slot="content">
                  1、点击次数：点击进入区域扫码页面的次数<br>
                  2、添加客户数：点击添加的客户总数，若客户重复添加将会重复计数<br>
                  3、流失客户数：点击添加后将成员删除的客户数
                </template>
                <a-icon type="question-circle" />
              </a-popover>
            </div>
            <!--            数据统计-->
            <div class="data-box mb20">
              <div class="data">
                <div class="item">
                  <div class="count">{{ seeTotalArray[0].today_click_num }}</div>
                  <div class="desc">今日点击次数</div>
                </div>
                <div class="item">
                  <div class="count">{{ seeTotalArray[0].today_add_num }}</div>
                  <div class="desc">今日添加客户数</div>
                </div>
                <div class="item">
                  <div class="count">{{ seeTotalArray[0].today_loss_num }}</div>
                  <div class="desc">今日流失客户数</div>
                </div>
              </div>
              <div class="data">
                <div class="item">
                  <div class="count">{{ seeTotalArray[0].total_click_num }}</div>
                  <div class="desc">总点击次数</div>
                </div>
                <div class="item">
                  <div class="count">{{ seeTotalArray[0].total_add_num }}</div>
                  <div class="desc">总添加客户数</div>
                </div>
                <div class="item">
                  <div class="count">{{ seeTotalArray[0].total_loss_num }}</div>
                  <div class="desc">总流失客户数</div>
                </div>
              </div>
            </div>
            <!--            表格-->
            <div class="analysis">
              <a-tabs v-model="showlookDataPanel" @change="tabsdataPanel">
                <a-tab-pane :key="1" tab="查看客户数据"></a-tab-pane>
                <a-tab-pane :key="2" tab="查看门店数据"></a-tab-pane>
              </a-tabs>
              <!--                  筛选条件-->
              <div class="search">
                <div style="display: flex;flex-wrap: wrap;" v-if="showlookDataPanel==1">
                  <!--                  客户昵称-->
                  <div class="shop-name mr14 mb5 mt5">客户昵称：
                    <a-input-search
                      placeholder="请输入门店名称"
                      style="width: 200px"
                      v-model="askClientData.contactName"
                      @search="searchClientTable"
                      :allowClear="true"
                      @change="cleanclientIpt"
                    />
                  </div>
                  <!--                  门店店主-->
                  <div class="city mr14 mb5 mt5">
                    <span v-if="pageType!=3">门店店主：</span>
                    <span v-else>城市负责人：</span>
                    <a-select
                      placeholder="请选择负责人 "
                      :showSearch="true"
                      @change="selectShopkeeper"
                      style="width: 200px;"
                      v-model="showClientData.employeeId"
                    >
                      <a-select-option
                        v-for="(item,index) in staffArray"
                        :key="index"
                        :value="item.name"
                      >{{ item.name }}
                      </a-select-option>
                    </a-select>
                  </div>
                  <!--                  时间筛选-->
                  <div class="shopkeeper mr14 mb5 mt5">
                    时间筛选：
                    <a-range-picker class="date-picker" @change="screenClientTime" v-model="showClientData.clientTimeData" />
                  </div>
                  <!--                  门店名称-->
                  <div class="on-state mr14 mb5 mt5" v-if="pageType!=3">
                    门店名称：
                    <a-input-search
                      placeholder="请输入门店名称"
                      style="width: 200px"
                      v-model="askClientData.shopName"
                      @search="searchClientShop"
                      :allowClear="true"
                      @change="cleanclientShop"
                    />
                  </div>
                  <!--                  流失状态-->
                  <div class="on-state mr14 mb5 mt5">
                    流失状态：
                    <a-select
                      style="width: 200px"
                      placeholder="请选择流失状态"
                      v-model="askClientData.status"
                      @change="selectlossStatus"
                    >
                      <a-select-option :value="1">未流失</a-select-option>
                      <a-select-option :value="2">已流失</a-select-option>
                    </a-select>
                  </div>
                  <!--                  用户位置-->
                  <div class="on-state mr14 mb5 mt5">
                    用户位置：
                    <a-select
                      style="width: 120px"
                      @change="screenUserProvince"
                      placeholder="请选择省份"
                      v-model="showClientData.province">
                      <a-select-option v-for="(item,index) in cityArray" :key="index"> {{ item.province }}</a-select-option>
                    </a-select>
                    <a-select
                      style="width: 120px;margin-left: 20px;"
                      placeholder="请选择城市"
                      v-model="showClientData.city"
                      @change="selectUserCity"
                    >
                      <a-select-option v-for="(item,index) in userProvince" :key="index" >
                        {{ item.city }}
                      </a-select-option>
                    </a-select>
                  </div>
                </div>
                <div v-else style="display: flex;flex-wrap: wrap;">
                  <div class="on-state mr14 mb5 mt5" v-if="pageType==3">
                    城市：
                    <a-select
                      style="width: 120px"
                      @change="screenUserProvince"
                      placeholder="请选择省份"
                      v-model="showClientData.province">
                      <a-select-option v-for="(item,index) in cityArray" :key="index"> {{ item.province }}</a-select-option>
                    </a-select>
                    <a-select
                      style="width: 120px;margin-left: 20px;"
                      placeholder="请选择城市"
                      v-model="showClientData.city"
                      @change="selectUserCity"
                    >
                      <a-select-option v-for="(item,index) in userProvince" :key="index" >
                        {{ item.city }}
                      </a-select-option>
                    </a-select>
                  </div>
                  <!--                  门店名称-->
                  <div class="shop-name mr14 mb5 mt5" v-if="pageType!=3">门店名称：
                    <a-input-search
                      placeholder="请输入门店名称"
                      style="width: 200px"
                      v-model="askPanelStoreData.shopName"
                      @search="searchStoreName"
                      :allowClear="true"
                      @change="emptyStoreName"
                    >
                    </a-input-search></div>
                  <!--     v-if="pageType!&&showlookDataPanel!=2"   2  2           门店门主-->
                  <div class="city mr14 mb5 mt5" v-if="pageType==1&&showlookDataPanel==2">
                    门店店主：
                    <a-select
                      placeholder="请选择门店门主"
                      :showSearch="true"
                      @change="storetableLeader"
                      style="width: 200px;"
                      v-model="showStoreData.leader"
                    >
                      <a-select-option
                        v-for="(item,index) in staffArray"
                        :key="index"
                        :value="item.name"
                      >{{ item.name }}
                      </a-select-option>
                    </a-select>
                  </div>
                  <div class="on-state mr14 mb5 mt5">
                    开启状态：
                    <a-select style="width: 200px" placeholder="请选择开启状态" v-model="showStoreData.state" @change="storeState">
                      <a-select-option :value="1">已开启</a-select-option>
                      <a-select-option :value="0">未开启</a-select-option>
                    </a-select>
                  </div>
                </div>
                <div class="reset">
                  <a-button @click="resetTablebtn">重置</a-button>
                </div>
              </div>
              <div class="analysis-table mt14 mb16">
                <div class="customers">
                  <div class="cu-box">
                    <span class="customers-title">
                      共{{ table.data.length }}个
                      <span v-if="showlookDataPanel==1">客户</span>
                      <span v-else>门店</span>
                    </span>
                    <a-divider type="vertical" />
                    <span>
                      <a-icon type="redo" />更新数据
                    </span>
                  </div>
                  <div class="button">
                    <a-button type="primary" ghost v-if="showlookDataPanel==1" @click="batchTag">批量打标签</a-button>
                  </div>
                  <!--   弹窗           -->
                  <addlableIndex @choiceTagsArr="acceptArray" ref="childRef"/>
                </div>
              </div>
              <!--              表格-->
              <div>
                <a-table
                  :columns="showlookDataPanel==1?table.clientColumns[pageType-1]:table.storeColumns[pageType-1]"
                  :data-source="table.data"
                  :rowSelection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }">
                  <div slot="ownerStore" slot-scope="text,record">
                    <a-tag><a-icon type="user" />{{ record.employee.name }}</a-tag>
                  </div>
                  <div slot="tag" slot-scope="text">
                    <a-tag v-for="(item,index) in text" :key="index">{{ item.name }}</a-tag>
                  </div>
                  <div slot="status" slot-scope="text">
                    <div v-if="showlookDataPanel==1">
                      <a-tag color="green" v-if="text==1">未流失</a-tag>
                      <a-tag color="gray" v-else>已流失</a-tag>
                    </div>
                    <div v-else>
                      <a-tag color="gray" v-if="text==0">未开启</a-tag>
                      <a-tag color="green" v-else>已开启</a-tag>
                    </div>
                  </div>
                  <div slot="operation" slot-scope="text,record">
                    <a v-if="showlookDataPanel==1" @click="$router.push({ path: '/workContact/contactFieldPivot?contactId='+record.contactId+'&employeeId='+record.employee.employeeId+'&isContact=2'})">
                      <span v-if="showlookDataPanel==1">客户详情</span>
                    </a>
                  </div>
                </a-table>
              </div>
            </div>
          </a-tab-pane>
        </a-tabs>
      </a-card>
      <shopkeeperUpdate ref="shopkeeperUpdate" @change="updateShopData" />
      <shopkeeperCreate ref="shopkeeperCreate" @change="receiveShopData" />
    </a-spin>

    <input type="text" class="copy-input" ref="copyInput">
    <!--    授权提示-->
    <warrantTip ref="warrantTip" />
  </div>
</template>

<script>
import {
  // eslint-disable-next-line no-unused-vars
  indexApi, department, searchCityApi, statusApi, storeApi, destroyApi, batchContactTagsApi, publicIndexApi,
  // eslint-disable-next-line no-unused-vars
  updateEmployeeApi, showApi, showContactApi, showShopApi, shareApi, updateApi, workRoomIndexApi, updateQrcodeApi
} from '@/api/shopCode'
import QRCode from 'qrcodejs2'
import shopkeeperUpdate from '@/views/shopCode/components/shopkeeperUpdate'
import shopkeeperCreate from '@/views/shopCode/components/shopkeeperCreate'
import shopkeeperSetup from '@/views/shopCode/components/shopkeeperSetup'
import addCityPop from '@/views/shopCode/components/addCityPop'
import addlableIndex from '@/components/addlabel/index'
import warrantTip from '@/components/warrantTip/warrantTip'
export default {
  components: {
    shopkeeperUpdate, shopkeeperCreate, shopkeeperSetup, addCityPop, addlableIndex, warrantTip
  },
  data () {
    return {
      // 公众号列表
      publiclist: [],
      // 公众号
      officialAccount: '',
      // 打标签请求id
      askClientIds: [],
      // 表格选中的数据
      selectedRowKeys: [],
      // 添加城市弹窗
      addToCityPopup: true,
      // 选择省
      selectProvince: undefined,
      // 选择市
      selectCity: undefined,
      // 选择页面类型
      pageType: 1,
      // 分享弹窗
      visible: false,
      // 群打卡链接
      clockInLink: '',
      // 门店数据
      // 显示的面板
      showpanel: 1,
      // 显示加载层
      loadShow: false,
      // 所有员工数据
      staffArray: [],
      // 拉群活码
      workRoomArray: [],
      // 省份
      cityArray: [],
      // 城市
      citySelect: [],
      // 选择的省份城市数据
      optCityData: {},
      // 门店请求数据
      askStoreData: {
        // 类型
        type: 1,
        // 门店名称
        name: '',
        // 省份
        province: '',
        city: '',
        // 门店活码
        qw_code: '',
        // 门店店主id
        employee: ''
      },
      // 门店表格
      storeList: {
        col: [
          [
            {
              title: '门店名称',
              dataIndex: 'name',
              width: '20%'
            },
            {
              title: '地址',
              dataIndex: 'address',
              width: '30%'
            },
            {
              title: '门店店主',
              dataIndex: 'employee',
              scopedSlots: { customRender: 'employee' },
              width: '20%'
            },
            {
              title: '状态',
              dataIndex: 'status',
              scopedSlots: { customRender: 'status' },
              width: '20%'
            },
            {
              title: '操作',
              dataIndex: 'operate',
              scopedSlots: { customRender: 'operate' },
              width: '80px'
            }
          ],
          [
            {
              title: '门店名称',
              dataIndex: 'name',
              width: '20%'
            },
            {
              title: '地址',
              dataIndex: 'address',
              width: '30%'
            }, {
              title: '门店活码',
              dataIndex: 'qwCode',
              scopedSlots: { customRender: 'qwCode' },
              width: '20%'
            }, {
              title: '状态',
              dataIndex: 'status',
              scopedSlots: { customRender: 'status' },
              width: '20%'
            },
            {
              title: '操作',
              dataIndex: 'operate',
              scopedSlots: { customRender: 'operate' },
              width: '80px'
            }
          ],
          [{
            title: '城市',
            dataIndex: 'city',
            width: '10%'
          }, {
            title: '门店活码',
            dataIndex: 'qwCode',
            scopedSlots: { customRender: 'qwCode' },
            width: '30%'
          }, {
            title: '状态',
            dataIndex: 'status',
            scopedSlots: { customRender: 'status' },
            width: '30%'
          },
          {
            title: '操作',
            dataIndex: 'operate',
            scopedSlots: { customRender: 'operate' },
            width: '80px'
          }]
        ],
        data: []
      },
      // 数据分析
      seeTotalArray: [
        {
          today_click_num: 0,
          today_add_num: 0,
          today_loss_num: 0,
          total_click_num: 0,
          total_add_num: 0,
          total_loss_num: 0
        }
      ],
      // 查看数据面板
      showlookDataPanel: 1,
      // 客户请求数据
      askClientData: {
        type: 1,
        contactName: '',
        employeeId: '',
        start_time: '',
        end_time: '',
        shopName: '',
        province: '',
        city: ''
      },
      // 门店请求数据
      askPanelStoreData: {
        type: 1,
        employeeId: '',
        shopName: '',
        status: ''
      },
      // 数据分析-客户数据
      showClientData: {
        // 数据分析--客户筛选时间
        clientTimeData: []
      },
      // 数据分析--门店数据
      showStoreData: {},
      // 选择的省份城市
      userProvince: [],
      table: {
        clientColumns: [
          [
            {
              title: '客户昵称',
              dataIndex: 'contactName'
            },
            {
              title: '门店店主',
              dataIndex: 'ownerStore',
              scopedSlots: { customRender: 'ownerStore' }
            },
            {
              title: '门店名称',
              dataIndex: 'shopName'
            },
            {
              title: '标签',
              dataIndex: 'tag',
              scopedSlots: { customRender: 'tag' }
            },
            {
              title: '用户位置',
              dataIndex: 'address'
            },
            {
              title: '添加时间',
              dataIndex: 'createdAt'
            },
            {
              title: '流失状态',
              dataIndex: 'status',
              scopedSlots: { customRender: 'status' }
            },
            {
              title: '操作',
              dataIndex: 'operation',
              scopedSlots: { customRender: 'operation' }
            }
          ],
          [
            {
              title: '客户昵称',
              dataIndex: 'contactName'
            },
            {
              title: '门店店主',
              dataIndex: 'ownerStore',
              scopedSlots: { customRender: 'ownerStore' }
            },
            {
              title: '门店名称',
              dataIndex: 'shopName'
            },
            {
              title: '标签',
              dataIndex: 'tag',
              scopedSlots: { customRender: 'tag' }
            },
            {
              title: '用户位置',
              dataIndex: 'address'
            },
            {
              title: '添加时间',
              dataIndex: 'createdAt'
            },
            {
              title: '流失状态',
              dataIndex: 'status',
              scopedSlots: { customRender: 'status' }
            },
            {
              title: '操作',
              dataIndex: 'operation',
              scopedSlots: { customRender: 'operation' }
            }
          ],
          [
            {
              title: '客户昵称',
              dataIndex: 'contactName'
            },
            {
              title: '城市负责人',
              dataIndex: 'ownerStore',
              scopedSlots: { customRender: 'ownerStore' }
            },
            {
              title: '标签',
              dataIndex: 'tag',
              scopedSlots: { customRender: 'tag' }
            },
            {
              title: '城市',
              dataIndex: 'city'
            },
            {
              title: '用户位置',
              dataIndex: 'address'
            },
            {
              title: '添加时间',
              dataIndex: 'createdAt'
            },
            {
              title: '流失状态',
              dataIndex: 'status',
              scopedSlots: { customRender: 'status' }
            },
            {
              title: '操作',
              dataIndex: 'operation',
              scopedSlots: { customRender: 'operation' }
            }
          ]
        ],
        storeColumns: [
          [
            {
              title: '门店名称',
              dataIndex: 'name'
            },
            {
              title: '门店地址',
              dataIndex: 'address'
            },
            {
              title: '门店店主',
              dataIndex: 'ownerStore',
              scopedSlots: { customRender: 'ownerStore' }
            },
            {
              title: '今日添加客户数',
              dataIndex: 'today_num'
            },
            {
              title: '添加客户总数',
              dataIndex: 'total_num'
            },
            {
              title: '门店状态',
              dataIndex: 'status',
              scopedSlots: { customRender: 'status' }
            },
            {
              title: '操作',
              dataIndex: 'operation',
              scopedSlots: { customRender: 'operation' }
            }
          ],
          [
            {
              title: '门店名称',
              dataIndex: 'name'
            },
            {
              title: '门店地址',
              dataIndex: 'address'
            },
            {
              title: '今日添加客户数',
              dataIndex: 'today_num'
            },
            {
              title: '添加客户总数',
              dataIndex: 'total_num'
            },
            {
              title: '门店状态',
              dataIndex: 'status',
              scopedSlots: { customRender: 'status' }
            }
          ],
          [
            {
              title: '城市',
              dataIndex: 'city'
            },
            {
              title: '今日添加客户数',
              dataIndex: 'today_num'
            },
            {
              title: '添加客户总数',
              dataIndex: 'total_num'
            },
            {
              title: '门店状态',
              dataIndex: 'status',
              scopedSlots: { customRender: 'status' }
            }
          ]
        ],
        data: []
      }
    }
  },
  created () {
    this.setUpPublicName()
    this.getPublicList()
    // 门店方法
    this.getStoreTable(this.askStoreData)
    this.getNumberData()
    this.getcityData()
    this.getWorkRoomData()
    // 数据总览方法
    this.seeTotalData()
    this.getClientTable(this.askClientData)
  },
  methods: {
    // 设置公众号
    setUpPublicName () {
      publicIndexApi({ type: 3 }).then((res) => {
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
    // 接收组件传值
    acceptArray (e) {
      if (e.length == 0) {
        this.$message.error('请至少选择一个标签')
        return false
      }
      const askTags = []
      e.forEach((item, index) => {
        const tags = {}
        tags.tagid = item.id
        tags.tagname = item.name
        askTags.push(tags)
      })
      batchContactTagsApi({
        ids: this.askClientIds,
        tags: askTags
      }).then((res) => {
        this.getClientTable(this.askClientData)
        this.selectedRowKeys = []
        this.$message.success('操作成功')
      })
    },
    onSelectChange (e) {
      this.selectedRowKeys = e
    },
    // 批量打标签
    batchTag () {
      if (this.selectedRowKeys.length == 0) {
        this.$message.warning('请先选中一个客户')
        return false
      }
      this.askClientIds = []
      this.selectedRowKeys.forEach((item, index) => {
        this.askClientIds[index] = this.table.data[item].contactId
      })
      this.$refs.childRef.show()
    },
    // 选择页面类型
    selectPageType () {
      this.selectedRowKeys = []
      this.showpanel = 1
      if (this.showpanel == 1) {
        this.askStoreData = {}
        this.optCityData = {}
        this.selectProvince = undefined
        this.selectCity = undefined
        this.askStoreData.type = this.pageType
        this.citySelect = []
        this.getStoreTable(this.askStoreData)
        this.getcityData()
      } else {
        this.askClientData = {}
        this.showClientData.province = undefined
        this.showClientData.city = undefined
        this.userProvince = []
        this.askClientData.type = this.pageType
        //  数据总览
        this.seeTotalData()
        //  查看客户数据
        this.showlookDataPanel = 1
        this.getClientTable(this.askClientData)
        this.getcityData()
      }
    },
    // 切换  门店列表--数据分析
    cutPageType () {
      this.seeTotalData()
      this.tabsdataPanel()
    },
    // 切换数据面板
    tabsdataPanel () {
      this.table.data = []
      this.selectedRowKeys = []
      this.showClientData.province = undefined
      this.showClientData.city = undefined
      this.userProvince = []
      if (this.showlookDataPanel == 1) {
        this.askClientData = {}
        this.askClientData.type = this.pageType
        this.getClientTable(this.askClientData)
        this.getcityData()
      } else {
        this.askPanelStoreData = {}
        this.askPanelStoreData.type = this.pageType
        this.storeTableData(this.askPanelStoreData)
        this.getcityData()
      }
    },
    // 数据总览
    seeTotalData () {
      showApi({
        type: this.pageType
      }).then((res) => {
        this.seeTotalArray = res.data
      })
    },
    // 获取用户表格数据
    getClientTable (params) {
      showContactApi(params).then((res) => {
        this.table.data = res.data.list
      })
    },
    // 接收添加城市数据
    receiveCityData (e) {
      if (e.id) {
        updateApi(e).then((res) => {
          this.$message.success('修改成功')
          this.getStoreTable(this.askStoreData)
          this.getcityData()
        })
      } else {
        storeApi(e).then((res) => {
          this.$message.success('创建成功')
          this.getStoreTable(this.askStoreData)
          this.getcityData()
        })
      }
    },
    // 修改门店活码
    modifyCode (e, record) {
      const shopkeeperData = {}
      this.workRoomArray.forEach((item) => {
        if (item.workRoomAutoPullId == e) {
          shopkeeperData.workRoomAutoPullId = item.workRoomAutoPullId
          shopkeeperData.qrcodeUrl = item.qrcodeUrl
        }
      })
      const params = {
        id: record.id,
        qw_code: shopkeeperData
      }
      updateQrcodeApi(params).then((res) => {
        this.getStoreTable(this.askStoreData)
        this.loadShow = true
        setTimeout(() => {
          this.loadShow = false
        }, 300)
      })
    },
    getWorkRoomData () {
      workRoomIndexApi({
        page: 1,
        perPage: 10000
      }).then((res) => {
        this.workRoomArray = res.data.list
      })
    },
    // 检索城市数据
    getcityData () {
      searchCityApi({ type: this.pageType
      }).then((res) => {
        this.cityArray = res.data.province
      })
    },
    // 获取门店表格
    getStoreTable (params) {
      indexApi(params).then((res) => {
        this.storeList.data = res.data.list
      })
    },
    // 选择省份
    handleProvinceChange (e) {
      this.citySelect = this.cityArray[e].city
      this.selectCity = undefined
    },
    // 选择城市
    choiceCity () {
      this.askStoreData.province = this.cityArray[this.selectProvince].province
      this.askStoreData.city = this.citySelect[this.selectCity].city
      this.getStoreTable(this.askStoreData)
    },
    // 更新数据
    updateShopData (e) {
      updateApi(e).then((res) => {
        this.getStoreTable(this.askStoreData)
        this.loadShow = true
        setTimeout(() => {
          this.loadShow = false
          this.$message.success('修改成功')
        }, 200)
      })
    },
    // 删除门店表格数据
    delStoreTableRow (record) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          destroyApi({ id: record.id }).then((res) => {
            that.$message.success('删除成功')
            that.getStoreTable(that.askStoreData)
            that.getcityData()
          })
        }
      })
    },
    // 接收新增门店的数据信息
    receiveShopData (e) {
      storeApi(e).then((res) => {
        this.$message.success('创建成功')
        this.getStoreTable(this.askStoreData)
      })
    },
    // 下载二维码
    downQrcode () {
      const img = this.$refs.qrCode.childNodes[1]
      const a = document.createElement('a')
      const event = new MouseEvent('click')
      a.download = 'qrcode'
      a.href = img.src
      a.dispatchEvent(event)
    },
    // 复制群打卡链接
    copyLink () {
      const inputElement = this.$refs.copyInput
      inputElement.value = this.clockInLink
      inputElement.select()
      document.execCommand('Copy')
      this.$message.success('复制成功')
    },
    // 分享事件
    shareBtn () {
      this.visible = true
      shareApi({ type: this.pageType }).then((res) => {
        this.clockInLink = res.data.link
        setTimeout(() => {
          this.initQrcode(res.data.link)
        }, 200)
      })
    },
    // 加载二维码
    initQrcode (link) {
      this.$refs.qrCode.innerHTML = ''
      // eslint-disable-next-line no-new
      new QRCode(this.$refs.qrCode, {
        text: link,
        width: 122,
        height: 122
      })
    },
    // 数据分析方法
    // 数据分析--重置
    resetTablebtn () {
      if (this.showlookDataPanel == 1) {
        this.askClientData = {}
        this.showClientData.province = undefined
        this.showClientData.city = undefined
        this.userProvince = []
        this.askClientData.type = this.pageType
        this.getClientTable(this.askClientData)
      } else {
        this.showClientData.province = undefined
        this.showClientData.city = undefined
        this.userProvince = []
        this.askPanelStoreData = {}
        this.showStoreData = {}
        this.askPanelStoreData.type = this.pageType
        this.storeTableData(this.askPanelStoreData)
      }
    },
    // 门店--门店状态
    storeState (e) {
      if (e == 0) {
        this.askPanelStoreData.status = 0
      } else {
        this.askPanelStoreData.status = 1
      }
      this.storeTableData(this.askPanelStoreData)
    },
    // 门店-门店门主
    storetableLeader (e) {
      this.staffArray.forEach((item) => {
        if (item.name == e) {
          this.askPanelStoreData.employeeId = item.id
        }
      })
      this.storeTableData(this.askPanelStoreData)
    },
    // 门店-搜索门店
    searchStoreName () {
      this.storeTableData(this.askPanelStoreData)
    },
    // 门店--清空输入框
    emptyStoreName () {
      if (this.askPanelStoreData.shopName == '') {
        this.storeTableData(this.askPanelStoreData)
      }
    },
    // 客户-省
    screenUserProvince (e) {
      this.userProvince = this.cityArray[e].city
    },
    // 客户-市
    selectUserCity () {
      if (this.showlookDataPanel == 1) {
        this.askClientData.province = this.cityArray[this.showClientData.province].province
        this.askClientData.city = this.userProvince[this.showClientData.city].city
        this.getClientTable(this.askClientData)
      } else {
        this.askPanelStoreData.province = this.cityArray[this.showClientData.province].province
        this.askPanelStoreData.city = this.userProvince[this.showClientData.city].city
        this.storeTableData(this.askPanelStoreData)
      }
    },
    // 客户-流失状态
    selectlossStatus () {
      this.getClientTable(this.askClientData)
    },
    // 客户-时间筛选
    screenClientTime (date, dateString) {
      this.askClientData.start_time = dateString[0]
      this.askClientData.end_time = dateString[1]
      this.getClientTable(this.askClientData)
    },
    // 用户-搜索客户
    searchClientTable () {
      this.getClientTable(this.askClientData)
    },
    // 用户-清除客户输入框
    cleanclientIpt () {
      if (this.askClientData.contactName == '') {
        this.getClientTable(this.askClientData)
      }
    },
    // 用户-搜索门店
    searchClientShop () {
      this.getClientTable(this.askClientData)
    },
    // 用户-清除门店输入框
    cleanclientShop () {
      if (this.askClientData.shopName == '') {
        this.getClientTable(this.askClientData)
      }
    },
    // 用户-选择门店店主
    selectShopkeeper (e) {
      this.staffArray.forEach((item) => {
        if (item.name == e) {
          this.askClientData.employeeId = item.id
        }
      })
      this.getClientTable(this.askClientData)
    },
    // 获取门店表格数据
    storeTableData (params) {
      showShopApi(params).then((res) => {
        this.table.data = res.data.list
      })
    },
    // 门店方法
    // 修改店主
    selectStaff (e, record) {
      let shopkeeperData = {}
      this.staffArray.forEach((item) => {
        if (item.name == e) {
          shopkeeperData = item
        }
      })
      updateEmployeeApi({
        id: record.id,
        employee: shopkeeperData
      }).then((res) => {
        this.getStoreTable(this.askStoreData)
        this.loadShow = true
        setTimeout(() => {
          this.loadShow = false
        }, 300)
      })
    },
    // 更改门店状态
    openShopState (record) {
      let status = 0
      if (record.status == 0) {
        status = 1
      } else {
        status = 0
      }
      statusApi({
        id: record.id,
        status
      }).then((res) => {
        this.getStoreTable(this.askStoreData)
        this.loadShow = true
        setTimeout(() => {
          this.loadShow = false
        }, 300)
      })
    },
    // 重置
    resetBtn () {
      this.askStoreData = {}
      this.optCityData = {}
      this.selectProvince = undefined
      this.selectCity = undefined
      this.citySelect = []
      this.askStoreData.type = this.pageType
      this.getStoreTable(this.askStoreData)
    },
    // 筛选开关状态
    screenSwitchState () {
      this.getStoreTable(this.askStoreData)
    },
    // 选择门店门主
    selectStore (e) {
      this.staffArray.forEach((item) => {
        if (item.name == e) {
          this.askStoreData.employee = item.id
        }
      })
      this.getStoreTable(this.askStoreData)
    },
    // 搜索门店名称
    retrievalTable () {
      this.getStoreTable(this.askStoreData)
    },
    // 清空搜索框
    emptyNickIpt () {
      if (this.askStoreData.name == '') {
        this.getStoreTable(this.askStoreData)
      }
    },
    // 搜索门店活码
    searchQwCode () {
      this.getStoreTable(this.askStoreData)
    },
    // 清空
    emptyQwCode () {
      if (this.askStoreData.qw_code == '') {
        this.getStoreTable(this.askStoreData)
      }
    },
    // 获取员工数据
    getNumberData () {
      department().then((res) => {
        this.staffArray = res.data.employee
      })
    }
  }
}
</script>

<style lang="less">
.switch_account{
  position: absolute;
  right: 0;
  top: 0;
  z-index: 10;
}
.work_room{
  display: flex;
  img{
    width: 25px;
    height: 25px;
  }
  div{
    margin-left: 10px;
  }
}
.search {
  display: flex;
  flex-wrap: wrap;
}
.modeRow{
  display: flex;
}
.modeTitle{
  font-size: 14px;
  font-weight: 600;
  color: rgba(0,0,0,.85);
  line-height: 20px;
  border-left: 2px solid #1890ff;
  padding-left: 7px;
}
.modeTips{
  font-size: 14px;
  margin-top: 8px;
  color: rgba(0,0,0,.45);
}
.qr-code {
  text-align: center;
  margin-top: 10px;
  img {
    width: 114px;
    height: 114px;
    display: inline-block;
  }
}
.separate{
  width: 1px;
  height: 180px;
  background: #E8E8E8;
  margin-left: 40px;
  margin-right: 40px;
}
.linkBox{
  width: 253px;
  height: 111px;
  overflow-x: hidden;
  padding: 8px 14px 8px 16px;
  border-radius: 2px;
  border: 1px solid #dcdfe6;
  margin-top: 8px;
}
.footRow{
  margin-top: 10px;
  display: flex;
  justify-content:flex-end;
}
.footBtn{
  margin-right: 30px;
}
.ant-modal-title{
  text-align: center;
  font-size: 17px;
  font-weight: 600;
}

.copy-input {
  position: absolute;
  top: 0;
  left: 0;
  opacity: 0;
  z-index: -10;
}

.reset {
  position: absolute;
  right: 25px;
}

.title {
  display: flex;
  align-items: center;

  .in-shop {
    flex: 1;
    font-weight: bold;
  }
}

.customers {
  display: flex;

  .cu-box {
    flex: 1;
  }
}

.overview-title,
.customers-title {
  font-weight: bold;
  font-size: 16px;
}

.date-picker {
  width: 210px;
}

.data-box {
  display: flex;
  align-items: center;
  justify-content: center;
  max-width: 1100px;
  margin-top: 15px;

  .data {
    flex: 1;
    height: 120px;
    background: #fbfdff;
    border: 1px solid #daedff;

    margin-right: 25px;
    display: flex;
    align-items: center;

    .item {
      flex: 1;
      border-right: 1px solid #e9e9e9;

      .count {
        font-size: 24px;
        font-weight: 500;
        text-align: center;
      }

      .desc {
        font-size: 13px;
        text-align: center;
      }

      &:last-child {
        border-right: 0;
      }
    }

    &:last-child {
      margin-right: 0;
    }
  }
}

.store-title {
  display: flex;
  align-items: center;

  .store-box {
    flex: 1;
  }
}
</style>
