<template>
  <div class="box">
    <a-card>
      <a-tabs type="card">
        <a-tab-pane key="1" tab="门店列表">
          <!--          搜索-->
          <div class="search">
            <div class="shop-name">
              门店名称：<a-input-search placeholder="请输入门店名称" style="width: 200px"/>
            </div>
            <div class="city ml30">
              城市：
              <a-select style="width: 200px" default-value="请选择城市">
                <a-select-option value="Home">
                  临沂
                </a-select-option>
                <a-select-option value="Company">
                  上海
                </a-select-option>
                <a-select-option value="del">
                  纽约
                </a-select-option>
              </a-select>
            </div>
            <div class="shopkeeper ml30">
              拉群活码：<a-input-search placeholder="请输入活码名称" style="width: 200px"/>
            </div>
            <div class="on-state ml30">
              开启状态：
              <a-select style="width: 200px" default-value="请选择开启状态">
                <a-select-option value="Home">
                  已开启
                </a-select-option>
                <a-select-option value="Company">
                  未开启
                </a-select-option>
              </a-select>
            </div>
            <div class="reset">
              <a-button>
                重置
              </a-button>
            </div>
          </div>
          <div class="shop-table mt20">
            <div class="title mb20">
              <span class="in-shop">
                共2个门店
              </span>
              <div class="button">
                <a-button type="primary" ghost>
                  <a-icon type="plus" />添加门店
                </a-button>
                <a-button type="primary" ghost class="ml10">
                  <a-icon type="setting" />设置
                </a-button>
                <a-button type="primary" ghost class="ml10">
                  <a-icon type="share-alt" />分享
                </a-button>
              </div>
            </div>
            <div class="table-box">
              <a-table :columns="storeList.col" :data-source="storeList.data">
                <div slot="shopkeeper">
                  <a-dropdown>
                    <a-menu slot="overlay" >
                      <a-menu-item key="1">
                        <a-icon type="user" />
                        牛俊生
                      </a-menu-item>
                      <a-menu-item key="2">
                        <a-icon type="user" />
                        刘波
                      </a-menu-item>
                      <a-menu-item key="3">
                        <a-icon type="user" />
                        牛波
                      </a-menu-item>
                    </a-menu>
                    <a-button>
                      牛俊生
                      <a-icon type="down" />
                    </a-button>
                  </a-dropdown>
                </div>
                <div slot="state">
                  <a-switch size="small" default-checked />
                  <span class="ml4">已开启</span>
                </div>
                <div slot="operate">
                  <a href="#" @click="$refs.shopUpdate.show()">修改</a>
                  <a-divider type="vertical"/>
                  <a href="#">删除</a>
                </div>
              </a-table>
            </div>
          </div>
        </a-tab-pane>
        <a-tab-pane key="2" tab="数据分析">
          <div class="overview">
            <span class="overview-title mr10">
              数据总览
            </span>
            <span>
              统计说明
            </span>
            <a-popover>
              <template slot="content">
                1、点击次数：点击进入区域扫码页面的次数<br>
                2、添加客户数：点击添加的客户总数，若客户重复添加将会重复计数<br>
                3、流失客户数：点击添加后将成员删除的客户数
              </template>
              <a-icon type="question-circle"/>
            </a-popover>
          </div>
          <div class="data-box mb20">
            <div class="data">
              <div class="item">
                <div class="count">
                  0
                </div>
                <div class="desc">
                  今日点击次数
                </div>
              </div>
              <div class="item">
                <div class="count">
                  0
                </div>
                <div class="desc">
                  今日添加客户数
                </div>
              </div>
              <div class="item">
                <div class="count">
                  0
                </div>
                <div class="desc">
                  今日流失客户数
                </div>
              </div>
            </div>
            <div class="data">
              <div class="item">
                <div class="count">
                  0
                </div>
                <div class="desc">
                  总点击次数
                </div>
              </div>
              <div class="item">
                <div class="count">
                  0
                </div>
                <div class="desc">
                  总添加客户数
                </div>
              </div>
              <div class="item">
                <div class="count">
                  0
                </div>
                <div class="desc">
                  总流失客户数
                </div>
              </div>
            </div>
          </div>
          <div class="analysis">
            <a-tabs>
              <a-tab-pane key="1" tab="查看客户数据">
                <div class="search">
                  <div class="shop-name mr14 mb5 mt5">
                    客户昵称：<a-input-search placeholder="请输入客户昵称" style="width: 200px"/>
                  </div>
                  <div class="city mr14 mb5 mt5">
                    门店店主：
                    <a-select style="width: 200px" default-value="请选择门店店主">
                      <a-select-option value="Home">
                        刘波
                      </a-select-option>
                      <a-select-option value="Company">
                        水波
                      </a-select-option>
                    </a-select>
                  </div>
                  <div class="shopkeeper mr14 mb5 mt5">
                    时间筛选：<a-range-picker class="date-picker"/>
                  </div>
                  <div class="on-state mr14 mb5 mt5">
                    门店名称：<a-input-search placeholder="请输入门店名称" style="width: 200px"/>
                  </div>
                  <div class="on-state mr14 mb5 mt5">
                    流失状态：
                    <a-select style="width: 200px" default-value="请选择流失状态">
                      <a-select-option value="Home">
                        已流失
                      </a-select-option>
                      <a-select-option value="Company">
                        未流失
                      </a-select-option>
                    </a-select>
                  </div>
                  <div class="on-state mr14 mb5 mt5">
                    用户位置：
                    <a-select style="width: 200px" default-value="请选择用户位置">
                      <a-select-option value="Home">
                        临沂
                      </a-select-option>
                      <a-select-option value="Company">
                        上海
                      </a-select-option>
                    </a-select>
                  </div>
                  <div class="reset">
                    <a-button>
                      重置
                    </a-button>
                  </div>
                </div>
                <div class="analysis-table mt14 mb16">
                  <div class="customers">
                    <div class="cu-box">
                      <span class="customers-title">
                        共1个客户
                      </span>
                      <a-divider type="vertical" />
                      <span>
                        <a-icon type="redo" />更新数据
                      </span>
                    </div>
                    <div class="button">
                      <a-button type="primary" ghost>
                        批量打标签
                      </a-button>
                      <!-- <a-button type="primary" ghost class="ml10">
                        导出 Excel
                      </a-button> -->
                    </div>
                  </div>
                </div>
                <div>
                  <a-table :columns="customerData.col" :data-source="customerData.data" :rowSelection="{}">
                    <div slot="shopkeeper">
                      <a-tag>
                        <a-icon type="user" />
                        刘波
                      </a-tag>
                    </div>
                    <div slot="label">
                      <a-tag>
                        核心
                      </a-tag>
                      <a-tag>
                        重要
                      </a-tag>
                    </div>
                    <div slot="operation">
                      <a>客户详情</a>
                    </div>
                  </a-table>
                </div>
              </a-tab-pane>
              <a-tab-pane key="2" tab="查看门店数据">
                <div class="search">
                  <div class="on-state mr20 mb5 mt5">
                    门店名称：<a-input-search placeholder="请输入门店名称" style="width: 200px"/>
                  </div>
                  <div class="open mr20 mb5 mt5">
                    开启状态：
                    <a-select style="width: 200px" default-value="请选择开启状态">
                      <a-select-option value="Home">
                        已开启
                      </a-select-option>
                      <a-select-option value="Company">
                        未开启
                      </a-select-option>
                      <a-select-option value="del">
                        已删除
                      </a-select-option>
                    </a-select>
                  </div>
                  <div class="reset">
                    <a-button>
                      重置
                    </a-button>
                  </div>
                </div>
                <div class="store-data">
                  <div class="store-title mt12 mb16">
                    <div class="store-box">
                      <span class="customers-title">
                        共5个门店
                      </span>
                      <a-divider type="vertical" />
                      <span>
                        <a-icon type="redo" />更新数据
                      </span>
                    </div>
                    <div class="button">
                      <!-- <a-button type="primary" ghost class="ml10">
                        导出 Excel
                      </a-button> -->
                    </div>
                  </div>
                  <div class="store-table">
                    <a-table :columns="storeData.col" :data-source="storeData.data">
                      <div slot="shopkeeper">
                        刘波
                      </div>
                      <div slot="operation">
                        <a>员工详情</a>
                      </div>
                    </a-table>
                  </div>
                </div>
              </a-tab-pane>
            </a-tabs>
          </div>
        </a-tab-pane>
      </a-tabs>
    </a-card>
    <shopUpdate ref="shopUpdate"/>
    <!--    列表-->
  </div>
</template>

<script>
import shopUpdate from '@/views/shopCode/components/shopUpdate'

export default {
  data () {
    return {
      storeList: {
        col: [
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
            title: '拉群活码',
            scopedSlots: { customRender: 'shopkeeper' },
            width: '20%'
          },
          {
            title: '状态',
            scopedSlots: { customRender: 'state' },
            width: '20%'
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'operate' },
            width: '10%'
          }
        ],
        data: [
          {
            name: '金叶花园店',
            address: '山东省临沂市兰山区柳青街道上海路163号金叶花园'
          },
          {
            name: '123',
            address: '升级多少结束的换届大会'
          }
        ]
      },
      customerData: {
        col: [
          {
            title: '客户昵称',
            dataIndex: 'name'
          },
          {
            title: '门店店主',
            scopedSlots: { customRender: 'shopkeeper' }
          },
          {
            title: '门店名称',
            dataIndex: 'storeName'
          },
          {
            title: '标签',
            scopedSlots: { customRender: 'label' }
          },
          {
            title: '用户位置',
            dataIndex: 'position'
          },
          {
            title: '添加时间',
            dataIndex: 'time'
          },
          {
            title: '流失状态',
            dataIndex: 'state'
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        data: [
          {
            name: '刘波',
            storeName: '金叶花园店',
            position: '山东省临沂市',
            time: '2021-06-07 17:13',
            state: '已流失'
          }
        ]
      },
      storeData: {
        col: [
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
            scopedSlots: { customRender: 'shopkeeper' }
          },
          {
            title: '今日添加客户数',
            dataIndex: 'addToDay'
          },
          {
            title: '添加客户总数',
            dataIndex: 'add'
          },
          {
            title: '门店状态',
            dataIndex: 'state'
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        data: [
          {
            name: '金叶花园店',
            address: '山东省临沂市兰山区柳青街道三和园B区2号楼',
            addToDay: '0',
            add: '20',
            state: '已开启'
          }
        ]
      }
    }
  },
  components: {
    shopUpdate
  }
}
</script>

<style lang="less" scoped>
.search{
  display: flex;
  flex-wrap: wrap;
}
.reset{
  position: absolute;
  right: 25px;
}

.title{
  display: flex;
  align-items: center;

  .in-shop{
    flex:1;
    font-weight: bold;
  }
}
.customers{
  display: flex;

  .cu-box{
    flex: 1;
  }
}

.overview-title,
.customers-title{
  font-weight: bold;
  font-size: 16px;
}
.date-picker{
  width: 210px;
}
.data-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width:1100px;
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
.store-title{
  display: flex;
  align-items: center;

  .store-box{
    flex: 1;
  }
}
</style>
